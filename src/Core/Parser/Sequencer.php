<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\Interceptor\Escape;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ExpressionException;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;

/**
 * Sequencer for Fluid syntax
 *
 * Uses a NoRewindIterator around a sequence of byte values to
 * iterate over each syntax-relevant character and determine
 * which nodes to create.
 *
 * Passes the outer iterator between functions that perform the
 * iterations. Since the iterator is a NoRewindIterator it will
 * not be reset before the start of each loop - meaning that
 * when it is passed to a new function, that function continues
 * from the last touched index in the byte sequence.
 *
 * The circumstances around "break or return" in the switches is
 * very, very important to understand in context of how iterators
 * work. Returning does not advance the iterator like breaking
 * would and this causes a different position in the byte sequence
 * to be experienced in the method that uses the return value (it
 * sees the index of the symbol which terminated the expression,
 * not the next symbol after that).
 */
class Sequencer
{
    /**
     * @var RenderingContextInterface
     */
    protected $renderingContext;

    /**
     * @var ParsingState
     */
    protected $state;

    /**
     * @var Contexts
     */
    protected $contexts;

    /**
     * @var Source
     */
    protected $source;

    /**
     * @var Splitter
     */
    protected $splitter;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var ViewHelperResolver
     */
    protected $resolver;

    /**
     * Whether or not the escaping interceptors are active
     *
     * @var boolean
     */
    protected $escapingEnabled = true;

    public function __construct(
        RenderingContextInterface $renderingContext,
        ParsingState $state,
        Contexts $contexts,
        Source $source,
        ?Configuration $configuration = null
    ) {
        $this->renderingContext = $renderingContext;
        $this->resolver = $renderingContext->getViewHelperResolver();
        $this->configuration = $configuration ?? $renderingContext->getParserConfiguration();
        $this->escapingEnabled = $this->configuration->isFeatureEnabled(Configuration::FEATURE_ESCAPING);
        $this->state = clone $state;
        $this->contexts = $contexts;
        $this->source = $source;
        $this->splitter = new Splitter($this->source, $this->contexts);
    }

    public function sequence(): ParsingState
    {
        // Please note: repeated calls to $this->state->getTopmostNodeFromStack() are indeed intentional. That method may
        // return different nodes at different times depending on what has occurred in other methods! Only the places
        // where $node is actually extracted is it (by design) safe to do so. DO NOT REFACTOR!
        // It is *also* intentional that this switch has no default case. The root context is very specific and will
        // only apply when the splitter is actually in root, which means there is no chance of it yielding an unexpected
        // character (because that implies a method called by this method already threw a SequencingException).
        foreach ($this->splitter->sequence as $symbol => $captured) {
            switch ($symbol) {
                case Splitter::BYTE_INLINE:
                    $node = $this->state->getNodeFromStack();
                    if ($this->splitter->index > 1 && $this->source->bytes[$this->splitter->index - 1] === Splitter::BYTE_BACKSLASH) {
                        $node->addChild(new TextNode(substr($captured, 0, -1) . '{'));
                        break;
                    }
                    if ($captured !== null) {
                        $node->addChild(new TextNode($captured));
                    }
                    $node->addChild($this->sequenceInlineNodes(false));
                    $this->splitter->switch($this->contexts->root);
                    break;

                case Splitter::BYTE_TAG:
                    if ($captured !== null) {
                        $this->state->getNodeFromStack()->addChild(new TextNode($captured));
                    }

                    $childNode = $this->sequenceTagNode();
                    $this->splitter->switch($this->contexts->root);
                    if ($childNode) {
                        $this->state->getNodeFromStack()->addChild($childNode);
                    }
                    break;

                case Splitter::BYTE_NULL:
                    if ($captured !== null) {
                        $this->state->getNodeFromStack()->addChild(new TextNode($captured));
                    }
                    break;
            }
        }

        if ($this->state->countNodeStack() > 1) {
            $names = [];
            while (($unterminatedNode = $this->state->popNodeFromStack())) {
                $names[] = get_class($unterminatedNode);
            }
            throw $this->splitter->createErrorAtPosition(
                'Unterminated node(s) detected: ' . implode(', ', array_reverse($names)),
                1562671632
            );
        }

        return $this->state;
    }

    protected function sequenceCharacterData(string $text): NodeInterface
    {
        $capturedClosingBrackets = 0;
        $this->splitter->switch($this->contexts->data);
        $this->splitter->sequence->next();
        foreach ($this->splitter->sequence as $symbol => $captured) {
            $text .= $captured;
            if ($symbol === Splitter::BYTE_ARRAY_END) {
                $text .= ']';
                ++$capturedClosingBrackets;
            } elseif ($symbol === Splitter::BYTE_TAG_END && $capturedClosingBrackets === 2) {
                $text .= '>';
                break;
            } else {
                $capturedClosingBrackets = 0;
            }
        }
        return new TextNode($text);
    }

    /**
     * Sequence a Fluid feature toggle node. Does not return
     * any node, only toggles various features of the Fluid
     * parser configuration or assigns context parameters
     * like namespaces.
     *
     * For backwards compatibility we allow the toggle name
     * to be passed, which is used in an explicit check when
     * sequencing inline nodes to detect if a {namespace ...}
     * node was encountered, in which case, this is not known
     * until the "toggle" has already been captured.
     *
     * @param string|null $toggle
     */
    protected function sequenceToggleInstruction(?string $toggle = null): void
    {
        $this->splitter->switch($this->contexts->toggle);
        $this->splitter->sequence->next();
        $flag = null;
        foreach ($this->splitter->sequence as $symbol => $captured) {
            switch ($symbol) {
                case Splitter::BYTE_WHITESPACE_SPACE:
                    $toggle = $toggle ?? $captured;
                    break;
                case Splitter::BYTE_INLINE_END:
                    if ($toggle === 'namespace') {
                        $parts = explode('=', (string) $captured);
                        $this->resolver->addNamespace($parts[0], $parts[1] ?? null);
                        return;
                    }

                    $this->configuration->setFeatureState($toggle, $captured ?? true);
                    // Re-read the parser configuration and react accordingly to any flags that may have changed.
                    $this->escapingEnabled = $this->configuration->isFeatureEnabled(Configuration::FEATURE_ESCAPING);
                    if (!$this->configuration->isFeatureEnabled(Configuration::FEATURE_PARSING)) {
                        throw (new PassthroughSourceException('Source must be represented as raw string', 1563379852))
                            ->setSource((string)$this->sequenceRemainderAsText());
                    }
                    return;
            }
        }
        throw $this->splitter->createErrorAtPosition('Unterminated feature toggle', 1563383038);
    }

    protected function sequenceTagNode(): ?NodeInterface
    {
        $arguments = [];
        $definitions = null;
        $text = '<';
        $key = null;
        $namespace = null;
        $method = null;
        $bytes = &$this->source->bytes;
        $node = new RootNode();
        $selfClosing = false;
        $closing = false;
        $escapingEnabledBackup = $this->escapingEnabled;
        $viewHelperNode = null;

        $this->splitter->switch($this->contexts->tag);
        $this->splitter->sequence->next();
        foreach ($this->splitter->sequence as $symbol => $captured) {
            $text .= $captured;
            switch ($symbol) {
                case Splitter::BYTE_ARRAY_START:
                    // Possible P/CDATA section. Check text explicitly for match, if matched, begin parsing-insensitive
                    // pass through sequenceCharacterDataNode()
                    $text .= '[';
                    if ($text === '<[CDATA[' || $text === '<[PCDATA[') {
                        return $this->sequenceCharacterData($text);
                    }
                    break;

                case Splitter::BYTE_INLINE:
                    $contextBefore = $this->splitter->context;
                    $collected = $this->sequenceInlineNodes(isset($namespace) && isset($method));
                    $node->addChild(new TextNode($text));
                    $node->addChild($collected);
                    $text = '';
                    $this->splitter->switch($contextBefore);
                    break;

                case Splitter::BYTE_SEPARATOR_EQUALS:
                    $key = $key . $captured;
                    $text .= '=';
                    if ($key === '') {
                        throw $this->splitter->createErrorAtPosition('Unexpected equals sign without preceding attribute/key name', 1561039838);
                    } elseif ($definitions !== null && !isset($definitions[$key]) && !$viewHelperNode->validateAdditionalArgument($key)) {
                        $error = $this->splitter->createUnsupportedArgumentError($key, $definitions);
                        $content = $this->renderingContext->getErrorHandler()->handleParserError($error);
                        return new TextNode($content);
                    }
                    break;

                case Splitter::BYTE_QUOTE_DOUBLE:
                case Splitter::BYTE_QUOTE_SINGLE:
                    $text .= chr($symbol);
                    if ($key === null) {
                        throw $this->splitter->createErrorAtPosition('Quoted value without a key is not allowed in tags', 1558952412);
                    } else {
                        $arguments[$key] = $this->sequenceQuotedNode(0, isset($namespace) && isset($method))->flatten(true);
                        $key = null;
                    }
                    break;

                case Splitter::BYTE_TAG_CLOSE:
                    $method = $method ?? $captured;
                    $text .= '/';
                    $closing = true;
                    $selfClosing = $bytes[$this->splitter->index - 1] !== Splitter::BYTE_TAG;

                    if ($this->splitter->context->context === Context::CONTEXT_ATTRIBUTES) {
                        // Arguments may be pending: if $key is set we must create an ECMA literal style shorthand
                        // (attribute value is variable of same name as attribute). Two arguments may be created in
                        // this case, if both $key and $captured are non-null. The former contains a potentially
                        // pending argument and the latter contains a captured value-less attribute right before the
                        // tag closing character.
                        if ($key !== null) {
                            $arguments[$key] = new ObjectAccessorNode($key);
                        }
                        // (see comment above) Hence, the two conditions must not be compunded to else-if.
                        if ($captured !== null) {
                            $arguments[$captured] = new ObjectAccessorNode($captured);
                        }
                    }
                    break;

                case Splitter::BYTE_SEPARATOR_COLON:
                    $text .= ':';
                    if (!$method) {
                        // If we haven't encountered a method yet, then $method won't be set, and we can assign NS now
                        $namespace = $namespace ?? $captured;
                    } else {
                        // If we do have a method this means we encountered a colon as part of an attribute name
                        $key = $key ?? ($captured . ':');
                    }
                    break;

                case Splitter::BYTE_TAG_END:
                    $text .= '>';
                    $method = $method ?? $captured;

                    $this->escapingEnabled = $escapingEnabledBackup;

                    if (!isset($namespace)) {
                        if ($this->splitter->context->context === Context::CONTEXT_DEAD || !$this->resolver->isAliasRegistered((string) $method)) {
                            return $node->addChildNode(new TextNode($text))->flatten();
                        }
                    } elseif ($this->resolver->isNamespaceIgnored((string) $namespace)) {
                        return $node->addChildNode(new TextNode($text))->flatten();
                    }

                    try {
                        $expectedClass = $this->resolver->resolveViewHelperClassName($namespace, $method);
                    } catch (\TYPO3Fluid\Fluid\Core\Exception $exception) {
                        $error = $this->splitter->createErrorAtPosition($exception->getMessage(), $exception->getCode());
                        $content = $this->renderingContext->getErrorHandler()->handleParserError($error);
                        return new TextNode($content);
                    }

                    if ($closing && !$selfClosing) {
                        // Closing byte was more than two bytes back, meaning the tag is NOT self-closing, but is a
                        // closing tag for a previously opened+stacked node. Finalize the node now.
                        $closesNode = $this->state->popNodeFromStack();
                        if ($closesNode instanceof $expectedClass) {
                            $arguments = $closesNode->getParsedArguments();
                            $viewHelperNode = $closesNode;
                        } else {
                            throw $this->splitter->createErrorAtPosition(
                                sprintf(
                                    'Mismatched closing tag. Expecting: %s:%s (%s). Found: (%s).',
                                    $namespace,
                                    $method,
                                    $expectedClass,
                                    get_class($closesNode)
                                ),
                                1557700789
                            );
                        }
                    }

                    // Possibly pending argument still needs to be processed since $key is not null. Create an ECMA
                    // literal style associative array entry. Do the same for $captured.
                    if ($this->splitter->context->context === Context::CONTEXT_ATTRIBUTES) {
                        if ($key !== null) {
                            $arguments[$key] = new ObjectAccessorNode($key);
                        }

                        if ($captured !== null) {
                            $arguments[$captured] = new ObjectAccessorNode($captured);
                        }
                    }

                    $viewHelperNode = $viewHelperNode ?? $this->resolver->createViewHelperInstanceFromClassName($expectedClass);

                    $viewHelperNode->onOpen(
                        $this->renderingContext,
                        $viewHelperNode->createArgumentDefinitions()->assignAll($arguments)
                    );

                    if (!$closing) {
                        $this->callInterceptor($viewHelperNode, InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER);
                        $this->state->pushNodeToStack($viewHelperNode);
                        return null;
                    }

                    $viewHelperNode = $viewHelperNode->onClose($this->renderingContext);

                    if ($viewHelperNode instanceof ViewHelperInterface) {
                        // The node may have been replaced with a different node type by the post-parsing event.
                        // Only when it has not been changed do we need to call the interceptor.
                        $this->callInterceptor(
                            $viewHelperNode,
                            $selfClosing ? InterceptorInterface::INTERCEPT_SELFCLOSING_VIEWHELPER : InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER
                        );
                    }

                    return $viewHelperNode;

                case Splitter::BYTE_WHITESPACE_TAB:
                case Splitter::BYTE_WHITESPACE_RETURN:
                case Splitter::BYTE_WHITESPACE_EOL:
                case Splitter::BYTE_WHITESPACE_SPACE:
                    $text .= chr($symbol);

                    if ($this->splitter->context->context === Context::CONTEXT_ATTRIBUTES) {
                        if ($captured !== null) {
                            // Encountering this case means we've collected a previous key and now collected a non-empty
                            // string value before encountering an equals sign. This is treated as ECMA literal short
                            // hand equivalent of having written `attr="{attr}"` in the Fluid template.
                            if ($key !== null) {
                                $arguments[$key] = new ObjectAccessorNode($key);
                            }
                            $key = $captured;
                        }
                    } elseif (isset($namespace) || (!isset($namespace, $method) && $this->resolver->isAliasRegistered((string)$captured))) {
                        $method = $captured;

                        try {
                            $viewHelperNode = $this->resolver->createViewHelperInstance($namespace, $method);
                        } catch (\TYPO3Fluid\Fluid\Core\Exception $exception) {
                            $error = $this->splitter->createErrorAtPosition($exception->getMessage(), $exception->getCode());
                            $content = $this->renderingContext->getErrorHandler()->handleParserError($error);
                            return new TextNode($content);
                        }

                        // Forcibly disable escaping OFF as default decision for whether or not to escape an argument.
                        $this->escapingEnabled = false;

                        $definitions = $viewHelperNode->createArgumentDefinitions()->getDefinitions();
                        $this->splitter->switch($this->contexts->attributes);
                        break;
                    } else {
                        // A whitespace before a colon means the tag is not a namespaced tag. We will ignore everything
                        // inside this tag, except for inline syntax, until the tag ends. For this we use a special,
                        // limited variant of the root context where instead of scanning for "<" we scan for ">".
                        // We continue in this same loop because it still matches the potential symbols being yielded.
                        // Most importantly: this new reduced context will NOT match a colon which is the trigger symbol
                        // for a ViewHelper tag.
                        $this->splitter->switch($this->contexts->dead);
                    }
                    break;
            }
        }

        // This case on the surface of it, belongs as "default" case in the switch above. However, the only case that
        // would *actually* produce this error, is if the splitter reaches EOF (null byte) symbol before the tag was
        // closed. Literally every other possible error type will be thrown as more specific exceptions (e.g. invalid
        // argument, missing key, wrong quotes, bad inline and *everything* else with the exception of EOF). Even a
        // stray null byte would not be caught here as null byte is not part of the symbol collection for "tag" context.
        throw $this->splitter->createErrorAtPosition('Unexpected token in tag sequencing', 1557700786);
    }

    /**
     * @param bool $allowArray
     * @return NodeInterface
     */
    protected function sequenceInlineNodes(bool $allowArray = true): NodeInterface
    {
        $text = '{';
        $node = null;
        $key = null;
        $namespace = null;
        $method = null;
        $definitions = null;
        $potentialAccessor = null;
        $callDetected = false;
        $hasPass = false;
        $hasColon = null;
        $hasWhitespace = false;
        $isArray = false;
        $array = [];
        $arguments = [];
        $parts = [];
        $ignoredEndingBraces = 0;
        $countedEscapes = 0;
        $this->splitter->switch($this->contexts->inline);
        $this->splitter->sequence->next();
        foreach ($this->splitter->sequence as $symbol => $captured) {
            $text .= $captured;
            switch ($symbol) {
                case Splitter::BYTE_AT:
                    $this->sequenceToggleInstruction();
                    return new TextNode('');
                    break;

                case Splitter::BYTE_BACKSLASH:
                    // Increase the number of counted escapes (is passed to sequenceNode() in the "QUOTE" cases and reset
                    // after the quoted string is extracted).
                    ++$countedEscapes;
                    break;

                case Splitter::BYTE_ARRAY_START:

                    $text .= chr($symbol);
                    $isArray = $allowArray;

                    #ArrayStart:
                    // Sequence the node. Pass the "use numeric keys?" boolean based on the current byte. Only array
                    // start creates numeric keys. Inline start with keyless values creates ECMA style {foo:foo, bar:bar}
                    // from {foo, bar}.
                    $array[$key ?? $captured ?? 0] = $node = $this->sequenceArrayNode(null, $symbol === Splitter::BYTE_ARRAY_START);
                    $this->splitter->switch($this->contexts->inline);
                    unset($key);
                    break;

                case Splitter::BYTE_INLINE:
                    // Encountering this case can mean different things: sub-syntax like {foo.{index}} or array, depending
                    // on presence of either a colon or comma before the inline. In protected mode it is simply added.
                    $text .= '{';
                    if (!$hasWhitespace && $text !== '{{') {
                        // Most likely, a nested object accessor syntax e.g. {foo.{bar}} - enter protected context since
                        // these accessors do not allow anything other than additional nested accessors.
                        $this->splitter->switch($this->contexts->accessor);
                        ++$ignoredEndingBraces;
                    } elseif ($this->splitter->context->context === Context::CONTEXT_PROTECTED) {
                        // Ignore one ending additional curly brace. Subtracted in the BYTE_INLINE_END case below.
                        // The expression in this case looks like {{inline}.....} and we capture the curlies.
                        $potentialAccessor .= $captured;
                        ++$ignoredEndingBraces;
                    } elseif ($allowArray || $isArray) {
                        $isArray = true;
                        $captured = $key ?? $captured ?? $potentialAccessor;
                        // This is a sub-syntax following a colon - meaning it is an array.
                        if ($captured !== null) {
                            #goto ArrayStart;
                            $array[$key ?? $captured ?? 0] = $node = $this->sequenceArrayNode(null, $symbol === Splitter::BYTE_ARRAY_START);
                            $this->splitter->switch($this->contexts->inline);
                        }
                    } else {
                        $childNodeToAdd = $this->sequenceInlineNodes($allowArray);
                        $node = isset($node) ? $node->addChild($childNodeToAdd) : (new RootNode())->addChild($childNodeToAdd);
                    }
                    break;

                case Splitter::BYTE_MINUS:
                    $text .= '-';
                    $potentialAccessor = $potentialAccessor ?? $captured;
                    break;

                // Backtick may be encountered in two different contexts: normal inline context, in which case it has
                // the same meaning as any quote and causes sequencing of a quoted string. Or protected context, in
                // which case it also sequences a quoted node but appends the result instead of assigning to array.
                // Note that backticks do not support escapes (they are a new feature that does not require escaping).
                case Splitter::BYTE_BACKTICK:
                    if ($this->splitter->context->context === Context::CONTEXT_PROTECTED) {
                        $node->addChild(new TextNode($text));
                        $node->addChild($this->sequenceQuotedNode()->flatten());
                        $text = '';
                        break;
                    }
                // Fallthrough is intentional: if not in protected context, consider the backtick a normal quote.

                // Case not normally countered in straight up "inline" context, but when encountered, means we have
                // explicitly found a quoted array key - and we extract it.
                case Splitter::BYTE_QUOTE_SINGLE:
                case Splitter::BYTE_QUOTE_DOUBLE:
                    if (!$allowArray) {
                        $text .= chr($symbol);
                        break;
                    }
                    if (isset($key)) {
                        $array[$key] = $this->sequenceQuotedNode($countedEscapes)->flatten(true);
                        $key = null;
                    } else {
                        $key = $this->sequenceQuotedNode($countedEscapes)->flatten(true);
                    }
                    $countedEscapes = 0;
                    $isArray = $allowArray;
                    break;

                case Splitter::BYTE_SEPARATOR_COMMA:
                    if (!$allowArray) {
                        $text .= ',';
                        break;
                    }
                    if (isset($captured)) {
                        $array[$key ?? $captured] = is_numeric($captured) ? $captured + 0 : new ObjectAccessorNode($captured);
                    }
                    $key = null;
                    $isArray = $allowArray;
                    break;

                case Splitter::BYTE_SEPARATOR_EQUALS:
                    $text .= '=';
                    if (!$allowArray) {
                        $node = new RootNode();
                        $this->splitter->switch($this->contexts->protected);
                        break;
                    }
                    $key = $captured;
                    $isArray = $allowArray;
                    break;

                case Splitter::BYTE_SEPARATOR_COLON:
                    $text .= ':';
                    $hasColon = true;
                    $namespace = $captured;
                    $key = $key ?? $captured;
                    $isArray = $isArray || ($allowArray && is_numeric($key));
                    if ($captured !== null) {
                        $parts[] = $captured;
                    }
                    $parts[] = ':';
                    break;

                case Splitter::BYTE_WHITESPACE_SPACE:
                case Splitter::BYTE_WHITESPACE_EOL:
                case Splitter::BYTE_WHITESPACE_RETURN:
                case Splitter::BYTE_WHITESPACE_TAB:
                    // If we already collected some whitespace we must enter protected context.
                    $text .= $this->source->source[$this->splitter->index - 1];

                    if ($captured !== null) {
                        // Store a captured part: a whitespace inside inline syntax will engage the expression matching
                        // that occurs when the node is closed. Allows matching the various parts to create the appropriate
                        // node type.
                        $parts[] = $captured;
                    }

                    if ($hasWhitespace && !$hasPass && !$allowArray) {
                        // Protection mode: this very limited context does not allow tags or inline syntax, and will
                        // protect things like CSS and JS - and will only enter a more reactive context if encountering
                        // the backtick character, meaning a quoted string will be sequenced. This backtick-quoted
                        // string can then contain inline syntax like variable accessors.
                        $node = $node ?? new RootNode();
                        $this->splitter->switch($this->contexts->protected);
                        break;
                    }
                    if ($captured === 'namespace') {
                        // Special case: we catch namespace definitions with {namespace xyz=foo} syntax here, although
                        // the proper way with current code is to use {@namespace xyz=foo}. We have this case here since
                        // it is relatively cheap (only happens when we see a space inside inline and a straight-up
                        // string comparison with strict types enabled). We then return an empty TextNode which is
                        // ignored by the parent node when attached so we don't create any output.
                        $this->sequenceToggleInstruction('namespace');
                        return new TextNode('');
                    }
                    $key = $key ?? $captured;
                    $hasWhitespace = true;
                    $isArray = $allowArray && ($hasColon ?? $isArray ?? is_numeric($captured));
                    $potentialAccessor = ($potentialAccessor ?? $captured);
                    break;

                case Splitter::BYTE_TAG_END:
                case Splitter::BYTE_PIPE:
                    // If there is an accessor on the left side of the pipe and $node is not defined, we create $node
                    // as an object accessor. If $node already exists we do nothing (and expect the VH trigger, the
                    // parenthesis start case below, to add $node as childnode and create a new $node).
                    $hasPass = true;
                    $isArray = $allowArray;
                    $callDetected = false;
                    $potentialAccessor = $potentialAccessor ?? $captured;
                    $text .=  $this->source->source[$this->splitter->index - 1];
                    if ($node instanceof ViewHelperInterface) {
                        $node->onOpen(
                            $this->renderingContext,
                            $node->createArgumentDefinitions()->assignAll($arguments)
                        )->onClose(
                            $this->renderingContext
                        );
                    }
                    if (isset($potentialAccessor)) {
                        $childNodeToAdd = new ObjectAccessorNode($potentialAccessor);
                        $node = isset($node) ? $node->addChild($childNodeToAdd) : $childNodeToAdd; //$node ?? (is_numeric($potentialAccessor) ? $potentialAccessor + 0 : new ObjectAccessorNode($potentialAccessor));
                    }
                    $potentialAccessor = $namespace = $method = $key = null;
                    break;

                case Splitter::BYTE_PARENTHESIS_START:
                    $isArray = false;
                    // Special case: if a parenthesis start was preceded by whitespace but had no pass operator we are
                    // not dealing with a ViewHelper call and will continue the sequencing, grabbing the parenthesis as
                    // part of the expression.
                    $text .= '(';
                    if (!$hasColon || ($hasWhitespace && !$hasPass)) {
                        $this->splitter->switch($this->contexts->protected);
                        $namespace = $method = null;
                        break;
                    }

                    $callDetected = true;
                    $method = $captured;
                    $childNodeToAdd = $node;
                    try {
                        $node = $this->resolver->createViewHelperInstance($namespace, $method);
                        $definitions = $node->createArgumentDefinitions()->getDefinitions();
                        $this->callInterceptor($node, Escape::INTERCEPT_OPENING_VIEWHELPER);
                    } catch (\TYPO3Fluid\Fluid\Core\Exception $exception) {
                        throw $this->splitter->createErrorAtPosition($exception->getMessage(), $exception->getCode());
                    }
                    $this->splitter->switch($this->contexts->array);
                    $arguments = $this->sequenceArrayNode((array) $definitions)->getInternalArray();
                    $this->splitter->switch($this->contexts->inline);
                    if ($childNodeToAdd) {
                        if ($childNodeToAdd instanceof ObjectAccessorNode) {
                            $this->callInterceptor($childNodeToAdd, InterceptorInterface::INTERCEPT_OBJECTACCESSOR);
                        }
                        $node->addChild($childNodeToAdd);
                    }
                    $text .= ')';
                    $potentialAccessor = null;
                    break;

                case Splitter::BYTE_INLINE_END:
                    $text .= '}';
                    if (--$ignoredEndingBraces >= 0) {
                        break;
                    }
                    $isArray = $allowArray && ($isArray ?: ($hasColon && !$hasPass && !$callDetected));
                    $potentialAccessor = $potentialAccessor ?? $captured;

                    // Decision: if we did not detect a ViewHelper we match the *entire* expression, from the cached
                    // starting index, to see if it matches a known type of expression. If it does, we must return the
                    // appropriate type of ExpressionNode.
                    if ($isArray) {
                        if ($captured !== null) {
                            $array[$key ?? $captured] = is_numeric($captured) ? $captured + 0 : new ObjectAccessorNode($captured);
                        }
                        return new ArrayNode($array);
                    } elseif ($callDetected) {
                        // The first-priority check is for a ViewHelper used right before the inline expression ends,
                        // in which case there is no further syntax to come.
                        $node = $node->onOpen(
                            $this->renderingContext,
                            $node->createArgumentDefinitions()->assignAll($arguments)
                        )->onClose(
                            $this->renderingContext
                        );
                        $interceptionPoint = InterceptorInterface::INTERCEPT_SELFCLOSING_VIEWHELPER;
                    } elseif ($this->splitter->context->context === Context::CONTEXT_ACCESSOR) {
                        // If we are currently in "accessor" context we can now add the accessor by stripping the collected text.
                        $node = new ObjectAccessorNode(substr($text, 1, -1));
                        $interceptionPoint = InterceptorInterface::INTERCEPT_OBJECTACCESSOR;
                    } elseif ($this->splitter->context->context === Context::CONTEXT_PROTECTED || ($hasWhitespace && !$callDetected && !$hasPass)) {
                        // In order to qualify for potentially being an expression, the entire inline node must contain
                        // whitespace, must not contain parenthesis, must not contain a colon and must not contain an
                        // inline pass operand. This significantly limits the number of times this (expensive) routine
                        // has to be executed.
                        $interceptionPoint = InterceptorInterface::INTERCEPT_TEXT;
                        $childNodeToAdd = new TextNode($text);
                        $parts[] = $captured;
                        try {
                            foreach ($this->renderingContext->getExpressionNodeTypes() as $expressionNodeTypeClassName) {
                                if ($expressionNodeTypeClassName::matches($parts)) {
                                    $interceptionPoint = InterceptorInterface::INTERCEPT_EXPRESSION;
                                    $childNodeToAdd = new $expressionNodeTypeClassName($parts);
                                    break;
                                }
                            }
                        } catch (ExpressionException $exception) {
                            // ErrorHandler will either return a string or throw the exception anew, depending on the
                            // exact implementation of ErrorHandlerInterface. When it returns a string we use that as
                            // text content of a new TextNode so the message is output as part of the rendered result.
                            $childNodeToAdd = new TextNode(
                                $this->renderingContext->getErrorHandler()->handleExpressionError($exception)
                            );
                        }
                        if (isset($node)) {
                            $this->callInterceptor($childNodeToAdd, $interceptionPoint);
                            $node = $node->addChild($childNodeToAdd);
                        } else {
                            $node = $childNodeToAdd;
                        }
                    } elseif (!$hasPass && !$callDetected) {
                        // Third priority check is if there was no pass syntax and no ViewHelper, in which case we
                        // create a standard ObjectAccessorNode; alternatively, if nothing was captured (expression
                        // was empty, e.g. {} was used) we create a TextNode with the captured text to output "{}".
                        if (isset($potentialAccessor)) {
                            // If the accessor is set we can trust it is not a numeric value, since this will have
                            // set $isArray to TRUE if nothing else already did so.
                            $node = is_numeric($potentialAccessor) ? $potentialAccessor + 0 : new ObjectAccessorNode($potentialAccessor);
                            $interceptionPoint = InterceptorInterface::INTERCEPT_OBJECTACCESSOR;
                        } else {
                            $node = new TextNode($text);
                            $interceptionPoint = InterceptorInterface::INTERCEPT_TEXT;
                        }
                    } elseif ($hasPass && $this->resolver->isAliasRegistered((string)$potentialAccessor)) {
                        // Fourth priority check is for a pass to a ViewHelper alias, e.g. "{value | raw}" in which case
                        // we look for the alias used and create a ViewHelperNode with no arguments.
                        $childNodeToAdd = $node;
                        $node = $this->resolver->createViewHelperInstance(null, $potentialAccessor);
                        $node->addChild($childNodeToAdd);
                        $node = $node->onOpen(
                            $this->renderingContext,
                            $node->createArgumentDefinitions()->assignAll($arguments)
                        )->onClose(
                            $this->renderingContext
                        );
                        $interceptionPoint = InterceptorInterface::INTERCEPT_SELFCLOSING_VIEWHELPER;
                    } else {
                        # TODO: should this be an error case, or should it result in a TextNode?
                        throw $this->splitter->createErrorAtPosition(
                            'Invalid inline syntax - not accessor, not expression, not array, not ViewHelper, but ' .
                            'contains the tokens used by these in a sequence that is not valid Fluid syntax. You can ' .
                            'most likely avoid this by adding whitespace inside the curly braces before the first ' .
                            'Fluid-like symbol in the expression. Symbols recognized as Fluid are: "' .
                            addslashes(implode('","', array_map('chr', $this->contexts->inline->bytes))) . '"',
                            1558782228
                        );
                    }

                    $this->callInterceptor($node, $interceptionPoint);
                    return $node;
            }
        }

        // See note in sequenceTagNode() end of method body. TL;DR: this is intentionally here instead of as "default"
        // case in the switch above for a very specific reason: the case is only encountered if seeing EOF before the
        // inline expression was closed.
        throw $this->splitter->createErrorAtPosition('Unterminated inline syntax', 1557838506);
    }

    /**
     * @param ArgumentDefinition[]|null $definitions
     * @param bool $numeric
     * @return ArrayNode
     */
    protected function sequenceArrayNode(?array $definitions = null, bool $numeric = false): ArrayNode
    {
        $array = [];

        $keyOrValue = null;
        $key = null;
        $itemCount = -1;
        $countedEscapes = 0;
        $escapingEnabledBackup = $this->escapingEnabled;

        $this->splitter->sequence->next();
        foreach ($this->splitter->sequence as $symbol => $captured) {
            switch ($symbol) {
                case Splitter::BYTE_SEPARATOR_COLON:
                case Splitter::BYTE_SEPARATOR_EQUALS:
                    // Colon or equals has same meaning (which allows tag syntax as argument syntax). Encountering this
                    // byte always means the preceding byte was a key. However, if nothing was captured before this,
                    // it means colon or equals was used without a key which is a syntax error.
                    $key = $key ?? $captured ?? (isset($keyOrValue) ? $keyOrValue->flatten(true) : null);
                    if (!isset($key)) {
                        throw $this->splitter->createErrorAtPosition('Unexpected colon or equals sign, no preceding key', 1559250839);
                    }
                    if ($definitions !== null && !$numeric && !isset($definitions[$key])) {
                        throw $this->splitter->createUnsupportedArgumentError((string)$key, $definitions);
                    }
                    break;

                case Splitter::BYTE_ARRAY_START:
                case Splitter::BYTE_INLINE:
                    // Minimal safeguards to improve error feedback. Theoretically such "garbage" could simply be ignored
                    // without causing problems to the parser, but it is probably best to report it as it could indicate
                    // the user expected X value but gets Y and doesn't notice why.
                    if ($captured !== null) {
                        throw $this->splitter->createErrorAtPosition('Unexpected content before array/inline start in associative array, ASCII: ' . ord($captured), 1559131849);
                    }
                    if (!isset($key) && !$numeric) {
                        throw $this->splitter->createErrorAtPosition('Unexpected array/inline start in associative array without preceding key', 1559131848);
                    }

                    // Encountering a curly brace or square bracket start byte will both cause a sub-array to be sequenced,
                    // the difference being that only the square bracket will cause third parameter ($numeric) passed to
                    // sequenceArrayNode() to be true, which in turn causes key-less items to be added with numeric indexes.
                    $key = $key ?? ++$itemCount;
                    $array[$key] = $this->sequenceArrayNode(null, $symbol === Splitter::BYTE_ARRAY_START);
                    $keyOrValue = null;
                    $key = null;
                    break;

                case Splitter::BYTE_QUOTE_SINGLE:
                case Splitter::BYTE_QUOTE_DOUBLE:
                    // Safeguard: if anything is captured before a quote this indicates garbage leading content. As with
                    // the garbage safeguards above, this one could theoretically be ignored in favor of silently making
                    // the odd syntax "just work".
                    if ($captured !== null) {
                        throw $this->splitter->createErrorAtPosition('Unexpected content before quote start in associative array, ASCII: ' . ord($captured), 1559145560);
                    }

                    // Quotes will always cause sequencing of the quoted string, but differs in behavior based on whether
                    // or not the $key is set. If $key is set, we know for sure we can assign a value. If it is not set
                    // we instead leave $keyOrValue defined so this will be processed by one of the next iterations.
                    $keyOrValue = $this->sequenceQuotedNode($countedEscapes);
                    if (isset($key)) {
                        $array[$key] = $keyOrValue->flatten(true);
                        $keyOrValue = null;
                        $key = null;
                        $countedEscapes = 0;
                    }
                    break;

                case Splitter::BYTE_SEPARATOR_COMMA:
                    // Comma separator: if we've collected a key or value, use it. Otherwise, use captured string.
                    // If neither key nor value nor captured string exists, ignore the comma (likely a tailing comma).
                    if (isset($keyOrValue)) {
                        // Key or value came as quoted string and exists in $keyOrValue
                        $potentialValue = $keyOrValue->flatten(true);
                        $key = $numeric ? ++$itemCount : $potentialValue;
                        $array[$key] = $numeric ? $potentialValue : (is_numeric($key) ? $key + 0 : new ObjectAccessorNode($key));
                    } elseif (isset($captured)) {
                        $key = $key ?? ($numeric ? ++$itemCount : $captured);
                        if (!$numeric && isset($definitions) && !isset($definitions[$key])) {
                            throw $this->splitter->createUnsupportedArgumentError((string)$key, $definitions);
                        }
                        $array[$key] = is_numeric($captured) ? $captured + 0 : new ObjectAccessorNode($captured);
                    }
                    $keyOrValue = null;
                    $key = null;
                    break;

                case Splitter::BYTE_WHITESPACE_TAB:
                case Splitter::BYTE_WHITESPACE_RETURN:
                case Splitter::BYTE_WHITESPACE_EOL:
                case Splitter::BYTE_WHITESPACE_SPACE:
                    // Any whitespace attempts to set the key, if not already set. The captured string may be null as
                    // well, leaving the $key variable still null and able to be coalesced.
                    $key = $key ?? $captured;
                    break;

                case Splitter::BYTE_BACKSLASH:
                    // Escapes are simply counted and passed to the sequenceQuotedNode() method, causing that method
                    // to ignore exactly this number of backslashes before a matching quote is seen as closing quote.
                    ++$countedEscapes;
                    break;

                case Splitter::BYTE_INLINE_END:
                case Splitter::BYTE_ARRAY_END:
                case Splitter::BYTE_PARENTHESIS_END:
                    // Array end indication. Check if anything was collected previously or was captured currently,
                    // assign that to the array and return an ArrayNode with the full array inside.
                    $captured = $captured ?? (isset($keyOrValue) ? $keyOrValue->flatten(true) : null);
                    $key = $key ?? ($numeric ? ++$itemCount : $captured);
                    if (isset($captured, $key)) {
                        if (is_numeric($captured)) {
                            $array[$key] = $captured + 0;
                        } elseif (isset($keyOrValue)) {
                            $array[$key] = $keyOrValue->flatten();
                        } else {
                            $array[$key] = new ObjectAccessorNode($captured ?? $key);
                        }
                    }
                    if (!$numeric && isset($key, $definitions) && !isset($definitions[$key])) {
                        throw $this->splitter->createUnsupportedArgumentError((string)$key, $definitions);
                    }
                    $this->escapingEnabled = $escapingEnabledBackup;
                    return new ArrayNode($array);
            }
        }

        throw $this->splitter->createErrorAtPosition(
            'Unterminated array',
            1557748574
        );
    }

    /**
     * Sequence a quoted value
     *
     * The return can be either of:
     *
     * 1. A string value if source was for example "string"
     * 2. An integer if source was for example "1"
     * 3. A float if source was for example "1.25"
     * 4. A RootNode instance with multiple child nodes if source was for example "string {var}"
     *
     * The idea is to return the raw value if there is no reason for it to
     * be a node as such - which is only necessary if the quoted expression
     * contains other (dynamic) values like an inline syntax.
     *
     * @param int $leadingEscapes A backwards compatibility measure: when passed, this number of escapes must precede a closing quote for it to trigger node closing.
     * @param bool $allowArray
     * @return RootNode
     */
    protected function sequenceQuotedNode(int $leadingEscapes = 0, $allowArray = true): RootNode
    {
        $startingByte = $this->source->bytes[$this->splitter->index];
        $contextToRestore = $this->splitter->switch($this->contexts->quoted);
        $node = new RootNode();
        $this->splitter->sequence->next();
        $countedEscapes = 0;

        foreach ($this->splitter->sequence as $symbol => $captured) {
            switch ($symbol) {

                case Splitter::BYTE_ARRAY_START:
                    $countedEscapes = 0; // Theoretically not required but done in case of stray escapes (gets ignored)
                    if ($captured === null) {
                        // Array start "[" only triggers array sequencing if it is the very first byte in the quoted
                        // string - otherwise, it is added as part of the text.
                        $this->splitter->switch($this->contexts->array);
                        $node->addChild($this->sequenceArrayNode(null, $allowArray));
                        $this->splitter->switch($this->contexts->quoted);
                    } else {
                        $node->addChild(new TextNode($captured . '['));
                    }
                    break;

                case Splitter::BYTE_INLINE:
                    $countedEscapes = 0; // Theoretically not required but done in case of stray escapes (gets ignored)
                    // The quoted string contains a sub-expression. We extract the captured content so far and if it
                    // is not an empty string, add it as a child of the RootNode we're building, then we add the inline
                    // expression as next sibling and continue the loop.
                    if ($captured !== null) {
                        $childNode = new TextNode($captured);
                        $this->callInterceptor($childNode, InterceptorInterface::INTERCEPT_TEXT);
                        $node->addChild($childNode);
                    }

                    $node->addChild($this->sequenceInlineNodes());
                    $this->splitter->switch($this->contexts->quoted);
                    break;

                case Splitter::BYTE_BACKSLASH:
                    $next = $this->source->bytes[$this->splitter->index + 1] ?? null;
                    ++$countedEscapes;
                    if ($next === $startingByte || $next === Splitter::BYTE_BACKSLASH) {
                        if ($captured !== null) {
                            $node->addChild(new TextNode($captured));
                        }
                    } else {
                        $node->addChild(new TextNode($captured . str_repeat('\\', $countedEscapes)));
                        $countedEscapes = 0;
                    }
                    break;

                // Note: although "case $startingByte:" could have been used here, it would not compile the switch
                // as a hash map and thus would not perform as well overall - when called frequently as it will be.
                // Backtick will only be encountered if the context is "protected" (insensitive inline sequencing)
                case Splitter::BYTE_QUOTE_SINGLE:
                case Splitter::BYTE_QUOTE_DOUBLE:
                case Splitter::BYTE_BACKTICK:
                    if ($symbol !== $startingByte || $countedEscapes !== $leadingEscapes) {
                        $childNode = new TextNode($captured . chr($symbol));
                        $this->callInterceptor($childNode, InterceptorInterface::INTERCEPT_TEXT);
                        $node->addChild($childNode);
                        $countedEscapes = 0; // If number of escapes do not match expected, reset the counter
                        break;
                    }
                    if ($captured !== null) {
                        $childNode = new TextNode($captured);
                        $this->callInterceptor($childNode, InterceptorInterface::INTERCEPT_TEXT);
                        $node->addChild($childNode);
                    }
                    $this->splitter->switch($contextToRestore);
                    return $node;
            }
        }

        throw $this->splitter->createErrorAtPosition('Unterminated expression inside quotes', 1557700793);
    }

    /**
     * Dead-end sequencing; if parsing is switched off it cannot be switched on again,
     * and the remainder of the template source must be sequenced as dead text.
     *
     * @return string|null
     */
    protected function sequenceRemainderAsText(): ?string
    {
        $this->splitter->sequence->next();
        $this->splitter->switch($this->contexts->empty);
        $source = null;
        foreach ($this->splitter->sequence as $symbol => $captured) {
            $source .= $captured;
        }
        return $source;
    }

    /**
     * Call all interceptors registered for a given interception point.
     *
     * @param NodeInterface $node The syntax tree node which can be modified by the interceptors.
     * @param integer $interceptionPoint the interception point. One of the \TYPO3Fluid\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_* constants.
     * @return void
     */
    protected function callInterceptor(NodeInterface &$node, $interceptionPoint)
    {
        if ($this->escapingEnabled) {
            /** @var $interceptor InterceptorInterface */
            foreach ($this->configuration->getEscapingInterceptors($interceptionPoint) as $interceptor) {
                $node = $interceptor->process($node, $interceptionPoint, $this->state);
            }
        }

        /** @var $interceptor InterceptorInterface */
        foreach ($this->configuration->getInterceptors($interceptionPoint) as $interceptor) {
            $node = $interceptor->process($node, $interceptionPoint, $this->state);
        }
    }
}