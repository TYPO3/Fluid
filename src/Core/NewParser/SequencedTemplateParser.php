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

    /** @var ParsingState */
    private $state;

    /** @var Splitter */
    private $splitter;

    /** @var Contexts */
    private $contexts;

    /** @var Position */
    private $position;

    public function parse($templateString, $templateIdentifier = null): ParsingState
    {
        $templateString = $this->preProcessTemplateSource($templateString);

        $this->source = new Source($templateString);
        $this->contexts = new Contexts();
        $this->position = new Position($this->contexts->root);
        $this->splitter = new Splitter($this->position, $this->source);
        $this->splitter->debugger = $this->debugger;

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

    protected function throwErrorAtPosition(string $message, int $code, Position $position)
    {
        $character = (string) addslashes($this->source->source{$position->index});
        $ascii = (string) $this->source->bytes[$position->index];
        $message .=  ' ASCII: ' . $ascii . ': ' . $this->extractSourceDumpOfLineAtPosition($position);
        $error = new SequencingException($message, $code);
        $error->setPosition($position);
        throw $error;
    }

    /**
     * @param \Iterator|Position[] $sequence
     * @return NodeInterface|null
     */
    protected function sequenceRootNodesAsChildrenOfTopStack(\Iterator $sequence): NodeInterface
    {
        // Please note: repeated calls to $this->getTopmostNodeFromStack() are indeed intentional. That method may
        // return different nodes at different times depending on what has occurreded in other methods! Only the places
        // where $node is actually extracted is it (by design) safe to do so. DO NOT REFACTOR!
        foreach ($sequence as $symbol => $position) {
            switch ($symbol) {
                case Splitter::BYTE_INLINE:
                    $node = $this->state->getNodeFromStack();
                    if ($position->captured !== null) {
                        $node->addChildNode(new TextNode($position->captured));
                    }
                    $node->addChildNode($this->sequenceInlineNodes($sequence));
                    break;

                case Splitter::BYTE_TAG:
                    if ($position->captured !== null) {
                        $this->state->getNodeFromStack()->addChildNode(new TextNode($position->captured));
                    }

                    $childNode = $this->sequenceTagNode($sequence);
                    if ($childNode) {
                        $this->state->getNodeFromStack()->addChildNode($childNode);
                    }
                    break;

                case Splitter::BYTE_NULL:
                    $content = $position->captured;
                    if ($position->captured !== null) {
                        $this->state->getNodeFromStack()->addChildNode(new TextNode($position->captured));
                    }
                    break;

                default:
                    $this->throwErrorAtPosition(
                        'Unexpected token in root node iteration: ' . addslashes(chr($symbol)) . ' at index ' . $position->index . ' in context ' . $position->getContextName(),
                        1557700785,
                        $position
                    );
                    break;
            }
        }

        return $this->state->popNodeFromStack();
    }

    /**
     * @param \Iterator|Position[] $sequence
     * @return NodeInterface|null
     */
    protected function sequenceTagNode(\Iterator $sequence): ?NodeInterface
    {
        $closeBytePosition = 0;
        $arguments = [];
        $text = '<';
        $namespace = null;
        $method = null;
        $bytes = &$this->source->bytes;
        $source = &$this->source->source;
        $node = new RootNode();

        $contextToRestore = $this->switch($this->contexts->tag);
        $sequence->next();
        foreach ($sequence as $symbol => $position) {
            $text .= $position->captured . chr($symbol);
            switch ($symbol) {
                case Splitter::BYTE_INLINE:
                    $text = substr($text, 0, -1); // Remove the captured inline start.
                    $node->addChildNode(new TextNode($text));
                    $node->addChildNode($this->sequenceInlineNodes($sequence));
                    $text = '';
                    unset($namespace, $method);
                    break;

                case Splitter::BYTE_QUOTE_DOUBLE:
                case Splitter::BYTE_QUOTE_SINGLE:
                case Splitter::BYTE_SEPARATOR_EQUALS:
                    break;

                case Splitter::BYTE_TAG_CLOSE:
                    $closeBytePosition = $position->index;
                    break;

                case Splitter::BYTE_SEPARATOR_COLON:
                    if (!isset($namespace)) {
                        $namespace = $position->captured;
                    }
                    break;

                case Splitter::BYTE_TAG_END:
                    $this->switch($contextToRestore);

                    if (!isset($namespace) || $this->renderingContext->getViewHelperResolver()->isNamespaceIgnored($namespace)) {
                        $node->addChildNode(new TextNode($text));
                        return $node;
                    }

                    $method = $method ?? $position->captured;
                    if ($closeBytePosition > 0 && $bytes[$closeBytePosition - 1] === Splitter::BYTE_TAG) {
                        $closesNode = $this->state->popNodeFromStack();
                        if ($closesNode instanceof ViewHelperNode && $closesNode->getNamespace() === $namespace && $closesNode->getIdentifier() === $method) {
                            $viewHelper = $closesNode->getUninitializedViewHelper();
                            $viewHelper::postParseEvent($closesNode, $closesNode->getArguments(), $this->state->getVariableContainer());
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

                    if ($bytes[$this->position->index - 1] === Splitter::BYTE_TAG_CLOSE) {
                        // The symbol that caused sequencing of the arguments array to end, was a tag close. Set the
                        // position of the tag closing character to the current byte position. The originally stored
                        // close-byte position will be zero because the closing character came *after* the namespace
                        // and method (e.g. is a self-closing tag).
                        // We must also advance the iterator because the next character will be a tag end which is not
                        // necessary to include.
                        $interceptionPoint = InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER;
                        $return = true;
                    } else {
                        $interceptionPoint = InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER;
                        $return = false;
                    }

                    $viewHelperNode = new ViewHelperNode($this->renderingContext, $namespace, $method, $arguments, $this->state);
                    ($viewHelperNode->getUninitializedViewHelper())::postParseEvent($viewHelperNode, $arguments, $this->state->getVariableContainer());
                    $this->callInterceptor($viewHelperNode, $interceptionPoint, $this->state);

                    if (!$return) {
                        $this->state->pushNodeToStack($viewHelperNode);
                        return null;
                    }

                    return $viewHelperNode;

                case Splitter::BYTE_WHITESPACE_TAB:
                case Splitter::BYTE_WHITESPACE_RETURN:
                case Splitter::BYTE_WHITESPACE_EOL:
                case Splitter::BYTE_WHITESPACE_SPACE:
                    if (isset($namespace)) {
                        $method = $method ?? $position->captured;

                        // A whitespace character, in tag context, means the beginning of an array sequence (which may
                        // or may not contain any items; the next symbol may be a tag end or tag close). We sequence the
                        // arguments array and create a ViewHelper node.
                        $arguments = $this->sequenceTagAttributes($sequence)->getInternalArray();
                    } else {
                        // A whitespace before a colon means the tag is not a namespaced tag. We will ignore everything
                        // inside this tag, except for inline syntax, until the tag ends. For this we use a special,
                        // limited variant of the root context where instead of scanning for "<" we scan for ">".
                        // We continue in this same loop because it still matches the potential symbols being yielded.
                        // Most importantly: this new reduced context will NOT match a colon which is the trigger symbol
                        // for a ViewHelper tag.
                        unset($namespace, $method);
                    }
                    $this->switch($this->contexts->dead);
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

        $this->switch($contextToRestore);
        return new TextNode($text);
    }

    /**
     * @param \Iterator|Position[] $sequence
     * @return ArrayNode
     */
    protected function sequenceTagAttributes(\Iterator $sequence): ArrayNode
    {
        $array = [];

        #$this->position->switch($this->contexts->attributes);
        $contextToRestore = $this->switch($this->contexts->attributes);
        $sequence->next();
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
                        $key = count($array) - 1;
                    }
                    $array[$key] = $this->sequenceInlineNodes($sequence);
                    break;

                //case Splitter::BYTE_SEPARATOR_COLON:
                case Splitter::BYTE_SEPARATOR_EQUALS:
                    $key = $position->captured;
                    break;

                case Splitter::BYTE_QUOTE_SINGLE:
                case Splitter::BYTE_QUOTE_DOUBLE:
                    if (!isset($key)) {
                        $key = $this->sequenceQuotedNode($sequence)->flatten(true);
                    } else {
                        $array[$key] = $this->sequenceQuotedNode($sequence)->flatten();
                        unset($key);
                    }
                    break;

                case Splitter::BYTE_WHITESPACE_TAB:
                case Splitter::BYTE_WHITESPACE_RETURN:
                case Splitter::BYTE_WHITESPACE_EOL:
                case Splitter::BYTE_WHITESPACE_SPACE:
                    $captured = $key ?? $position->captured;
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

                case Splitter::BYTE_TAG_END:
                    // Rewind exactly one character so the method that called this method will see the tag close.
                    --$this->position->index;
                case Splitter::BYTE_TAG_CLOSE:
                    if (isset($key)) {
                        // We now have enough to assign the array value and clear our key and value store variables.
                        $captured = $position->captured;
                        if ($captured !== null) {
                            $array[$key] = $this->createObjectAccessorNodeOrRawValue($captured, $position);
                        }
                    }
                    $this->switch($contextToRestore);
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
    protected function sequenceInlineNodes(\Iterator $sequence, bool $allowArray = true): NodeInterface
    {
        $startingIndex = $this->position->index;

        $node = null;
        $key = null;
        $namespace = null;
        $method = null;
        $callDetected = false;
        $hasPass = false;
        $isArray = false;

        $array = [];

        $contextToRestore = $this->switch($this->contexts->inline);
        $sequence->next();
        foreach ($sequence as $symbol => $position) {
            switch ($symbol) {
                case Splitter::BYTE_MINUS:
                    break;

                // Case not normally countered in straight up "inline" context, but when encountered, means we have
                // explicitly found a quoted array key - and we extract it.
                case Splitter::BYTE_QUOTE_SINGLE:
                case Splitter::BYTE_QUOTE_DOUBLE:
                    if (!isset($key)) {
                        $key = $this->sequenceQuotedNode($sequence)->flatten(true);
                    } else {
                        $array[$key] = $this->sequenceQuotedNode($sequence)->flatten(true);
                    }
                    $isArray = true;
                    break;

                case Splitter::BYTE_SEPARATOR_COMMA:
                    $isArray = true;
                    if ($position->captured !== null) {
                        if (!isset($key)) {
                            $key = $captured;
                        }
                        $array[$key] = $this->createObjectAccessorNodeOrRawValue($position->captured, $position);
                        unset($key);
                    }
                    break;

                case Splitter::BYTE_SEPARATOR_EQUALS:
                    $isArray = true;
                case Splitter::BYTE_SEPARATOR_COLON:
                    $captured = $position->captured;
                    $namespace = $key = $captured;
                    break;

                case Splitter::BYTE_INLINE_END:
                    // Decision: if we did not detect a ViewHelper we match the *entire* expression, from the cached
                    // starting index, to see if it matches a known type of expression. If it does, we must return the
                    // appropriate type of ExpressionNode.
                    if ($isArray) {
                        if ($position->captured !== null) {
                            if (!isset($key)) {
                                $key = $captured;
                            }
                            $array[$key] = $this->createObjectAccessorNodeOrRawValue($position->captured, $position);
                        }
                        $this->switch($contextToRestore);
                        return new ArrayNode($array);
                    } elseif (!$callDetected) {
                        $entirePosition = $position->pad($position->index - ($startingIndex + 1), 1);
                        $section = $this->pack($entirePosition);
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
                        $node = $this->createObjectAccessorNodeOrRawValue($position->captured, $position);
                        $this->callInterceptor($node, InterceptorInterface::INTERCEPT_OBJECTACCESSOR, $this->state);
                    } else {
                        $potentialAccessor = $potentialAccessor ?? $position->captured;
                        if ($potentialAccessor !== null) {
                            if ($node !== null) {
                                $this->throwErrorAtPosition(
                                    'Attempt to pipe a value into an object accessor - you may only pipe to ViewHelpers',
                                    1557740018,
                                    $position
                                );
                            }
                            $node = $this->createObjectAccessorNodeOrRawValue($potentialAccessor);
                            $this->callInterceptor($node, InterceptorInterface::INTERCEPT_OBJECTACCESSOR, $this->state);
                        } elseif ($node instanceof ViewHelperNode) {
                            $viewHelper = $node->getUninitializedViewHelper();
                            $viewHelper::postParseEvent($node, $arguments, $this->state->getVariableContainer());

                            $escapingEnabledBackup = $this->escapingEnabled;
                            $this->escapingEnabled = (bool)$viewHelper->isOutputEscapingEnabled();
                            $this->callInterceptor($node, InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER, $this->state);
                            $this->escapingEnabled = $escapingEnabledBackup;
                        } else {
                            #echo $this->source->source;
                            #var_dump($captured);
                            var_dump($node);
                            exit();
                        }
                    }

                    #$this->position->leave();
                    $this->switch($contextToRestore);
                    return $node;

                case Splitter::BYTE_TAG_END:
                case Splitter::BYTE_PIPE:
                    // If there is an accessor on the left side of the pipe we make the $node into an ObjectAccessorNode,
                    // the next iteration may then call a ViewHelper (OK) or attempt to pass to another object access (FAIL)
                    $hasPass = true;
                    $potentialAccessor = $potentialAccessor ?? $position->captured;
                    if (!empty($potentialAccessor)) {
                        $node = $this->createObjectAccessorNodeOrRawValue($potentialAccessor, $position);
                    }
                    unset($namespace, $method, $potentialAccessor, $key);
                    break;

                case Splitter::BYTE_PARENTHESIS_START:
                    $callDetected = true;
                    $method = $position->captured;
                    $isArray = false;

                    $childNodeToAdd = $node;
                    $arguments = $this->sequenceArrayNode($sequence)->getInternalArray();
                    $node = new ViewHelperNode($this->renderingContext, $namespace, $method, $arguments, $this->state);
                    $viewHelper = $node->getUninitializedViewHelper();
                    $viewHelper::postParseEvent($node, $arguments, $this->state->getVariableContainer());
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
                    unset($potentialAccessor);
                    break;

                case Splitter::BYTE_WHITESPACE_SPACE:
                case Splitter::BYTE_WHITESPACE_EOL:
                case Splitter::BYTE_WHITESPACE_RETURN:
                case Splitter::BYTE_WHITESPACE_TAB:
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

        $contextToRestore = $this->switch($this->contexts->array);
        $sequence->next();
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
                    $array[$key] = $this->sequenceArrayNode($sequence);
                    break;

                case Splitter::BYTE_SEPARATOR_COLON:
                case Splitter::BYTE_SEPARATOR_EQUALS:
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
                        if ($position->captured !== null) {
                            $array[$key] = $this->createObjectAccessorNodeOrRawValue($position->captured, $position);
                        }
                    }
                    $this->switch($contextToRestore);
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
        $node = new ObjectAccessorNode(trim($accessor));
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
        $startingByte = $this->source->bytes[$this->position->index];
        $contextToRestore = $this->switch($this->contexts->quoted);
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
                    $captured = $position->captured;
                    if ($captured !== null) {
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
                    if ($symbol !== $startingByte) {
                        break;
                    }
                    $captured = $position->captured;
                    if ($captured !== null) {
                        $childNode = new TextNode(trim($captured, '\\'));
                        $this->callInterceptor($childNode, InterceptorInterface::INTERCEPT_TEXT, $this->state);
                        $node->addChildNode($childNode);
                    }
                    $this->switch($contextToRestore);
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

    private function switch(Context $context): Context
    {
        $previous = $this->position->context;
        $this->position->context = $context;
        return $previous;
    }
}