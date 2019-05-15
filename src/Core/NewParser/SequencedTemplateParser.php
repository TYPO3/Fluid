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
 * Structure outline:
 *
 * 1. Enters a function with \Iterator as argument and starts iterating.
 *    A new function is called for every section - not just because this
 *    makes it easier to perceive what goes on, but also to create an
 *    isolated scope for each section (for example, making sure that once
 *    an array is done, its values will never "bleed" to other arrays).
 * 2. Each function covers a section of template code - for example, a
 *    tag, an inline expression, an array or something quoted.
 * 3. Whenever the section of template code is determined to have ended,
 *    for example when the "inline" function encounters a closing curly
 *    brace, the function returns (IMPORTANT!).
 * 4. If the function calls $sequence->next() immediately before the
 *    return statement this means that whatever called the function must
 *    continue from the *next* matched symbol.
 * 5. If it does not call $sequence->next() then whichever method called
 *    the method determines what to do.
 * 6. Rule of thumb: when something gets returned, we next() the sequence
 *    so the caller method doesn't have to, simply because a return means
 *    a logical context was exited and the caller should naturally go on
 *    to the next symbol in the sequence.
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
        $this->state->pushNodeToStack(new RootNode());
        $node = $this->sequenceRootNodesAsChildrenOfTopStack($iterator);
        if (!$node instanceof RootNode) {
            $this->throwErrorAtPosition('A node was not closed (' . get_class($node) . ')', 1557700794, $this->sequencer->position);
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

    protected function sequenceRootNodesAsChildrenOfTopStack(\Iterator $sequence, bool $pop = true): NodeInterface
    {
        // Please note: repeated calls to $this->getTopmostNodeFromStack() are indeed intentional. That method may
        // return different nodes at different times depending on what has occurreded in other methods! Only the places
        // where $node is actually extracted is it (by design) safe to do so. DO NOT REFACTOR!
        foreach ($sequence as $symbol => $position) {
            switch ($symbol) {
                case Splitter::BYTE_INLINE:
                    $content = $this->sequencer->pack($position);
                    $node = $this->state->getNodeFromStack();
                    if ($content !== '') {
                        $node->addChildNode(new TextNode($content));
                    }
                    $node->addChildNode($this->sequenceInlineNodes($sequence));
                    break;

                case Splitter::BYTE_TAG:
                    $content = $this->sequencer->pack($position);
                    if ($content !== '') {
                        $this->state->getNodeFromStack()->addChildNode(new TextNode($content));
                    }

                    $childNode = $this->sequenceTagNode($sequence);
                    $node = $this->state->getNodeFromStack();
                    if ($node !== $childNode) {
                        // Prevent adding a node as child of itself; $this->sequenceTagNode() may return the same node
                        // we extracted to $node. If that happens, we expect $pop to be true which means that exiting
                        // this method removes the current node from the stack to clean up after itself.
                        $node->addChildNode($childNode);
                    }
                    break;

                case Splitter::BYTE_NULL:
                    $content = $this->sequencer->pack($position);
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
            }
        }

        return $pop ? $this->state->popNodeFromStack() : $this->state->getNodeFromStack();
    }

    protected function sequenceTagNode(\Iterator $sequence): NodeInterface
    {
        $closeBytePosition = 0;
        $node = new RootNode();
        $nodesAdded = false;

        $sequence->next();
        foreach ($sequence as $symbol => $position) {
            switch ($symbol) {
                case Splitter::BYTE_INLINE:
                    $content = $this->sequencer->pack($position);
                    $childNode = new TextNode('<' . $content);
                    $this->callInterceptor($node, InterceptorInterface::INTERCEPT_TEXT, $this->state);
                    $node->addChildNode($childNode);
                    $node->addChildNode($this->sequenceInlineNodes($sequence));
                    $nodesAdded = true;
                    $closeBytePosition = 0;
                    break;

                case Splitter::BYTE_TAG_CLOSE:
                    $closeBytePosition = $position->index;
                    break;

                case Splitter::BYTE_TAG_END:
                case Splitter::BYTE_WHITESPACE_TAB:
                case Splitter::BYTE_WHITESPACE_RETURN:
                case Splitter::BYTE_WHITESPACE_EOL:
                case Splitter::BYTE_WHITESPACE_SPACE:
                case Splitter::BYTE_PARENTHESIS_START:
                    $identifier = $this->sequencer->pack($position, Splitter::MASK_WHITESPACE);
                    if ($position->context->context !== Context::CONTEXT_TAG_INACTIVE) {
                        if ($symbol === Splitter::BYTE_TAG_END) {
                            # $sequence->next();
                        }
                        $node = $this->sequenceViewHelperNode($sequence, $identifier, $closeBytePosition);
                        return $node;
                    }
                    // The tag is re-created while adding the symbols that were not captured.
                    $childNode = new TextNode(($nodesAdded ? '' : '<') . $this->sequencer->pack($position) . '>');
                    $this->callInterceptor($node, InterceptorInterface::INTERCEPT_TEXT, $this->state);
                    $node->addChildNode($childNode);
                    $closeBytePosition = 0;
                    return $node;

                default:
                    $this->throwErrorAtPosition(
                        'Unexpected token while awaiting terminator for namespaced tag: ' . addslashes(chr($symbol)),
                        1557700786,
                        $position
                    );
                    break;
            }
        }
        $this->throwErrorAtPosition(
            'An unknown namespaced tag node was encountered which did not switch to ViewHelper context',
            1557700787,
            $position
        );
    }

    protected function sequenceInlineNodes(\Iterator $sequence): NodeInterface
    {
        $startingIndex = $this->sequencer->position->index;
        $sequence->next();

        $node = null;
        $key = null;
        $namespace = null;
        $method = null;
        $callDetected = false;
        foreach ($sequence as $symbol => $position) {
            $childNodeToAdd = $node;
            switch ($symbol) {
                // Case not normally countered in straight up "inline" context, but when encountered, means we have
                // explicitly found a quoted array key - and we extract it.
                case Splitter::BYTE_QUOTE_SINGLE:
                case Splitter::BYTE_QUOTE_DOUBLE:
                    $key = $this->sequenceQuotedNode($sequence)->flatten();
                    break;

                case Splitter::BYTE_SEPARATOR_COLON:
                case Splitter::BYTE_SEPARATOR_EQUALS:
                    $captured = $key ?? $this->sequencer->pack($position);
                    if ($position->context->context === Context::CONTEXT_ARRAY) {
                        // Encountering this case means we have switched to an array context - finalize the node by
                        // sequencing the array, extract the key if not already extracted and pass it to the array sequencer.
                        #$sequence->next();
                        return new ArrayNode($this->sequenceArrayNode($sequence, $captured));
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
                        $node = new ObjectAccessorNode($this->sequencer->pack($position));
                        $this->callInterceptor(
                            $node,
                            InterceptorInterface::INTERCEPT_OBJECTACCESSOR,
                            $this->state
                        );
                        #$sequence->next();
                        #return $node;
                    } else {
                        $potentialAccessor = $potentialAccessor ?? $this->sequencer->pack($position);
                        if ($potentialAccessor !== '') {
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
                            $this->callInterceptor(
                                $node,
                                InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER,
                                $this->state
                            );
                        } else {
                            #echo $this->sequencer->source->source;
                            var_dump($captured);
                            var_dump($node);
                            exit();
                        }
                    }

                    #$sequence->next();
                    return $node;

                case Splitter::BYTE_PIPE:
                    // If there is an accessor on the left side of the pipe we make the $node into an ObjectAccessorNode,
                    // the next iteration may then call a ViewHelper (OK) or attempt to pass to another object access (FAIL)
                    $potentialAccessor = $potentialAccessor ?? $this->sequencer->pack($position, Splitter::MASK_WHITESPACE);
                    if (!empty($potentialAccessor)) {
                        $node = new ObjectAccessorNode($potentialAccessor);
                        $this->callInterceptor(
                            $node,
                            InterceptorInterface::INTERCEPT_OBJECTACCESSOR,
                            $this->state
                        );
                    }
                    break;

                case Splitter::BYTE_PARENTHESIS_START:
                    $callDetected = true;
                    $method = $this->sequencer->pack($position);

                    $sequence->next();
                    $arguments = $this->sequenceArrayNode($sequence);
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
                    $captured = $this->sequencer->pack($position, Splitter::MASK_WHITESPACE);
                    if ($captured !== '') {
                        $potentialAccessor = $captured;
                    }
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

    protected function sequenceViewHelperNode(\Iterator $sequence, string $identifier, int $closeBytePosition): NodeInterface
    {
        $arguments = [];
        $node = null;

        list ($namespace, $method) = explode(':', $identifier);

        foreach ($sequence as $symbol => $position) {
            switch ($symbol) {
                // Whitespace - has different meaning depending on context (no meaning outside of tag context)
                case Splitter::BYTE_WHITESPACE_TAB:
                case Splitter::BYTE_WHITESPACE_RETURN:
                case Splitter::BYTE_WHITESPACE_EOL:
                case Splitter::BYTE_WHITESPACE_SPACE:
                    if ($position->context->context === Context::CONTEXT_TAG) {
                        // A whitespace character, in tag context, means the beginning of an array sequence (which may
                        // or may not contain any items; the next symbol may be a tag end or tag close). We sequence the
                        // arguments array and create a ViewHelper node.

                        #$sequence->next();
                        $arguments = $this->sequenceArrayNode($sequence);
                        $node = new ViewHelperNode($this->renderingContext, $namespace, $method, $arguments, $this->state);
                    }

                    // If the character that caused array sequencing to stop was a tag close "/" character, break and allow
                    // the loop to enter the next iteration (which expects a ">" character).
                    if ($this->sequencer->source->bytes[$this->sequencer->position->index] === Splitter::BYTE_TAG_CLOSE) {
                        break;
                    }

                // Otherwise we intentionally allow fall-through to the next case which will finalise the node.

                case Splitter::BYTE_TAG_END:
                    // Check the byte that exists at the "lastyield" position. If it is a tag close, we declare the
                    // ViewHelper as self-closing.

                    $closingTag = $this->sequencer->source->bytes[$position->index - 1] === Splitter::BYTE_TAG_CLOSE
                        || ($closeBytePosition > 0 && $this->sequencer->source->bytes[$closeBytePosition - 1] === Splitter::BYTE_TAG);

                    if ($closingTag) {
                        $closesNode = $node ?? $this->state->popNodeFromStack();
                        if ($closesNode instanceof ViewHelperNode && $closesNode->getNamespace() === $namespace && $closesNode->getIdentifier() === $method) {
                            ($closesNode->getUninitializedViewHelper())::postParseEvent($closesNode, $closesNode->getArguments(), $this->state->getVariableContainer());
                            $this->callInterceptor(
                                $closesNode,
                                InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER,
                                $this->state
                            );
                            return $closesNode;
                        } else {
                            $this->throwErrorAtPosition(
                                'Mismatched closing tag. Expecting: ' . $identifier . '. Found: ' . $closesNode->getNamespace() . ':' . $closesNode->getIdentifier(),
                                1557700789,
                                $position
                            );
                        }
                    }

                    $node = new ViewHelperNode($this->renderingContext, $namespace, $method, $arguments, $this->state);
                    if ($closingTag) {
                        // The tag is a self-closing tag, return it without adding it to the stack.
                        #$sequence->next();
                        ($node->getUninitializedViewHelper())::postParseEvent($node, $arguments, $this->state->getVariableContainer());
                        $this->callInterceptor(
                            $node,
                            InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER,
                            $this->state
                        );
                        return $node;
                    }

                    $this->callInterceptor(
                        $node,
                        InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER,
                        $this->state
                    );

                    // The ViewHelper was not closed. Push the node to the stack and await a closing node.
                    $this->state->pushNodeToStack($node);
                    $sequence->next();
                    return $this->sequenceRootNodesAsChildrenOfTopStack($sequence, false);

                case Splitter::BYTE_PARENTHESIS_START:
                    $method = $this->sequencer->pack($position);

                    $sequence->next();
                    $this->setEscapingEnabled(false);
                    $arguments = $this->sequenceArrayNode($sequence);
                    $node = new ViewHelperNode($this->renderingContext, $namespace, $method, $arguments, $this->state);
                    $this->callInterceptor(
                        $node,
                        InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER,
                        $this->state
                    );
                    return $node;

                case Splitter::BYTE_TAG_CLOSE:
                    $closeBytePosition = max($closeBytePosition, $position->index);
                    break;

                default:
                    $this->throwErrorAtPosition(
                        'Unexpected token in ViewHelper context: ' . addslashes(chr($symbol)),
                        1557700790,
                        $position
                    );
            }
        }

        if ($node instanceof ViewHelperNode) {
            $this->callInterceptor(
                $node,
                InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER,
                $this->state
            );
        }

        die('fuuuuuuuuuuuuu');
        return $node;
    }

    protected function sequenceArrayNode(\Iterator $sequence, ?string $key = null): array
    {
        $array = [];

        $ignoreWhitespaceUntilValueFound = $key !== null;

        $escapingEnabledBackup = $this->escapingEnabled;
        $this->escapingEnabled = false;

        foreach ($sequence as $symbol => $position) {
            switch ($symbol) {
                case Splitter::BYTE_INLINE:
                    if (!isset($key)) {
                        $this->throwErrorAtPosition(
                            'Unexpected beginning of array without a preceding key',
                            1557754838,
                            $position
                        );
                    }
                    $sequence->next();
                    $array[$key] = $this->sequenceArrayNode($sequence);
                    break;

                case Splitter::BYTE_SEPARATOR_COLON:
                case Splitter::BYTE_SEPARATOR_EQUALS:
                    $key = $this->sequencer->pack($position, Splitter::MASK_WHITESPACE | Splitter::MASK_SEPARATORS);
                    break;

                case Splitter::BYTE_QUOTE_SINGLE:
                case Splitter::BYTE_QUOTE_DOUBLE:
                    if (!isset($key)) {
                        $key = $this->sequenceQuotedNode($sequence)->flatten();
                    } else {
                        $array[$key] = $this->sequenceQuotedNode($sequence)->flatten();
                        $ignoreWhitespaceUntilValueFound = false;
                        unset($key);
                    }
                    break;

                case Splitter::BYTE_SEPARATOR_COMMA:
                    // Comma separator: if neither key nor value has been collected, the result is an ObjectAccessorNode
                    // which takes the value of the variable that has the same name as the key.
                    $captured = $this->sequencer->pack($position);
                    if ($captured !== '') {
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
                    $captured = $this->sequencer->pack($position, Splitter::MASK_WHITESPACE | Splitter::MASK_SEPARATORS);
                    if (isset($key)) {
                        if ($ignoreWhitespaceUntilValueFound) {
                            break;
                        }

                        // We now have enough to assign the array value and clear our key and value store variables.

                        if ($captured !== '') {
                            $array[$key] = $this->createObjectAccessorNodeOrRawValue($captured, $position);
                            unset($key);
                        }
                    } elseif ($captured !== '') {
                        $key = $captured;
                    }
                    break;

                case Splitter::BYTE_TAG_CLOSE:
                case Splitter::BYTE_TAG_END:
                case Splitter::BYTE_INLINE_END:
                case Splitter::BYTE_PARENTHESIS_END:
                    if (isset($key)) {
                        // We now have enough to assign the array value and clear our key and value store variables.
                        $captured = $this->sequencer->pack($position, Splitter::MASK_WHITESPACE | Splitter::MASK_SEPARATORS);
                        if ($captured !== '') {
                            $array[$key] = $this->createObjectAccessorNodeOrRawValue($captured, $position);
                        }
                        #$array[$key] = $captured;
                    }
                    if ($symbol === Splitter::BYTE_TAG_CLOSE) {
                    #    $sequence->next();
                    }
                    #unset($key);
                    $this->escapingEnabled = $escapingEnabledBackup;
                    return $array;

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
        if ($accessor === '') {
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
     * @param \Iterator $sequence
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
                    $captured = $this->sequencer->pack($position);
                    if ($captured !== '' ) {
                        $childNode = new TextNode($captured);
                        $this->callInterceptor($childNode, InterceptorInterface::INTERCEPT_TEXT, $this->state);
                        $node->addChildNode($childNode);
                    }

                    $node->addChildNode($this->sequenceInlineNodes($sequence));
                    break;

                // Note: although "case $openingQuoteByte:" could have been used here, it would not compile the switch
                // as a hash map and thus would not perform as well overall - when called frequently as it will be.
                case Splitter::BYTE_QUOTE_SINGLE:
                case Splitter::BYTE_QUOTE_DOUBLE:
                    $captured = $this->sequencer->pack($position);
                    if ($captured !== '' ) {
                        $childNode = new TextNode($captured);
                        $this->callInterceptor($childNode, InterceptorInterface::INTERCEPT_TEXT, $this->state);
                        $node->addChildNode($childNode);
                    }
                    #$sequence->next();
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