<?php
declare(strict_types=1);

namespace TYPO3Fluid\Fluid\Core\Parser;

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ExpressionException;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
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
        Source $source
    ) {
        $this->renderingContext = $renderingContext;
        $this->resolver = $renderingContext->getViewHelperResolver();
        $this->configuration = $renderingContext->buildParserConfiguration();
        $this->state = clone $state;
        $this->contexts = $contexts;
        $this->source = $source;
        $this->splitter = new Splitter($this->source, $this->contexts);
    }

    /**
     * Creates a dump, starting from the first line break before $position,
     * to the next line break from $position, counting the lines and characters
     * and inserting a marker pointing to the exact offending character.
     *
     * Is not very efficient - but adds bug tracing information. Should only
     * be called when exceptions are raised during sequencing.
     *
     * @param Position $position
     * @return string
     */
    public function extractSourceDumpOfLineAtPosition(Position $position): string
    {
        $lines = $this->splitter->countCharactersMatchingMask(Splitter::MASK_LINEBREAKS, 1, $position->index) + 1;
        $offset = $this->splitter->findBytePositionBeforeOffset(Splitter::MASK_LINEBREAKS, $position->index);
        $line = substr(
            $this->source->source,
            $offset,
            $this->splitter->findBytePositionAfterOffset(Splitter::MASK_LINEBREAKS, $position->index)
        );
        $character = $position->index - $offset - 1;
        $string = 'Line ' . $lines . ' character ' . $character . PHP_EOL;
        $string .= PHP_EOL;
        $string .= str_repeat(' ', max($character, 0)) . 'v' . PHP_EOL;
        $string .= trim($line) . PHP_EOL;
        $string .= str_repeat(' ', max($character, 0)) . '^' . PHP_EOL;
        return $string;
    }

    protected function createErrorAtPosition(string $message, int $code): SequencingException
    {
        $position = new Position($this->splitter->context, $this->splitter->index);
        $ascii = (string) $this->source->bytes[$this->splitter->index];
        $message .=  ' ASCII: ' . $ascii . ': ' . $this->extractSourceDumpOfLineAtPosition($position);
        $error = new SequencingException($message, $code);
        return $error;
    }

    protected function createUnsupportedArgumentError(string $argument, array $definitions): SequencingException
    {
        return $this->createErrorAtPosition(
            sprintf(
                'Unsupported argument "%s". Supported: ' . implode(', ', array_keys($definitions)),
                $argument
            ),
            1558298976
        );
    }

    protected function createIterator(\Generator $generator): \NoRewindIterator
    {
        return new \NoRewindIterator($generator);
    }

    public function sequence(): ParsingState
    {
        $split = $this->splitter->parse();
        $sequence = $this->createIterator($split);

        // Please note: repeated calls to $this->getTopmostNodeFromStack() are indeed intentional. That method may
        // return different nodes at different times depending on what has occured in other methods! Only the places
        // where $node is actually extracted is it (by design) safe to do so. DO NOT REFACTOR!
        // It is *also* intentional that this switch has no default case. The root context is very specific and will
        // only apply when the splitter is actually in root, which means there is no chance of it yielding an unexpected
        // character (because that implies a method called by this method already threw a SequencingException).
        foreach ($sequence as $symbol => $captured) {
            switch ($symbol) {
                case Splitter::BYTE_INLINE:
                    $node = $this->state->getNodeFromStack();
                    if ($this->splitter->index > 1 && $this->source->bytes[$this->splitter->index - 1] === Splitter::BYTE_BACKSLASH) {
                        $node->addChildNode(new TextNode(substr($captured, 0, -1) . '{'));
                        break;
                    }
                    if ($captured !== null) {
                        $node->addChildNode(new TextNode($captured));
                    }
                    $node->addChildNode($this->sequenceInlineNodes($sequence, false));
                    $this->splitter->switch($this->contexts->root);
                    break;

                case Splitter::BYTE_TAG:
                    if ($captured !== null) {
                        $this->state->getNodeFromStack()->addChildNode(new TextNode($captured));
                    }

                    $childNode = $this->sequenceTagNode($sequence);
                    $this->splitter->switch($this->contexts->root);
                    if ($childNode) {
                        $this->state->getNodeFromStack()->addChildNode($childNode);
                    }
                    break;

                case Splitter::BYTE_NULL:
                    if ($captured !== null) {
                        $this->state->getNodeFromStack()->addChildNode(new TextNode($captured));
                    }
                    break;
            }
        }

        return $this->state;
    }

    /**
     * @param \Iterator|?string[] $sequence
     * @return NodeInterface|null
     */
    protected function sequenceTagNode(\Iterator $sequence): ?NodeInterface
    {
        $arguments = [];
        $definitions = null;
        $text = '<';
        $namespace = null;
        $method = null;
        $bytes = &$this->source->bytes;
        $node = new RootNode();
        $selfClosing = false;
        $closing = false;
        #$escapingEnabledBackup = $this->escapingEnabled;

        $interceptionPoint = InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER;

        $this->splitter->switch($this->contexts->tag);
        $sequence->next();
        foreach ($sequence as $symbol => $captured) {
            $text .= $captured;
            switch ($symbol) {
                case Splitter::BYTE_INLINE:
                    $contextBefore = $this->splitter->context;
                    $collected = $this->sequenceInlineNodes($sequence, isset($namespace) && isset($method));
                    $node->addChildNode(new TextNode($text));
                    $node->addChildNode($collected);
                    $text = '';
                    $this->splitter->switch($contextBefore);
                    break;

                case Splitter::BYTE_SEPARATOR_EQUALS:
                    $key = $captured;
                    if ($definitions !== null && !isset($definitions[$key])) {
                        throw $this->createUnsupportedArgumentError($key, $definitions);
                    }
                    break;

                case Splitter::BYTE_QUOTE_DOUBLE:
                case Splitter::BYTE_QUOTE_SINGLE:
                    $text .= chr($symbol);
                    if (!isset($key)) {
                        throw $this->createErrorAtPosition('Quoted value without a key is not allowed in tags', 1558952412);
                    } else {
                        $arguments[$key] = $this->sequenceQuotedNode($sequence, 0, isset($namespace) && isset($method))->flatten(true);
                        $key = null;
                    }
                    break;

                case Splitter::BYTE_TAG_CLOSE:
                    $method = $method ?? $captured;
                    $text .= '/';
                    $closing = true;
                    $selfClosing = $bytes[$this->splitter->index - 1] !== Splitter::BYTE_TAG;
                    $interceptionPoint = InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER;
                    if ($this->splitter->context->context === Context::CONTEXT_ATTRIBUTES && $captured !== null) {
                        // We are still capturing arguments and the last yield contained a value. Null-coalesce key
                        // with captured string so object accessor becomes key name (ECMA shorthand literal)
                        $arguments[$key ?? $captured] = is_numeric($captured) ? $captured + 0 : new ObjectAccessorNode($captured);
                        $key = null;
                    }
                    break;

                case Splitter::BYTE_SEPARATOR_COLON:
                    $text .= ':';
                    $namespace = $namespace ?? $captured;
                    break;

                case Splitter::BYTE_TAG_END:
                    $text .= '>';
                    $method = $method ?? $captured;

                    if (!isset($namespace) || !isset($method) || $this->splitter->context->context === Context::CONTEXT_DEAD || $this->resolver->isNamespaceIgnored($namespace)) {
                        return $node->addChildNode(new TextNode($text))->flatten();
                    }

                    try {
                        $expectedClass = $this->resolver->resolveViewHelperClassName($namespace, $method);
                    } catch (\TYPO3Fluid\Fluid\Core\Exception $exception) {
                        throw $this->createErrorAtPosition($exception->getMessage(), $exception->getCode());
                    }

                    if ($closing && !$selfClosing) {
                        // Closing byte was more than two bytes back, meaning the tag is NOT self-closing, but is a
                        // closing tag for a previously opened+stacked node. Finalize the node now.
                        $closesNode = $this->state->popNodeFromStack();
                        if ($closesNode instanceof $expectedClass) {
                            $arguments = $closesNode->getParsedArguments();
                            $viewHelperNode = $closesNode;
                        } else {
                            throw $this->createErrorAtPosition(
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

                    if ($this->splitter->context->context === Context::CONTEXT_ATTRIBUTES && $captured !== null) {
                        // We are still capturing arguments and the last yield contained a value. Null-coalesce key
                        // with captured string so object accessor becomes key name (ECMA shorthand literal)
                        $arguments[$key ?? $captured] = is_numeric($captured) ? $captured + 0 : new ObjectAccessorNode($captured);
                    }

                    $viewHelperNode = $viewHelperNode ?? $this->resolver->createViewHelperInstanceFromClassName($expectedClass);
                    #$this->escapingEnabled = $escapingEnabledBackup;

                    if (!$closing) {
                        $this->callInterceptor($viewHelperNode, $interceptionPoint);
                        $viewHelperNode->setParsedArguments($arguments);
                        $this->state->pushNodeToStack($viewHelperNode);
                        return null;
                    }

                    $viewHelperNode = $viewHelperNode->postParse($arguments, $this->state, $this->renderingContext);

                    return $viewHelperNode;

                case Splitter::BYTE_WHITESPACE_TAB:
                case Splitter::BYTE_WHITESPACE_RETURN:
                case Splitter::BYTE_WHITESPACE_EOL:
                case Splitter::BYTE_WHITESPACE_SPACE:
                    if ($this->splitter->context->context === Context::CONTEXT_ATTRIBUTES) {
                        if ($captured !== null) {
                            $arguments[$key ?? $captured] = is_numeric($captured) ? $captured + 0 : new ObjectAccessorNode($captured);
                            $key = null;
                        }
                    } else {
                        $text .= chr($symbol);
                        if (isset($namespace)) {
                            $method = $captured;

                            $this->escapingEnabled = false;
                            $viewHelperNode = $this->resolver->createViewHelperInstance($namespace, $method);
                            $definitions = $viewHelperNode->prepareArguments();

                            // A whitespace character, in tag context, means the beginning of an array sequence (which may
                            // or may not contain any items; the next symbol may be a tag end or tag close). We sequence the
                            // arguments array and create a ViewHelper node.
                            $this->splitter->switch($this->contexts->attributes);
                            break;
                        }

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
        throw $this->createErrorAtPosition('Unexpected token in tag sequencing', 1557700786);
    }

    /**
     * @param \Iterator|?string[] $sequence
     * @param bool $allowArray
     * @return NodeInterface
     */
    protected function sequenceInlineNodes(\Iterator $sequence, bool $allowArray = true): NodeInterface
    {
        $text = '{';
        $node = null;
        $key = null;
        $namespace = null;
        $method = null;
        $potentialAccessor = null;
        $callDetected = false;
        $hasPass = false;
        $hasColon = null;
        $hasWhitespace = false;
        $isArray = false;
        $array = [];
        $arguments = [];
        $ignoredEndingBraces = 0;
        $countedEscapes = 0;

        $this->splitter->switch($this->contexts->inline);
        $sequence->next();
        foreach ($sequence as $symbol => $captured) {
            $text .= $captured;
            switch ($symbol) {
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
                    $array[$key ?? $captured ?? 0] = $node = $this->sequenceArrayNode($sequence, null, $symbol === Splitter::BYTE_ARRAY_START);
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
                            $array[$key ?? $captured ?? 0] = $node = $this->sequenceArrayNode($sequence, null, $symbol === Splitter::BYTE_ARRAY_START);
                            $this->splitter->switch($this->contexts->inline);
                        }
                    } else {
                        $childNodeToAdd = $this->sequenceInlineNodes($sequence, $allowArray);
                        $node = isset($node) ? $node->addChildNode($childNodeToAdd) : (new RootNode())->addChildNode($childNodeToAdd);
                    }
                    break;

                case Splitter::BYTE_MINUS:
                    $text .= '-';
                    break;

                // Backtick may be encountered in two different contexts: normal inline context, in which case it has
                // the same meaning as any quote and causes sequencing of a quoted string. Or protected context, in
                // which case it also sequences a quoted node but appends the result instead of assigning to array.
                // Note that backticks do not support escapes (they are a new feature that does not require escaping).
                case Splitter::BYTE_BACKTICK:
                    if ($this->splitter->context->context === Context::CONTEXT_PROTECTED) {
                        $node->addChildNode(new TextNode($text));
                        $node->addChildNode($this->sequenceQuotedNode($sequence)->flatten());
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
                        $array[$key] = $this->sequenceQuotedNode($sequence, $countedEscapes)->flatten(true);
                        $key = null;
                    } else {
                        $key = $this->sequenceQuotedNode($sequence, $countedEscapes)->flatten(true);
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
                    break;

                case Splitter::BYTE_WHITESPACE_SPACE:
                case Splitter::BYTE_WHITESPACE_EOL:
                case Splitter::BYTE_WHITESPACE_RETURN:
                case Splitter::BYTE_WHITESPACE_TAB:
                    // If we already collected some whitespace we must enter protected context.
                    $text .= $this->source->source[$this->splitter->index - 1];
                    if ($hasWhitespace && !$hasPass && !$allowArray) {
                        // Protection mode: this very limited context does not allow tags or inline syntax, and will
                        // protect things like CSS and JS - and will only enter a more reactive context if encountering
                        // the backtick character, meaning a quoted string will be sequenced. This backtick-quoted
                        // string can then contain inline syntax like variable accessors.
                        $node = $node ?? new RootNode();
                        $this->splitter->switch($this->contexts->protected);
                        break;
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
                    if (isset($potentialAccessor)) {
                        $childNodeToAdd = new ObjectAccessorNode($potentialAccessor);
                        $node = isset($node) ? $node->addChildNode($childNodeToAdd) : $childNodeToAdd; //$node ?? (is_numeric($potentialAccessor) ? $potentialAccessor + 0 : new ObjectAccessorNode($potentialAccessor));
                    }
                    //!isset($potentialAccessor) ?: ($node = ($node ?? $this->createObjectAccessorNodeOrRawValue($potentialAccessor)));
                    unset($namespace, $method, $potentialAccessor, $key);
                    break;

                case Splitter::BYTE_PARENTHESIS_START:
                    $isArray = false;
                    // Special case: if a parenthesis start was preceded by whitespace but had no pass operator we are
                    // not dealing with a ViewHelper call and will continue the sequencing, grabbing the parenthesis as
                    // part of the expression.
                    $text .= '(';
                    if (!$hasColon || ($hasWhitespace && !$hasPass)) {
                        $this->splitter->switch($this->contexts->protected);
                        unset($namespace, $method);
                        break;
                    }

                    $callDetected = true;
                    $method = $captured;
                    $childNodeToAdd = $node;
                    try {
                        $node = $this->resolver->createViewHelperInstance($namespace, $method);
                        $definitions = $node->prepareArguments();
                    } catch (\TYPO3Fluid\Fluid\Core\Exception $exception) {
                        throw $this->createErrorAtPosition($exception->getMessage(), $exception->getCode());
                    }
                    $this->splitter->switch($this->contexts->array);
                    $arguments = $this->sequenceArrayNode($sequence, $definitions)->getInternalArray();
                    $this->splitter->switch($this->contexts->inline);
                    if ($childNodeToAdd) {
                        $escapingEnabledBackup = $this->escapingEnabled;
                        $this->escapingEnabled = (bool)$node->isChildrenEscapingEnabled();
                        if ($childNodeToAdd instanceof ObjectAccessorNode) {
                            $this->callInterceptor($childNodeToAdd, InterceptorInterface::INTERCEPT_OBJECTACCESSOR);
                        }
                        $this->escapingEnabled = $escapingEnabledBackup;
                        $node->addChildNode($childNodeToAdd);
                    }
                    $text .= ')';
                    unset($potentialAccessor);
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
                        $node = $node->postParse($arguments, $this->state, $this->renderingContext);
                        $interceptionPoint = InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER;
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
                        foreach ($this->renderingContext->getExpressionNodeTypes() as $expressionNodeTypeClassName) {
                            $matchedVariables = [];
                            // TODO: rewrite expression nodes to receive a sub-Splitter that lets the expression node
                            // consume a symbol+capture sequence and either match or ignore it; then use the already
                            // consumed (possibly halted mid-way through iterator!) sequence to achieve desired behavior.
                            preg_match_all($expressionNodeTypeClassName::$detectionExpression, $text, $matchedVariables, PREG_SET_ORDER);
                            foreach ($matchedVariables as $matchedVariableSet) {
                                try {
                                    $childNodeToAdd = new $expressionNodeTypeClassName($matchedVariableSet[0], $matchedVariableSet, $this->state);
                                    $interceptionPoint = InterceptorInterface::INTERCEPT_EXPRESSION;
                                } catch (ExpressionException $error) {
                                    $childNodeToAdd = new TextNode($this->renderingContext->getErrorHandler()->handleExpressionError($error));
                                }
                                break;
                            }
                        }
                        $node = isset($node) ? $node->addChildNode($childNodeToAdd) : $childNodeToAdd;
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
                        $node->addChildNode($childNodeToAdd);
                        $node = $node->postParse($arguments, $this->state, $this->renderingContext);
                        $interceptionPoint = InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER;
                    } else {
                        # TODO: should this be an error case, or should it result in a TextNode?
                        throw $this->createErrorAtPosition(
                            'Invalid inline syntax - not accessor, not expression, not array, not ViewHelper, but ' .
                            'contains the tokens used by these in a sequence that is not valid Fluid syntax. You can ' .
                            'most likely avoid this by adding whitespace inside the curly braces before the first ' .
                            'Fluid-like symbol in the expression. Symbols recognized as Fluid are: "' .
                            addslashes(implode('","', array_map('chr', $this->contexts->inline->bytes))) . '"',
                            1558782228
                        );
                    }

                    $escapingEnabledBackup = $this->escapingEnabled;
                    $this->escapingEnabled = (bool)((isset($viewHelper) && $node->isOutputEscapingEnabled()) || $escapingEnabledBackup);
                    $this->callInterceptor($node, $interceptionPoint, $this->state);
                    $this->escapingEnabled = $escapingEnabledBackup;
                    return $node;
            }
        }

        // See note in sequenceTagNode() end of method body. TL;DR: this is intentionally here instead of as "default"
        // case in the switch above for a very specific reason: the case is only encountered if seeing EOF before the
        // inline expression was closed.
        throw $this->createErrorAtPosition('Unterminated inline syntax', 1557838506);
    }

    /**
     * @param \Iterator|Position[] $sequence
     * @param ArgumentDefinition[] $definitions
     * @param bool $numeric
     * @return ArrayNode
     */
    protected function sequenceArrayNode(\Iterator $sequence, array $definitions = null, bool $numeric = false): ArrayNode
    {
        $array = [];

        $keyOrValue = null;
        $key = null;
        $escapingEnabledBackup = $this->escapingEnabled;
        $this->escapingEnabled = false;
        $itemCount = -1;
        $countedEscapes = 0;

        $sequence->next();
        foreach ($sequence as $symbol => $captured) {
            switch ($symbol) {
                case Splitter::BYTE_SEPARATOR_COLON:
                case Splitter::BYTE_SEPARATOR_EQUALS:
                    // Colon or equals has same meaning (which allows tag syntax as argument syntax). Encountering this
                    // byte always means the preceding byte was a key. However, if nothing was captured before this,
                    // it means colon or equals was used without a key which is a syntax error.
                    $key = $key ?? $captured ?? (isset($keyOrValue) ? $keyOrValue->flatten(true) : null);
                    if (!isset($key)) {
                        throw $this->createErrorAtPosition('Unexpected colon or equals sign, no preceding key', 1559250839);
                    }
                    if ($definitions !== null && !$numeric && !isset($definitions[$key])) {
                        throw $this->createUnsupportedArgumentError((string)$key, $definitions);
                    }
                    break;

                case Splitter::BYTE_ARRAY_START:
                case Splitter::BYTE_INLINE:
                    // Minimal safeguards to improve error feedback. Theoretically such "garbage" could simply be ignored
                    // without causing problems to the parser, but it is probably best to report it as it could indicate
                    // the user expected X value but gets Y and doesn't notice why.
                    if ($captured !== null) {
                        throw $this->createErrorAtPosition('Unexpected content before array/inline start in associative array, ASCII: ' . ord($captured), 1559131849);
                    }
                    if (!isset($key) && !$numeric) {
                        throw $this->createErrorAtPosition('Unexpected array/inline start in associative array without preceding key', 1559131848);
                    }

                    // Encountering a curly brace or square bracket start byte will both cause a sub-array to be sequenced,
                    // the difference being that only the square bracket will cause third parameter ($numeric) passed to
                    // sequenceArrayNode() to be true, which in turn causes key-less items to be added with numeric indexes.
                    $key = $key ?? ++$itemCount;
                    $array[$key] = $this->sequenceArrayNode($sequence, null, $symbol === Splitter::BYTE_ARRAY_START);
                    $keyOrValue = null;
                    $key = null;
                    break;

                case Splitter::BYTE_QUOTE_SINGLE:
                case Splitter::BYTE_QUOTE_DOUBLE:
                    // Safeguard: if anything is captured before a quote this indicates garbage leading content. As with
                    // the garbage safeguards above, this one could theoretically be ignored in favor of silently making
                    // the odd syntax "just work".
                    if ($captured !== null) {
                        throw $this->createErrorAtPosition('Unexpected content before quote start in associative array, ASCII: ' . ord($captured), 1559145560);
                    }

                    // Quotes will always cause sequencing of the quoted string, but differs in behavior based on whether
                    // or not the $key is set. If $key is set, we know for sure we can assign a value. If it is not set
                    // we instead leave $keyOrValue defined so this will be processed by one of the next iterations.
                    $keyOrValue = $this->sequenceQuotedNode($sequence, $countedEscapes);
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
                            throw $this->createUnsupportedArgumentError((string)$key, $definitions);
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
                        throw $this->createUnsupportedArgumentError((string)$key, $definitions);
                    }
                    $this->escapingEnabled = $escapingEnabledBackup;
                    return new ArrayNode($array);
            }
        }

        throw $this->createErrorAtPosition(
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
     * @param \Iterator|Position[] $sequence
     * @param int $leadingEscapes A backwards compatibility measure: when passed, this number of escapes must precede a closing quote for it to trigger node closing.
     * @param bool $allowArray
     * @return RootNode
     */
    protected function sequenceQuotedNode(\Iterator $sequence, int $leadingEscapes = 0, $allowArray = true): RootNode
    {
        $startingByte = $this->source->bytes[$this->splitter->index];
        $contextToRestore = $this->splitter->switch($this->contexts->quoted);
        $node = new RootNode();
        $sequence->next();
        $countedEscapes = 0;

        foreach ($sequence as $symbol => $captured) {
            switch ($symbol) {

                case Splitter::BYTE_ARRAY_START:
                    $countedEscapes = 0; // Theoretically not required but done in case of stray escapes (gets ignored)
                    if ($captured === null) {
                        // Array start "[" only triggers array sequencing if it is the very first byte in the quoted
                        // string - otherwise, it is added as part of the text.
                        $this->splitter->switch($this->contexts->array);
                        $node->addChildNode($this->sequenceArrayNode($sequence, null, $allowArray));
                        $this->splitter->switch($this->contexts->quoted);
                    } else {
                        $node->addChildNode(new TextNode($captured . '['));
                    }
                    break;

                case Splitter::BYTE_INLINE:
                    $countedEscapes = 0; // Theoretically not required but done in case of stray escapes (gets ignored)
                    // The quoted string contains a sub-expression. We extract the captured content so far and if it
                    // is not an empty string, add it as a child of the RootNode we're building, then we add the inline
                    // expression as next sibling and continue the loop.
                    if ($captured !== null) {
                        $childNode = new TextNode($captured);
                        $this->callInterceptor($childNode, InterceptorInterface::INTERCEPT_TEXT, $this->state);
                        $node->addChildNode($childNode);
                    }

                    $node->addChildNode($this->sequenceInlineNodes($sequence));
                    $this->splitter->switch($this->contexts->quoted);
                    break;

                case Splitter::BYTE_BACKSLASH:
                    ++$countedEscapes;
                    if ($captured !== null) {
                        $node->addChildNode(new TextNode($captured));
                    }
                    break;

                // Note: although "case $startingByte:" could have been used here, it would not compile the switch
                // as a hash map and thus would not perform as well overall - when called frequently as it will be.
                // Backtick will only be encountered if the context is "protected" (insensitive inline sequencing)
                case Splitter::BYTE_QUOTE_SINGLE:
                case Splitter::BYTE_QUOTE_DOUBLE:
                case Splitter::BYTE_BACKTICK:
                    if ($symbol !== $startingByte || $countedEscapes !== $leadingEscapes) {
                        $node->addChildNode(new TextNode($captured . chr($symbol)));
                        $countedEscapes = 0; // If number of escapes do not match expected, reset the counter
                        break;
                    }
                    if ($captured !== null) {
                        $node->addChildNode(new TextNode($captured));
                    }
                    $this->splitter->switch($contextToRestore);
                    return $node;
            }
        }

        throw $this->createErrorAtPosition('Unterminated expression inside quotes', 1557700793);
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