<?php
declare(strict_types=1);

namespace TYPO3Fluid\Fluid\Core\NewParser;

use cogpowered\FineDiff\Render\Text;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\InterceptorInterface;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ExpressionException;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ExpressionNodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ParseTimeEvaluatedExpressionNodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NumericNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

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
 * to be experienced in the method that uses the return value.
 */
class SequencedTemplateParser extends TemplateParser
{
    /** @var ?Debugger */
    public $debugger;

    /** @var Sequencer */
    private $sequencer;

    /** @var ParsingState */
    private $state;

    public function parse($templateString, $templateIdentifier = null): ParsingState
    {
        $templateString = $this->preProcessTemplateSource($templateString);

        $this->state = $this->getParsingState();
        $this->sequencer = new Sequencer($templateString, $this->debugger);
        $sequence = $this->sequencer->sequence();
        $iterator = new \NoRewindIterator($sequence);
        $node = $this->sequenceRootNodesAsChildrenOfTopStack($iterator);
        if (!$node instanceof RootNode) {
            $child = $node;
            $node = new RootNode();
            $node->addChildNode($child);
        }
        $this->state->setRootNode($node);
        return $this->state;
    }

    protected function throwErrorAtPosition(string $message, int $code, Position $position)
    {
        $character = (string) addslashes($this->sequencer->source->source{$position->index});
        $ascii = (string) $this->sequencer->source->bytes[$position->index];
        $message .=  ' ASCII: ' . $ascii . ': ' . $this->sequencer->extractSourceDumpOfLineAtPosition($position);
        $error = new SequencingException($message, $code);
        $error->setPosition($position);
        throw $error;
    }

    protected function sequenceRootNodesAsChildrenOfTopStack(\Iterator $sequence): NodeInterface
    {
        // Please note: repeated calls to $this->getTopmostNodeFromStack() are indeed intentional. That method may
        // return different nodes at different times depending on what has occurreded in other methods! Only the places
        // where $node is actually extracted is it (by design) safe to do so. DO NOT REFACTOR!
        foreach ($sequence as $symbol => $position) {
            switch ($symbol) {
                case Splitter::BYTE_INLINE:
                    //$content = $this->sequencer->pack($position);
                    $content = $position->captured;
                    $node = $this->state->getNodeFromStack();
                    if ($content !== null) {
                        $node->addChildNode(new TextNode($content));
                    }
                    $node->addChildNode($this->sequenceInlineNodes($sequence));
                    break;
/*
                case Splitter::BYTE_ARRAY_START:
                    $sequence->next();
                    return $this->sequenceArrayNode($sequence);
*/
                case Splitter::BYTE_TAG:
                    //$content = $this->sequencer->pack($position);
                    $content = $position->captured;
                    if ($content !== null) {
                        $this->state->getNodeFromStack()->addChildNode(new TextNode($content));
                    }

                    $childNode = $this->sequenceTagNode($sequence);
                    if ($childNode) {
                        $this->state->getNodeFromStack()->addChildNode($childNode);
                    }
                    break;

                case Splitter::BYTE_NULL:
                    //$content = $this->sequencer->pack($position);
                    $content = $position->captured;
                    if ($content !== '') {
                        $this->state->getNodeFromStack()->addChildNode(new TextNode($content));
                    }
                    break;

                default:
                    $this->throwErrorAtPosition(
                        'Unexpected token in root node iteration: ' . addslashes(chr($symbol)) . ' at index ' . $this->sequencer->position->index . ' in context ' . $position->getContextName(),
                        1557700785,
                        $position
                    );
                    break;
            }
        }

        return $this->state->popNodeFromStack();
    }

    protected function sequenceTagNode(\Iterator $sequence): ?NodeInterface
    {
        $closeBytePosition = 0;
        $arguments = [];
        $text = '<';
        $namespace = null;
        $method = null;
        $node = new RootNode();

        $sequence->next();
        foreach ($sequence as $symbol => $position) {
            switch ($symbol) {
                case Splitter::BYTE_INLINE:
                    //$text .= $this->sequencer->pack($position);
                    $text .= $position->captured;
                    $node->addChildNode(new TextNode($text));
                    $node->addChildNode($this->sequenceInlineNodes($sequence));
                    #$sequence->next();
                    $text = '';
                    unset($namespace, $method);
                    break;

                case Splitter::BYTE_QUOTE_DOUBLE:
                case Splitter::BYTE_QUOTE_SINGLE:
                case Splitter::BYTE_SEPARATOR_EQUALS:
                $text .= $position->captured . chr($symbol);
                    break;

                case Splitter::BYTE_TAG_CLOSE:
                    $closeBytePosition = $position->index;
                    $text .= $position->captured . '/';
                    #$node->appendText('/');
                    break;

                case Splitter::BYTE_SEPARATOR_COLON:
                    if (!isset($namespace)) {
                        //$namespace = $this->sequencer->pack($position, Splitter::MASK_WHITESPACE);
                        $namespace = $position->captured;
                    }
                    $text .= ':';
                    break;

                case Splitter::BYTE_TAG_END:
                    if (!isset($namespace)) {
                        $text .= $position->captured . '>';
                        $node->addChildNode(new TextNode($text));
                        return $node;
                    }

                    $method = $method ?? $this->sequencer->pack($position, Splitter::MASK_WHITESPACE);
                    if ($closeBytePosition > 0 && $this->sequencer->source->bytes[$closeBytePosition - 1] === Splitter::BYTE_TAG) {
                        $closesNode = $this->state->popNodeFromStack();
                        if ($closesNode instanceof ViewHelperNode && $closesNode->getNamespace() === $namespace && $closesNode->getIdentifier() === $method) {
                            ($closesNode->getUninitializedViewHelper())::postParseEvent($closesNode, $closesNode->getArguments(), $this->state->getVariableContainer());
                            $this->callInterceptor($closesNode, InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER, $this->state);
                            return $closesNode;
                        } else {
                            $this->throwErrorAtPosition(
                                'Mismatched closing tag. Expecting: ' . $identifier . '. Found: ' . $closesNode->getNamespace() . ':' . $closesNode->getIdentifier(),
                                1557700789,
                                $position
                            );
                        }
                    }
                case Splitter::BYTE_WHITESPACE_TAB:
                case Splitter::BYTE_WHITESPACE_RETURN:
                case Splitter::BYTE_WHITESPACE_EOL:
                case Splitter::BYTE_WHITESPACE_SPACE:
                    if (isset($namespace)) {
                        //$method = $method ?? $this->sequencer->pack($position, Splitter::MASK_WHITESPACE);
                        $method = $method ?? $position->captured;
                        // A whitespace character, in tag context, means the beginning of an array sequence (which may
                        // or may not contain any items; the next symbol may be a tag end or tag close). We sequence the
                        // arguments array and create a ViewHelper node.

                        if ($symbol !== Splitter::BYTE_TAG_END) {
                            $arguments = $this->sequenceTagAttributes($sequence)->getInternalArray();
                        }
                        if ($this->sequencer->source->bytes[$this->sequencer->position->index] !== Splitter::BYTE_TAG_CLOSE) {
                            $interceptionPoint = InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER;
                            $return = false;
                        } else {
                            // The symbol that caused sequencing of the arguments array to end, was a tag close. Set the
                            // position of the tag closing character to the current byte position. The originally stored
                            // close-byte position will be zero because the closing character came *after* the namespace
                            // and method (e.g. is a self-closing tag).
                            // We must also advance the iterator because the next character will be a tag end which is not
                            // necessary to include.
                            $interceptionPoint = InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER;
                            $return = true;
                            $sequence->next();
                        }

                        $viewHelperNode = new ViewHelperNode($this->renderingContext, $namespace, $method, $arguments, $this->state);
                        ($viewHelperNode->getUninitializedViewHelper())::postParseEvent($viewHelperNode, $arguments, $this->state->getVariableContainer());
                        $this->callInterceptor($viewHelperNode, $interceptionPoint, $this->state);

                        if (!$return) {
                            $this->state->pushNodeToStack($viewHelperNode);
                            return null;
                        }
                        return $viewHelperNode;
                    } else {
                        $text .= $position->captured . chr($symbol);
                        #unset($namespace, $method);
                    }
                    break;

                default:
                    $this->throwErrorAtPosition(
                        'Unexpected token in tag sequencing: ' . addslashes(chr($symbol)),
                        1557700786,
                        $position
                    );
                    break;
            }
        }

        return new TextNode($text);
    }

    /**
     * @param \Iterator|Position[] $sequence
     * @return ArrayNode
     */
    protected function sequenceTagAttributes(\Iterator $sequence): ArrayNode
    {
        $array = [];

        foreach ($sequence as $symbol => $position) {
            switch ($symbol) {
                case Splitter::BYTE_INLINE:
                    if (!isset($key)) {
                        /*
                        $this->throwErrorAtPosition(
                            'Unexpected beginning of array without a preceding key',
                            1557754838,
                            $position
                        );
                        */
                        $key = 0;
                    }
                    #$sequence->next();
                    $array[$key] = $this->sequenceInlineNodes($sequence);
                    break;

                case Splitter::BYTE_SEPARATOR_COLON:
                case Splitter::BYTE_SEPARATOR_EQUALS:
                    //$key = $this->sequencer->pack($position, Splitter::MASK_WHITESPACE | Splitter::MASK_SEPARATORS);
                    $key = $position->captured;
                    break;

                case Splitter::BYTE_QUOTE_SINGLE:
                case Splitter::BYTE_QUOTE_DOUBLE:
                    if (!isset($key)) {
                        $key = $this->sequenceQuotedNode($sequence)->flatten(true);
                    } else {
                        $array[$key] = $this->sequenceQuotedNode($sequence)->flatten();
                        $ignoreWhitespaceUntilValueFound = false;
                        unset($key);
                    }
                    break;

                case Splitter::BYTE_SEPARATOR_COMMA:
                    // Comma separator: if neither key nor value has been collected, the result is an ObjectAccessorNode
                    // which takes the value of the variable that has the same name as the key.
                    //$captured = $this->sequencer->pack($position); // , 0, Splitter::MASK_BACKSLASH
                    $captured = $position->captured;
                    if ($captured !== null) {
                        // Comma has an unquoted, non-array value immediately before it. This is what we want to process.
                        if (!isset($key)) {
                            $key = $captured;
                        }
                        $array[$key] = $this->createObjectAccessorNodeOrRawValue($captured, $position);
                        unset($key);
                    }
                    break;

                case Splitter::BYTE_WHITESPACE_TAB:
                case Splitter::BYTE_WHITESPACE_RETURN:
                case Splitter::BYTE_WHITESPACE_EOL:
                case Splitter::BYTE_WHITESPACE_SPACE:
                    //$captured = $this->sequencer->pack($position, Splitter::MASK_WHITESPACE | Splitter::MASK_SEPARATORS);
                    $captured = $position->captured;
                    if (isset($key)) {

                        // We now have enough to assign the array value and clear our key and value store variables.
                        if ($captured !== null) {
                            $array[$key] = $this->createObjectAccessorNodeOrRawValue($captured, $position);
                            unset($key);
                        }
                    } elseif ($captured !== null) {
                        $key = $captured;
                    }
                    break;

                case Splitter::BYTE_TAG_CLOSE:
                case Splitter::BYTE_TAG_END:
                    if (isset($key)) {
                        // We now have enough to assign the array value and clear our key and value store variables.
                        //$captured = $this->sequencer->pack($position, Splitter::MASK_WHITESPACE | Splitter::MASK_SEPARATORS, Splitter::MASK_BACKSLASH);
                        $captured = $position->captured;
                        if ($captured !== '') {
                            $array[$key] = $this->createObjectAccessorNodeOrRawValue($captured, $position);
                        }
                    }
                    return new ArrayNode($array);

                default:
                    $this->throwErrorAtPosition(
                        'Unexpected token in attributes context: ' . addslashes(chr($symbol)),
                        1557700791,
                        $position
                    );
                    break;
            }
        }
        $this->throwErrorAtPosition(
            'Unterminated tag',
            1557748574,
            $position
        );
    }

    /**
     * @param \Iterator|Position[] $sequence
     * @return NodeInterface
     */
    protected function sequenceInlineNodes(\Iterator $sequence): NodeInterface
    {
        $startingIndex = $this->sequencer->position->index;

        $node = null;
        $key = null;
        $namespace = null;
        $method = null;
        $callDetected = false;

        $sequence->next();

        foreach ($sequence as $symbol => $position) {
            $childNodeToAdd = $node;
            switch ($symbol) {
                // Case not normally countered in straight up "inline" context, but when encountered, means we have
                // explicitly found a quoted array key - and we extract it.
                case Splitter::BYTE_QUOTE_SINGLE:
                case Splitter::BYTE_QUOTE_DOUBLE:
                    $key = $this->sequenceQuotedNode($sequence)->flatten(true);
                    break;

                case Splitter::BYTE_SEPARATOR_COMMA:
                case Splitter::BYTE_SEPARATOR_COLON:
                case Splitter::BYTE_SEPARATOR_EQUALS:
                    $captured = $key ?? $potentialAccessor ?? $this->sequencer->pack($position);
                    if ($position->context->context === Context::CONTEXT_ARRAY) {
                        // Encountering this case means we have switched to an array context - finalize the node by
                        // sequencing the array, extract the key if not already extracted and pass it to the array sequencer.
                        $sequence->next();
                        return $this->sequenceArrayNode($sequence, $captured);
                    } else {
                        // We are likely sequencing a ViewHelper; store a potential namespace name and expect an
                        // opening parenthesis.
                        $namespace = $captured;
                    }
                    break;

                case Splitter::BYTE_INLINE_END:
                    // Decision: if we did not detect a ViewHelper we match the *entire* expression, from the cached
                    // starting index, to see if it matches a known type of expression. If it does, we must return the
                    // appropriate type of ExpressionNode.
                    if (!$callDetected) {
                        $entirePosition = $position->pad($position->index - ($startingIndex + 1), 1);
                        $section = $this->sequencer->pack($entirePosition);
                        foreach ($this->renderingContext->getExpressionNodeTypes() as $expressionNodeTypeClassName) {
                            $matchedVariables = [];
                            preg_match_all($expressionNodeTypeClassName::$detectionExpression, $section, $matchedVariables, PREG_SET_ORDER);
                            foreach ($matchedVariables as $matchedVariableSet) {
                                $expressionNode = new $expressionNodeTypeClassName($matchedVariableSet[0], $matchedVariableSet, $this->state);
                                #try {
                                    // Trigger initial parse-time evaluation to allow the node to manipulate the rendering context.
                                    if ($expressionNode instanceof ParseTimeEvaluatedExpressionNodeInterface) {
                                        $expressionNode->evaluate($this->renderingContext);
                                    }

                                    $this->callInterceptor($expressionNode, InterceptorInterface::INTERCEPT_EXPRESSION, $this->state);
                                    #$sequence->next();
                                    return $expressionNode;

                                #} catch (ExpressionException $error) {
                                #    $this->textHandler(
                                #        $state,
                                #        $this->renderingContext->getErrorHandler()->handleExpressionError($error)
                                #    );
                                #}
                            }

                        }
                        //$node = new ObjectAccessorNode($this->sequencer->pack($position));
                        $node = new ObjectAccessorNode($position->captured);
                        $this->callInterceptor(
                            $node,
                            InterceptorInterface::INTERCEPT_OBJECTACCESSOR,
                            $this->state
                        );
                    } else {
                        //$potentialAccessor = $potentialAccessor ?? $this->sequencer->pack($position);
                        $potentialAccessor = $potentialAccessor ?? $position->captured;
                        if ($potentialAccessor !== null) {
                            if ($node !== null) {
                                $this->throwErrorAtPosition(
                                    'Attempt to pipe a value into an object accessor - you may only pipe to ViewHelpers',
                                    1557740018,
                                    $position
                                );
                            }
                            $node = new ObjectAccessorNode($potentialAccessor);
                            $this->callInterceptor(
                                $node,
                                InterceptorInterface::INTERCEPT_OBJECTACCESSOR,
                                $this->state
                            );
                        } elseif ($node instanceof ViewHelperNode) {
                            ($node->getUninitializedViewHelper())::postParseEvent($node, $arguments, $this->state->getVariableContainer());
                            $this->callInterceptor($node, InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER, $this->state);
                        } else {
                            #echo $this->sequencer->source->source;
                            #var_dump($captured);
                            var_dump($node);
                            exit();
                        }
                    }

                    return $node;

                case Splitter::BYTE_PIPE:
                    // If there is an accessor on the left side of the pipe we make the $node into an ObjectAccessorNode,
                    // the next iteration may then call a ViewHelper (OK) or attempt to pass to another object access (FAIL)
                    //$potentialAccessor = $potentialAccessor ?? $this->sequencer->pack($position, Splitter::MASK_WHITESPACE);
                    $potentialAccessor = $potentialAccessor ?? $position->captured;
                    if (!empty($potentialAccessor)) {
                        $node = new ObjectAccessorNode($potentialAccessor);
                        $this->callInterceptor(
                            $node,
                            InterceptorInterface::INTERCEPT_OBJECTACCESSOR,
                            $this->state
                        );
                    }
                    unset($namespace, $method, $potentialAccessor);
                    break;

                case Splitter::BYTE_PARENTHESIS_START:
                    $callDetected = true;
                    //$method = $this->sequencer->pack($position);
                    $method = $position->captured;

                    $sequence->next();
                    $arguments = $this->sequenceArrayNode($sequence)->getInternalArray();
                    $node = new ViewHelperNode($this->renderingContext, $namespace, $method, $arguments, $this->state);
                    ($node->getUninitializedViewHelper())::postParseEvent($node, $arguments, $this->state->getVariableContainer());
                    // The node is not intercepted yet - only the last node needs to be intercepted.
                    if ($childNodeToAdd) {
                        $node->addChildNode($childNodeToAdd);
                    }
                    unset($potentialAccessor);
                    break;

                case Splitter::BYTE_WHITESPACE_SPACE:
                case Splitter::BYTE_WHITESPACE_EOL:
                case Splitter::BYTE_WHITESPACE_RETURN:
                case Splitter::BYTE_WHITESPACE_TAB:
                    //$captured = $this->sequencer->pack($position, Splitter::MASK_WHITESPACE);
                    $potentialAccessor = $potentialAccessor ?? $position->captured;
                    break;

                default:
                    $this->throwErrorAtPosition(
                        'Unexpected token in inline context: ' . addslashes(chr($symbol)),
                        1557700788,
                        $position
                    );
                    break;
            }
        }
        $this->throwErrorAtPosition(
            'Unterminated inline syntax: ' . addslashes(chr($symbol)),
            1557838506,
            $position
        );
        return $node;
    }

    /**
     * @param \Iterator|Position[] $sequence
     * @param ?string $key
     * @return ArrayNode
     */
    protected function sequenceArrayNode(\Iterator $sequence, ?string $key = null): ArrayNode
    {
        $array = [];

        $ignoreWhitespaceUntilValueFound = $key !== null;

        $escapingEnabledBackup = $this->escapingEnabled;
        $this->escapingEnabled = false;

        foreach ($sequence as $symbol => $position) {
            switch ($symbol) {
                case Splitter::BYTE_ARRAY_START:
                case Splitter::BYTE_INLINE:
                    if (!isset($key)) {
                        /*
                        $this->throwErrorAtPosition(
                            'Unexpected beginning of array without a preceding key',
                            1557754838,
                            $position
                        );
                        */
                        $key = 0;
                    }
                    $sequence->next();
                    $array[$key] = $this->sequenceArrayNode($sequence);
                    break;

                case Splitter::BYTE_SEPARATOR_COLON:
                case Splitter::BYTE_SEPARATOR_EQUALS:
                    //$key = $this->sequencer->pack($position, Splitter::MASK_WHITESPACE | Splitter::MASK_SEPARATORS);
                    $key = $key ?? trim($position->captured);
                    break;

                case Splitter::BYTE_QUOTE_SINGLE:
                case Splitter::BYTE_QUOTE_DOUBLE:
                    if (!isset($key)) {
                        $key = $this->sequenceQuotedNode($sequence)->flatten(true);
                    } else {
                        $array[$key] = $this->sequenceQuotedNode($sequence)->flatten();
                        $ignoreWhitespaceUntilValueFound = false;
                        unset($key);
                    }
                    break;

                case Splitter::BYTE_SEPARATOR_COMMA:
                    // Comma separator: if neither key nor value has been collected, the result is an ObjectAccessorNode
                    // which takes the value of the variable that has the same name as the key.
                    //$captured = $this->sequencer->pack($position); // , 0, Splitter::MASK_BACKSLASH
                    $captured = $position->captured;
                    $ignoreWhitespaceUntilValueFound = true;
                    if ($captured !== null) {
                        // Comma has an unquoted, non-array value immediately before it. This is what we want to process.
                        if (!isset($key)) {
                            $key = $captured;
                        }
                        $array[$key] = $this->createObjectAccessorNodeOrRawValue($captured, $position);
                        $ignoreWhitespaceUntilValueFound = false;
                        unset($key);
                    }
                    break;

                case Splitter::BYTE_WHITESPACE_TAB:
                case Splitter::BYTE_WHITESPACE_RETURN:
                case Splitter::BYTE_WHITESPACE_EOL:
                case Splitter::BYTE_WHITESPACE_SPACE:
                    //$captured = $this->sequencer->pack($position, Splitter::MASK_WHITESPACE | Splitter::MASK_SEPARATORS);
                    $captured = $position->captured;
                    if (isset($key)) {
                        if ($ignoreWhitespaceUntilValueFound) {
                            break;
                        }

                        // We now have enough to assign the array value and clear our key and value store variables.
                        if ($captured !== null) {
                            $array[$key] = $this->createObjectAccessorNodeOrRawValue($captured, $position);
                            unset($key);
                        }
                    } elseif ($captured !== null) {
                        $key = $key ?? $captured;
                    }
                    break;

                case Splitter::BYTE_TAG_CLOSE:
                case Splitter::BYTE_TAG_END:
                case Splitter::BYTE_INLINE_END:
                case Splitter::BYTE_ARRAY_END:
                case Splitter::BYTE_PARENTHESIS_END:
                    if (isset($key)) {
                        // We now have enough to assign the array value and clear our key and value store variables.
                        $captured = $this->sequencer->pack($position, Splitter::MASK_WHITESPACE | Splitter::MASK_SEPARATORS, Splitter::MASK_BACKSLASH);
                        if ($captured !== null) {
                            $array[$key] = $this->createObjectAccessorNodeOrRawValue($captured, $position);
                        }
                    }
                    $this->escapingEnabled = $escapingEnabledBackup;
                    return new ArrayNode($array);

                default:
                    $this->throwErrorAtPosition(
                        'Unexpected token in array context: ' . addslashes(chr($symbol)),
                        1557700791,
                        $position
                    );
                    break;
            }
        }
        $this->throwErrorAtPosition(
            'Unterminated array',
            1557748574,
            $position
        );
    }

    /**
     * Returns an integer if the $accessor string is numeric, or returns
     * an ObjectAccessor if it is not.
     *
     * @param string $accessor
     * @param Position $position
     * @return int|string
     */
    protected function createObjectAccessorNodeOrRawValue(string $accessor, Position $position)
    {
        if (is_numeric($accessor)) {
            return $accessor + 0;
        }
        if ($accessor === null) {
            $this->throwErrorAtPosition('Attempt to create an empty object accessor', 1557748262, $position);
        }
        $node = new ObjectAccessorNode($accessor);
        $this->callInterceptor($node, InterceptorInterface::INTERCEPT_OBJECTACCESSOR, $this->state);
        return $node;
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
        $node = new RootNode();
        $sequence->next();
        foreach ($sequence as $symbol => $position) {
            switch ($symbol) {
                case Splitter::BYTE_INLINE:
                    // The quoted string contains a sub-expression. We extract the captured content so far and if it
                    // is not an empty string, we put the value in the $pinnedValue variable and decide what to do
                    // next depending on what happens:
                    // - If we encounter the closing quote and there's no value either before or after, we return
                    //   the pinned value because the inline expression was the only child of the quoted node.
                    // - But if there is content either before the pinned value or after a possible inline node, then
                    //   we add the pinned value as sibline node and return the root node.
                    //$captured = $this->sequencer->pack($position);
                    $captured = $position->captured;
                    if ($captured !== null) {
                        $childNode = new TextNode($captured);
                        $this->callInterceptor($childNode, InterceptorInterface::INTERCEPT_TEXT, $this->state);
                        $node->addChildNode($childNode);
                    }

                    $node->addChildNode($this->sequenceInlineNodes($sequence));
                    break;

                case Splitter::BYTE_ARRAY_START:
                    $sequence->next();
                    $node->addChildNode($this->sequenceArrayNode($sequence));
                    break;

                // Note: although "case $openingQuoteByte:" could have been used here, it would not compile the switch
                // as a hash map and thus would not perform as well overall - when called frequently as it will be.
                case Splitter::BYTE_QUOTE_SINGLE:
                case Splitter::BYTE_QUOTE_DOUBLE:
                    #$captured = $this->sequencer->pack($position, 0, Splitter::MASK_BACKSLASH);
                    $captured = $position->captured;
                    if ($captured !== null) {
                        $childNode = new TextNode(trim($captured, '\\'));
                        $this->callInterceptor($childNode, InterceptorInterface::INTERCEPT_TEXT, $this->state);
                        $node->addChildNode($childNode);
                    }
                    return $node;

                default:
                    $this->throwErrorAtPosition(
                        'Unexpected token in quoted context: ' . addslashes(chr($symbol)),
                        1557700792,
                        $position
                    );
                    break;
            }
        }
        $this->throwErrorAtPosition('Unterminated quoted expression', 1557700793, $position);
    }
}