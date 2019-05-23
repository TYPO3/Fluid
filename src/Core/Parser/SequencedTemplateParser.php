<?php
declare(strict_types=1);

namespace TYPO3Fluid\Fluid\Core\Parser;

use cogpowered\FineDiff\Render\Text;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\InterceptorInterface;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ExpressionException;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ExpressionNodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ParseTimeEvaluatedExpressionNodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NumericNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\PostponedViewHelperNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;

/**
 * Sequence-based Template Parser for Fluid syntax
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
 * The circumstance around "break or return" in the switches is
 * very, very important to understand in context of how iterators
 * work. Returning does not advance the iterator like breaking
 * would and this causes a different position in the byte sequence
 * to be experienced in the method that uses the return value (it
 * sees the index of the symbol which terminated the expression,
 * not the next symbol after that).
 */
class SequencedTemplateParser extends TemplateParser
{
    /** @var ?Debugger */
    public $debugger;

    /** @var ParsingState */
    private $state;

    /** @var Splitter */
    private $splitter;

    /** @var Contexts */
    private $contexts;

    /** @var Source */
    private $source;

    public function parse($templateString, $templateIdentifier = null): ParsingState
    {
        $templateIdentifier = $templateIdentifier ?? 'source_' . sha1($templateString);
        $templateString = $this->preProcessTemplateSource($templateString);

        $this->source = new Source($templateString);
        $this->contexts = new Contexts();
        $this->splitter = new Splitter($this->source, $this->contexts);

        $this->state = $this->getParsingState();
        $sequence = $this->splitter->parse();
        $iterator = new \NoRewindIterator($sequence);
        $node = $this->sequenceRootNodesAsChildrenOfTopStack($iterator);

        if (!$node instanceof RootNode) {
            $child = $node;
            $node = new RootNode();
            $node->addChildNode($child);
        }
        $this->state->setRootNode($node);
        $this->parsedTemplates[$templateIdentifier] = $this->state;
        return $this->state;
    }

    /**
     * Pack byte sequence defined by Position
     *
     * Small utility that can "pack" a Position object pointing to
     * offsets in the Source object's byte array, creating an ASCII
     * string to be returned.
     *
     * Accepts bit masks like the Splitter, which can be used to
     * pass undesired byte values that will be ignored.
     *
     * Essentially a highly optimised version of doing:
     *
     *     trim(pack('C*', ...array_slice($bytes, $offset, $length)), implode('', $undesiredChars))
     *
     * Or:
     *
     *     trim(substr($source, $offset, $length), implode('', $undesiredChars));
     *
     * But many, many times faster than either of those methods.
     *
     * Caveat: contrary to what "trim" would do, this method will
     * ignore the unwanted bytes regardless of where in the string
     * they exist - start, end or anywhere inside other characters.
     * This behavior is fine for sequencing Fluid where symbols are
     * yielded in a way that unwanted characters would already have
     * caused a split, but probably not good for other use cases.
     *
     * Note: the longer the extraction sequence is, the better this
     * class performs compared to either of the methods above. The
     * method of slicing and packing the byte array is by far the
     * least optimal. But only this method has the benefit of not
     * creating any memory copies except for the exact bytes that
     * must be returned - and uses a bit based replacement for the
     * trim() method which avoids adding, instead of removing after,
     * any unwanted bytes.
     */
    public function pack(Position $position, int $ignorePrimaryMask = 0, int $ignoreSecondaryMask = 0): ?string
    {
        $offset = $position->lastYield + 1;
        $end = $position->index;
        $source = &$this->source->source;
        $captured = null;
        for ($index = $offset; $index < $end; $index++) {
            $byte = $this->source->bytes[$index];
            if ($ignorePrimaryMask > 0 && $byte < 64 && ($ignorePrimaryMask & (1 << $byte))) {
                continue;
            } elseif ($ignoreSecondaryMask > 0 && $byte > 64 && $byte < 128 && ($ignoreSecondaryMask & (1 << ($byte - Splitter::MAP_SHIFT)))) {
                continue;
            }
            $captured .= $source{$index - 1};
        }
        return $captured;
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
        $crop = new Position(
            $position->context,
            $this->splitter->findBytePositionBeforeOffset(Splitter::MASK_LINEBREAKS, $position->index),
            $this->splitter->findBytePositionAfterOffset(Splitter::MASK_LINEBREAKS, $position->index)
        );
        $line = $this->pack($crop) ?? '';
        $character = $position->index - $crop->lastYield - 1;
        $string = 'Line ' . $lines . ' character ' . $character . PHP_EOL;
        $string .= PHP_EOL;
        $string .= str_repeat(' ', max($character, 0)) . 'v' . PHP_EOL;
        $string .= trim($line) . PHP_EOL;
        $string .= str_repeat(' ', max($character, 0)) . '^' . PHP_EOL;
        return $string;
    }

    protected function throwErrorAtPosition(string $message, int $code)
    {
        $character = (string) addslashes($this->source->source{$this->splitter->index});
        $position = new Position($this->splitter->context, 0, $this->splitter->index);
        $ascii = (string) $this->source->bytes[$this->splitter->index];
        $message .=  ' ASCII: ' . $ascii . ': ' . $this->extractSourceDumpOfLineAtPosition($position);
        $error = new SequencingException($message, $code);
        $error->setPosition($position);
        throw $error;
    }

    protected function throwUnsupportedArgumentError(string $argument, array $definitions)
    {
        $this->throwErrorAtPosition(
            sprintf(
                'Unsupported argument "%s". Supported: ' . implode(', ', array_keys($definitions)),
                $argument
            ),
            1558298976
        );
    }

    /**
     * @param \Iterator|?string[] $sequence
     * @return NodeInterface|null
     */
    protected function sequenceRootNodesAsChildrenOfTopStack(\Iterator $sequence): NodeInterface
    {
        // Please note: repeated calls to $this->getTopmostNodeFromStack() are indeed intentional. That method may
        // return different nodes at different times depending on what has occurreded in other methods! Only the places
        // where $node is actually extracted is it (by design) safe to do so. DO NOT REFACTOR!
        foreach ($sequence as $symbol => $captured) {
            switch ($symbol) {
                case Splitter::BYTE_INLINE:
                    $node = $this->state->getNodeFromStack();
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

                default:
                    $this->throwErrorAtPosition(
                        'Unexpected token in root node iteration: ' . addslashes(chr($symbol)) . ' at index ' . $this->splitter->index . ' in context ' . $this->splitter->context->getContextName(),
                        1557700785
                    );
                    break;
            }
        }

        return $this->state->popNodeFromStack();
    }

    /**
     * @param \Iterator|?string[] $sequence
     * @return NodeInterface|null
     */
    protected function sequenceTagNode(\Iterator $sequence): ?NodeInterface
    {
        $closeBytePosition = 0;
        $arguments = [];
        $definitions = null;
        $text = '<';
        $namespace = null;
        $method = null;
        $bytes = &$this->source->bytes;
        $source = &$this->source->source;
        $node = new RootNode();
        $selfClosing = false;
        $closing = false;
        $escapingEnabledBackup = $this->escapingEnabled;

        $interceptionPoint = InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER;

        $this->splitter->switch($this->contexts->tag);
        $sequence->next();
        foreach ($sequence as $symbol => $captured) {
            $text .= $captured;
            switch ($symbol) {
                case Splitter::BYTE_INLINE:
                    $contextBefore = $this->splitter->context;
                    $collected = $this->sequenceInlineNodes($sequence);
                    $this->splitter->switch($contextBefore);
                    if ($this->splitter->context->context === Context::CONTEXT_ATTRIBUTES) {
                        #if (!isset($key)) {
                        #    $key = count($arguments) - 1;
                        #}
                        $arguments[$key ?? (count($arguments) - 1)] = $collected;
                    } else {
                        $node->addChildNode(new TextNode($text));
                        $node->addChildNode($collected);
                        $text = '';
                    }
                    break;

                case Splitter::BYTE_SEPARATOR_EQUALS:
                    $key = $captured;
                    if ($definitions !== null && !isset($definitions[$key])) {
                        $this->throwUnsupportedArgumentError($key, $definitions);
                    }
                    break;

                case Splitter::BYTE_QUOTE_DOUBLE:
                case Splitter::BYTE_QUOTE_SINGLE:
                    $text .= chr($symbol);
                    if (!isset($key)) {
                        $key = $this->sequenceQuotedNode($sequence)->flatten(true);
                        if ($definitions !== null && !isset($definitions[$key])) {
                            $this->throwUnsupportedArgumentError($key, $definitions);
                        }
                    } else {
                        $arguments[$key] = $this->sequenceQuotedNode($sequence)->flatten();
                        unset($key);
                    }
                    break;

                case Splitter::BYTE_TAG_CLOSE:
                    $text .= '/';
                    $closing = true;
                    $selfClosing = $bytes[$this->splitter->index - 1] !== Splitter::BYTE_TAG;
                    $interceptionPoint = InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER;
                    break;

                case Splitter::BYTE_SEPARATOR_COLON:
                    $text .= ':';
                    $namespace = $namespace ?? $captured;
                    break;

                case Splitter::BYTE_TAG_END:
                    $text .= '>';
                    $method = $method ?? $captured;

                    if (!isset($namespace) || !isset($method) || $this->splitter->context->context === Context::CONTEXT_DEAD || $this->renderingContext->getViewHelperResolver()->isNamespaceIgnored($namespace)) {
                        $node->addChildNode(new TextNode($text));
                        return $node;
                    }

                    if ($closing && !$selfClosing) {
                        // Closing byte was more than two bytes back, meaning the tag is NOT self-closing, but is a
                        // closing tag for a previously opened+stacked node. Finalize the node now.
                        $closesNode = $this->state->popNodeFromStack();
                        if ($closesNode instanceof PostponedViewHelperNode && $closesNode->getNamespace() === $namespace && $closesNode->getIdentifier() === $method) {
                            $arguments = $closesNode->getArguments();
                            $viewHelperNode = $closesNode;
                        } else {
                            $this->throwErrorAtPosition(
                                'Mismatched closing tag. Expecting: ' . $namespace . ':' . $method . '. Found: ' . $closesNode->getNamespace() . ':' . $closesNode->getIdentifier(),
                                1557700789
                            );
                        }
                    }

                    if ($this->splitter->context->context === Context::CONTEXT_ATTRIBUTES && $captured !== null) {
                        // We are still capturing arguments and the last yield contained a value. Null-coalesce key
                        // with captured string so object accessor becomes key name (ECMA shorthand literal)
                        $arguments[$key ?? $captured] = $this->createObjectAccessorNodeOrRawValue($captured);
                    }

                    if ($definitions !== null) {
                        $arguments = $this->createArguments($arguments, $definitions);
                    }

                    $this->escapingEnabled = $escapingEnabledBackup;

                    try {
                        if (!isset($viewHelperNode)) {
                            $viewHelperNode = new PostponedViewHelperNode($this->renderingContext, $namespace, $method);
                        }
                        $viewHelper = $viewHelperNode->getUninitializedViewHelper();
                        $viewHelperNode->setArguments($arguments);
                    } catch (\TYPO3Fluid\Fluid\Core\ViewHelper\Exception $exception) {
                        $this->throwErrorAtPosition($exception->getMessage(), $exception->getCode());
                    }

                    if (!$closing) {
                        $this->callInterceptor($viewHelperNode, $interceptionPoint, $this->state);
                        $this->state->pushNodeToStack($viewHelperNode);
                        return null;
                    }

                    $viewHelper::postParseEvent($viewHelperNode, $arguments, $this->state->getVariableContainer());

                    return $viewHelperNode;

                case Splitter::BYTE_WHITESPACE_TAB:
                case Splitter::BYTE_WHITESPACE_RETURN:
                case Splitter::BYTE_WHITESPACE_EOL:
                case Splitter::BYTE_WHITESPACE_SPACE:
                    if ($this->splitter->context->context === Context::CONTEXT_ATTRIBUTES) {
                        if (isset($key)) {
                            // We now have enough to assign the array value and clear our key and value store variables.
                            if ($captured !== null) {
                                $arguments[$key] = $this->createObjectAccessorNodeOrRawValue($captured);
                                unset($key);
                            }
                        } elseif ($captured !== null) {
                            $key = $captured;
                        }
                    } else {
                        $text .= chr($symbol);
                        if (isset($namespace)) {
                            $method = $captured;

                            $this->escapingEnabled = false;
                            $viewHelperNode = new PostponedViewHelperNode($this->renderingContext, $namespace, $method);
                            $definitions = $viewHelperNode->getUninitializedViewHelper($this->renderingContext)->prepareArguments();

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
                        //unset($namespace, $method);
                        $this->splitter->switch($this->contexts->dead);
                    }
                    break;

                default:
                    $this->throwErrorAtPosition('Unexpected token in tag sequencing', 1557700786);
                    break;
            }
        }

        return new TextNode($text);
    }

    /**
     * @param \Iterator|?string[] $sequence
     * @return NodeInterface
     */
    protected function sequenceInlineNodes(\Iterator $sequence, bool $allowArray = true): NodeInterface
    {
        $text = '{';

        $startingPosition = $this->splitter->index;
        $node = null;
        $key = null;
        $namespace = null;
        $method = null;
        $callDetected = false;
        $hasPass = false;
        $hasColon = null;
        $hasEqualsSign = false;
        $hasWhitespace = false;
        $isArray = false;
        $array = [];
        $ignoredEndingBraces = 0;

        $this->splitter->switch($this->contexts->inline);
        $sequence->next();
        foreach ($sequence as $symbol => $captured) {
            $text .= $captured . $this->source->source[$this->splitter->index - 1];
            switch ($symbol) {
                case Splitter::BYTE_BACKSLASH:
                    // Add the next character to the expression and advance the Position index by 1 to skip the next.
                    $text = substr($text, 0, -1) . $this->source->source[$this->splitter->index];
                    ++$this->splitter->index;
                    break;

                case Splitter::BYTE_ARRAY_START:
                    ArrayStart:
                    $isArray = true;

                    // Sequence the node. Pass the "use numeric keys?" boolean based on the current byte. Only array
                    // start creates numeric keys. Inline start with keyless values creates ECMA style {foo:foo, bar:bar}
                    // from {foo, bar}.
                    $array[$key ?? $captured ?? 0] = $node = $this->sequenceArrayNode($sequence, null, $symbol === Splitter::BYTE_ARRAY_START);
                    $this->splitter->switch($this->contexts->inline);
                    unset($key);
                    break;

                case Splitter::BYTE_INLINE:
                    // Encountering this case can mean two things: sub-syntax like {foo.{index}} or array, depending
                    // on presence of either a colon or comma before the inline.
                    if ($hasColon || $isArray) {
                        $isArray = true;
                        $captured = $key ?? $captured ?? $potentialAccessor;
                        // This is a sub-syntax following a colon - meaning it is an array.
                        if ($captured !== null) {
                            goto ArrayStart;
                        }
                    } elseif ($this->splitter->index > ($startingPosition + 1)) {
                        // Ignore one ending additional curly brace. Subtracted in the BYTE_INLINE_END case below.
                        // The expression in this case looks like {{inline}.....} and we capture the curlies.
                        ++$ignoredEndingBraces;
                    } else {
                        goto ArrayStart;
                    }
                    break;

                case Splitter::BYTE_MINUS:
                    break;

                // Case not normally countered in straight up "inline" context, but when encountered, means we have
                // explicitly found a quoted array key - and we extract it.
                case Splitter::BYTE_QUOTE_SINGLE:
                case Splitter::BYTE_QUOTE_DOUBLE:
                    isset($key) ? ($array[$key] = $this->sequenceQuotedNode($sequence)->flatten(true)) & $key = null : $key = $this->sequenceQuotedNode($sequence)->flatten(true);
                    $isArray = true;
                    break;

                case Splitter::BYTE_SEPARATOR_COMMA:
                    $isArray = true;
                    if ($captured !== null) {
                        #if (!isset($key)) {
                        #    $key = $captured;
                        #}
                        $array[$key ?? $captured] = $this->createObjectAccessorNodeOrRawValue($captured);
                        unset($key);
                    }
                    break;

                case Splitter::BYTE_SEPARATOR_EQUALS:
                    $isArray = true;
                    break;

                case Splitter::BYTE_SEPARATOR_COLON:
                    $hasColon = true;
                    $namespace = $key = $captured;
                    break;

                case Splitter::BYTE_WHITESPACE_SPACE:
                case Splitter::BYTE_WHITESPACE_EOL:
                case Splitter::BYTE_WHITESPACE_RETURN:
                case Splitter::BYTE_WHITESPACE_TAB:
                    $hasWhitespace = true;
                    $isArray = $hasColon ?? $isArray ?? is_numeric($captured);
                    $potentialAccessor = $potentialAccessor ?? $captured;
                    break;

                case Splitter::BYTE_INLINE_END:
                    if (--$ignoredEndingBraces >= 0) {
                        break;
                    }
                    $isArray = $isArray ?: ($hasColon && !$hasPass && !$callDetected);

                    // Decision: if we did not detect a ViewHelper we match the *entire* expression, from the cached
                    // starting index, to see if it matches a known type of expression. If it does, we must return the
                    // appropriate type of ExpressionNode.
                    if ($isArray) {
                        if ($captured !== null) {
                            #if (!isset($key)) {
                            #    $key = $captured;
                            #}
                            $array[$key ?? $captured] = $this->createObjectAccessorNodeOrRawValue($captured);
                        }
                        return new ArrayNode($array);
                    }

                    if ($hasWhitespace && !$callDetected) {
                        // In order to qualify for potentially being an expression, the entire inline node must contain
                        // whitespace, must not contain parenthesis, must not contain a colon and must not contain an
                        // inline pass operand. This significantly limits the number of times this (expensive) routine
                        // has to be executed.
                        $interceptionPoint = InterceptorInterface::INTERCEPT_TEXT;
                        $node = new TextNode($text);
                        foreach ($this->renderingContext->getExpressionNodeTypes() as $expressionNodeTypeClassName) {
                            $matchedVariables = [];
                            // TODO: rewrite expression nodes to receive a sub-Splitter that lets the expression node
                            // consume a symbol+capture sequence and either match or ignore it; then use the already
                            // consumed (possibly halted mid-way through iterator!) sequence to achieve desired behavior.
                            preg_match_all($expressionNodeTypeClassName::$detectionExpression, $text, $matchedVariables, PREG_SET_ORDER);
                            foreach ($matchedVariables as $matchedVariableSet) {
                                $expressionNode = new $expressionNodeTypeClassName($matchedVariableSet[0], $matchedVariableSet, $this->state);
                                try {
                                    // Trigger initial parse-time evaluation to allow the node to manipulate the rendering context.
                                    if ($expressionNode instanceof ParseTimeEvaluatedExpressionNodeInterface) {
                                        $expressionNode->evaluate($this->renderingContext);
                                    }

                                    $interceptionPoint = InterceptorInterface::INTERCEPT_EXPRESSION;
                                    $node = $expressionNode;
                                } catch (ExpressionException $error) {
                                    $node = new TextNode($this->renderingContext->getErrorHandler()->handleExpressionError($error));
                                }
                                break;
                            }
                        }
                    } elseif ($node instanceof PostponedViewHelperNode) {
                        $node->setArguments($arguments);
                        $viewHelper = $node->getUninitializedViewHelper();
                        $viewHelper::postParseEvent($node, $arguments, $this->state->getVariableContainer());
                        $interceptionPoint = InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER;
                    } elseif (!$hasPass && !$callDetected) {
                        $potentialAccessor = $potentialAccessor ?? $captured;
                        if (isset($potentialAccessor)) {
                            // If the accessor is set we can trust it is not a numeric value, since this will have
                            // set $isArray to TRUE if nothing else already did so.
                            $node = $this->createObjectAccessorNodeOrRawValue($potentialAccessor);
                            $interceptionPoint = InterceptorInterface::INTERCEPT_OBJECTACCESSOR;
                        } else {
                            $node = new TextNode($text);
                            $interceptionPoint = InterceptorInterface::INTERCEPT_TEXT;
                        }
                    } elseif ($hasPass && $this->renderingContext->getViewHelperResolver()->isAliasRegistered($potentialAccessor)) {
                        $childNodeToAdd = $node;
                        $node = new PostponedViewHelperNode($this->renderingContext, null, $captured, [], $this->state);
                        $node->addChildNode($childNodeToAdd);
                        $viewHelper = $node->getUninitializedViewHelper();
                        $viewHelper::postParseEvent($node, $arguments, $this->state->getVariableContainer());
                        $interceptionPoint = InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER;
                    } else {
                        $node = $this->createObjectAccessorNodeOrRawValue(substr($text, 1, -1));
                        $interceptionPoint = InterceptorInterface::INTERCEPT_OBJECTACCESSOR;
                    }

                    $escapingEnabledBackup = $this->escapingEnabled;
                    $this->escapingEnabled = (bool)(isset($viewHelper) && $viewHelper->isOutputEscapingEnabled());
                    $this->callInterceptor($node, $interceptionPoint, $this->state);
                    $this->escapingEnabled = $escapingEnabledBackup;
                    return $node;

                case Splitter::BYTE_TAG_END:
                case Splitter::BYTE_PIPE:
                    // If there is an accessor on the left side of the pipe and $node is not defined, we create $node
                    // as an object accessor. If $node already exists we do nothing (and expect the VH trigger, the
                    // parenthesis start case below, to add $node as childnode and create a new $node).
                    $hasPass = true;
                    $isArray = false;
                    $potentialAccessor = $potentialAccessor ?? $captured;
                    if ($potentialAccessor !== null && !isset($node)) {
                        $node = $this->createObjectAccessorNodeOrRawValue($potentialAccessor);
                    }
                    unset($namespace, $method, $potentialAccessor, $key, $callDetected);
                    break;

                case Splitter::BYTE_PARENTHESIS_START:
                    $isArray = false;
                    // Special case: if a parenthesis start was preceded by whitespace but had no pass operator we are
                    // not dealing with a ViewHelper call and will continue the sequencing, grabbing the parenthesis as
                    // part of the expression.
                    if (!$hasColon || ($hasWhitespace && !$hasPass)) {
                        unset($namespace, $method);
                        break;
                    }

                    $callDetected = true;
                    $method = $captured;
                    $childNodeToAdd = $node;
                    try {
                        $node = new PostponedViewHelperNode($this->renderingContext, $namespace, $method);
                        $viewHelper = $node->getUninitializedViewHelper();
                        $definitions = $viewHelper->prepareArguments();
                    } catch (\TYPO3Fluid\Fluid\Core\ViewHelper\Exception $exception) {
                        $this->throwErrorAtPosition($exception->getMessage(), $exception->getCode());
                    }
                    $this->splitter->switch($this->contexts->array);
                    $arguments = $this->sequenceArrayNode($sequence, $definitions)->getInternalArray();
                    $node->setArguments($arguments);
                    $this->splitter->switch($this->contexts->inline);
                    if ($childNodeToAdd) {
                        $escapingEnabledBackup = $this->escapingEnabled;
                        $this->escapingEnabled = (bool)$viewHelper->isChildrenEscapingEnabled();
                        if ($childNodeToAdd instanceof ObjectAccessorNode) {
                            $this->callInterceptor($childNodeToAdd, InterceptorInterface::INTERCEPT_OBJECTACCESSOR, $this->state);
                        } elseif ($childNodeToAdd instanceof ExpressionNodeInterface) {
                            $this->callInterceptor($childNodeToAdd, InterceptorInterface::INTERCEPT_EXPRESSION, $this->state);
                        }
                        $this->escapingEnabled = $escapingEnabledBackup;
                        $node->addChildNode($childNodeToAdd);
                    }
                    $text .= ')';
                    unset($potentialAccessor);
                    break;

                default:
                    $this->throwErrorAtPosition('Unexpected token in inline context', 1557700788);
                    break;
            }
        }
        $this->throwErrorAtPosition('Unterminated inline syntax', 1557838506);
        return $node;
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

        $key = null;
        $escapingEnabledBackup = $this->escapingEnabled;
        $this->escapingEnabled = false;
        $itemCount = 0;

        $sequence->next();
        foreach ($sequence as $symbol => $captured) {
            switch ($symbol) {
                case Splitter::BYTE_SEPARATOR_COLON:
                case Splitter::BYTE_SEPARATOR_EQUALS:
                    $key = $key ?? $captured ?? ($numeric ? $itemCount : null);
                    if ($definitions !== null && !isset($definitions[$key])) {
                        $this->throwUnsupportedArgumentError($key, $definitions);
                    }
                    ++$itemCount;
                    break;

                    case Splitter::BYTE_ARRAY_START:
                case Splitter::BYTE_INLINE:
                    $key = $key ?? $captured ?? ($numeric ? $itemCount : null);
                    if ($definitions !== null && !isset($definitions[$key])) {
                        $this->throwUnsupportedArgumentError($key, $definitions);
                    }
                    $array[$key] = $this->sequenceArrayNode($sequence, null, $symbol === Splitter::BYTE_ARRAY_START);
                    unset($key);
                    ++$itemCount;
                    break;

                case Splitter::BYTE_QUOTE_SINGLE:
                case Splitter::BYTE_QUOTE_DOUBLE:
                    if (!isset($key)) {
                        $key = $this->sequenceQuotedNode($sequence)->flatten(true);
                        if ($definitions !== null && !isset($definitions[$key])) {
                            $this->throwUnsupportedArgumentError($key, $definitions);
                        }
                    } else {
                        $array[$key] = $this->sequenceQuotedNode($sequence)->flatten();
                        ++$itemCount;
                        unset($key);
                    }
                    break;

                case Splitter::BYTE_SEPARATOR_COMMA:
                    // Comma separator: if neither key nor value has been collected, the result is an ObjectAccessorNode
                    // which takes the value of the variable that has the same name as the key.
                    if ($captured !== null) {
                        // Comma has an unquoted, non-array value immediately before it. This is what we want to process.
                        $key = $key ?? ($numeric ? $itemCount : $captured);
                        if ($definitions !== null && !isset($definitions[$key])) {
                            $this->throwUnsupportedArgumentError($key, $definitions);
                        }
                        $array[$key] = $this->createObjectAccessorNodeOrRawValue($numeric ? ($captured ?? $key) : $key);
                        #$array[$key] = $this->createObjectAccessorNodeOrRawValue($numeric ? $captured : $key);
                        unset($key);
                        ++$itemCount;
                    }
                    break;

                case Splitter::BYTE_WHITESPACE_TAB:
                case Splitter::BYTE_WHITESPACE_RETURN:
                case Splitter::BYTE_WHITESPACE_EOL:
                case Splitter::BYTE_WHITESPACE_SPACE:
                    $key = $key ?? $captured ?? ($numeric ? $itemCount : null);
                    if (isset($key)) {
                        // We now have enough to assign the array value and clear our key and value store variables.
                        if ($captured !== null) {
                            $array[$key] = $this->createObjectAccessorNodeOrRawValue($captured);
                            ++$itemCount;
                            unset($key);
                        }
                    } elseif ($captured !== null && $definitions !== null && !isset($definitions[$key])) {
                        $this->throwUnsupportedArgumentError($key, $definitions);
                    }
                    break;

                case Splitter::BYTE_TAG_CLOSE:
                case Splitter::BYTE_TAG_END:
                case Splitter::BYTE_INLINE_END:
                case Splitter::BYTE_ARRAY_END:
                case Splitter::BYTE_PARENTHESIS_END:
                    if ($captured !== null) {
                        $key = $key ?? ($numeric ? $itemCount : $captured);
                        $array[$key] = $this->createObjectAccessorNodeOrRawValue($captured);
                        unset($key);
                    }
                    ++$itemCount;
                    if ($definitions !== null) {
                        $array = $this->createArguments($array, $definitions);
                    }

                    $this->escapingEnabled = $escapingEnabledBackup;
                    return new ArrayNode($array);

                default:
                    $this->throwErrorAtPosition('Unexpected token in array context', 1557700791);
                    break;
            }
        }
        $this->throwErrorAtPosition(
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
     * @return RootNode
     */
    protected function sequenceQuotedNode(\Iterator $sequence): RootNode
    {
        $startingByte = $this->source->bytes[$this->splitter->index];
        $contextToRestore = $this->splitter->switch($this->contexts->quoted);
        $node = new RootNode();
        $sequence->next();
        foreach ($sequence as $symbol => $captured) {
            switch ($symbol) {
                case Splitter::BYTE_INLINE:
                    // The quoted string contains a sub-expression. We extract the captured content so far and if it
                    // is not an empty string, we put the value in the $pinnedValue variable and decide what to do
                    // next depending on what happens:
                    // - If we encounter the closing quote and there's no value either before or after, we return
                    //   the pinned value because the inline expression was the only child of the quoted node.
                    // - But if there is content either before the pinned value or after a possible inline node, then
                    //   we add the pinned value as sibline node and return the root node.
                    if ($captured !== null) {
                        $childNode = new TextNode($captured);
                        $this->callInterceptor($childNode, InterceptorInterface::INTERCEPT_TEXT, $this->state);
                        $node->addChildNode($childNode);
                    }

                    $node->addChildNode($this->sequenceInlineNodes($sequence));
                    $this->splitter->switch($this->contexts->quoted);
                    break;

                // Note: although "case $openingQuoteByte:" could have been used here, it would not compile the switch
                // as a hash map and thus would not perform as well overall - when called frequently as it will be.
                case Splitter::BYTE_QUOTE_SINGLE:
                case Splitter::BYTE_QUOTE_DOUBLE:
                    if ($symbol !== $startingByte) {
                        break;
                    }
                    if ($captured !== null) {
                        $childNode = new TextNode(trim($captured, '\\'));
                        $this->callInterceptor($childNode, InterceptorInterface::INTERCEPT_TEXT, $this->state);
                        $node->addChildNode($childNode);
                    }
                    $this->splitter->switch($contextToRestore);
                    return $node;

                default:
                    $this->throwErrorAtPosition('Unexpected token in quoted context', 1557700792);
                    break;
            }
        }
        $this->throwErrorAtPosition('Unterminated quoted expression', 1557700793);
    }

    /**
     * Creates arguments by padding with missing+optional arguments
     * and casting or creating BooleanNode where appropriate. Input
     * array may not contain all arguments - output array will.
     *
     * @param array $arguments
     * @param array $definitions
     * @return array
     */
    protected function createArguments(array $arguments, array $definitions): array
    {
        $missingArguments = [];
        foreach ($definitions as $name => $definition) {
            $argument = &$arguments[$name] ?? null;
            if ($definition->isRequired() && !isset($argument)) {
                // Required but missing argument, causes failure (delayed, to report all missing arguments at once)
                $missingArguments[] = $name;
            } elseif (!isset($argument)) {
                // Argument is optional (required filtered out above), fit it with the default value
                $argument = $definition->getDefaultValue();
            } elseif (($type = $definition->getType()) && ($type === 'bool' || $type === 'boolean')) {
                // Cast the value or create a BooleanNode
                $argument = is_scalar($argument) || is_bool($argument) || is_numeric($argument) ? (bool)$argument : new BooleanNode($argument);
            }
            $arguments[$name] = $argument;
        }
        if (!empty($missingArguments)) {
            $this->throwErrorAtPosition('Required argument(s) not provided: ' . implode(', ', $missingArguments), 1558533510);
        }
        return $arguments;
    }

    /**
     * Returns an integer if the $accessor string is numeric, or returns
     * an ObjectAccessor if it is not.
     *
     * @param string $accessor
     * @return int|float|ObjectAccessorNode
     */
    protected function createObjectAccessorNodeOrRawValue(string $accessor)
    {
        if (is_numeric($accessor)) {
            return $accessor + 0;
        }
        if ($accessor === null) {
            $this->throwErrorAtPosition('Attempt to create an empty object accessor', 1557748262);
        }
        $node = new ObjectAccessorNode($accessor);
        return $node;
    }
}