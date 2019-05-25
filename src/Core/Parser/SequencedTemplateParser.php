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

        $state = $this->getParsingState();
        $state->contexts = new Contexts();
        $state->source = new Source($templateString);
        $state->splitter = new Splitter($state->source, $state->contexts);

        $sequence = $state->splitter->parse();
        $iterator = new \NoRewindIterator($sequence);
        $node = $this->sequenceRootNodesAsChildrenOfTopStack($iterator, $state);

        if (!$node instanceof RootNode) {
            $child = $node;
            $node = new RootNode();
            $node->addChildNode($child);
        }
        $state->setRootNode($node);
        $this->parsedTemplates[$templateIdentifier] = $state;
        return $state;
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
        $lines = $state->splitter->countCharactersMatchingMask(Splitter::MASK_LINEBREAKS, 1, $position->index) + 1;
        $offset = $state->splitter->findBytePositionBeforeOffset(Splitter::MASK_LINEBREAKS, $position->index);
        $line = substr(
            $state->source->source,
            $offset,
            $state->splitter->findBytePositionAfterOffset(Splitter::MASK_LINEBREAKS, $position->index)
        );
        $character = $position->index - $offset - 1;
        $string = 'Line ' . $lines . ' character ' . $character . PHP_EOL;
        $string .= PHP_EOL;
        $string .= str_repeat(' ', max($character, 0)) . 'v' . PHP_EOL;
        $string .= trim($line) . PHP_EOL;
        $string .= str_repeat(' ', max($character, 0)) . '^' . PHP_EOL;
        return $string;
    }

    protected function throwErrorAtPosition(string $message, int $code)
    {
        $position = new Position($state->splitter->context, $state->splitter->index);
        $ascii = (string) $state->source->bytes[$state->splitter->index];
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
    protected function sequenceRootNodesAsChildrenOfTopStack(\Iterator $sequence, ParsingState $state): NodeInterface
    {
        // Please note: repeated calls to $this->getTopmostNodeFromStack() are indeed intentional. That method may
        // return different nodes at different times depending on what has occurreded in other methods! Only the places
        // where $node is actually extracted is it (by design) safe to do so. DO NOT REFACTOR!
        foreach ($sequence as $symbol => $captured) {
            switch ($symbol) {
                case Splitter::BYTE_INLINE:
                    $node = $state->getNodeFromStack();
                    if ($captured !== null) {
                        $node->addChildNode(new TextNode($captured));
                    }
                    $node->addChildNode($this->sequenceInlineNodes($sequence, $state, false));
                    $state->splitter->switch($state->contexts->root);
                    break;

                case Splitter::BYTE_TAG:
                    if ($captured !== null) {
                        $state->getNodeFromStack()->addChildNode(new TextNode($captured));
                    }

                    $childNode = $this->sequenceTagNode($sequence, $state);
                    $state->splitter->switch($state->contexts->root);
                    if ($childNode) {
                        $state->getNodeFromStack()->addChildNode($childNode);
                    }
                    break;

                case Splitter::BYTE_NULL:
                    if ($captured !== null) {
                        $state->getNodeFromStack()->addChildNode(new TextNode($captured));
                    }
                    break;

                default:
                    $this->throwErrorAtPosition(
                        'Unexpected token in root node iteration: ' . addslashes(chr($symbol)) . ' at index ' . $state->splitter->index . ' in context ' . $state->splitter->context->getContextName(),
                        1557700785
                    );
                    break;
            }
        }

        return $state->popNodeFromStack();
    }

    /**
     * @param \Iterator|?string[] $sequence
     * @param ParsingState $state
     * @return NodeInterface|null
     */
    protected function sequenceTagNode(\Iterator $sequence, ParsingState $state): ?NodeInterface
    {
        $closeBytePosition = 0;
        $arguments = [];
        $definitions = null;
        $text = '<';
        $namespace = null;
        $method = null;
        $bytes = &$state->source->bytes;
        $source = &$state->source->source;
        $node = new RootNode();
        $selfClosing = false;
        $closing = false;
        $escapingEnabledBackup = $this->escapingEnabled;

        $interceptionPoint = InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER;

        $state->splitter->switch($state->contexts->tag);
        $sequence->next();
        foreach ($sequence as $symbol => $captured) {
            $text .= $captured;
            switch ($symbol) {
                case Splitter::BYTE_INLINE:
                    $contextBefore = $state->splitter->context;
                    $collected = $this->sequenceInlineNodes($sequence, $state);
                    $state->splitter->switch($contextBefore);
                    if ($state->splitter->context->context === Context::CONTEXT_ATTRIBUTES) {
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
                        $key = $this->sequenceQuotedNode($sequence, $state)->flatten();
                        if ($definitions !== null && !isset($definitions[$key])) {
                            $this->throwUnsupportedArgumentError($key, $definitions);
                        }
                    } else {
                        $arguments[$key] = $this->sequenceQuotedNode($sequence, $state)->flatten();
                        unset($key);
                    }
                    break;

                case Splitter::BYTE_TAG_CLOSE:
                    $method = $method ?? $captured;
                    $text .= '/';
                    $closing = true;
                    $selfClosing = $bytes[$state->splitter->index - 1] !== Splitter::BYTE_TAG;
                    $interceptionPoint = InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER;
                    break;

                case Splitter::BYTE_SEPARATOR_COLON:
                    $text .= ':';
                    $namespace = $namespace ?? $captured;
                    break;

                case Splitter::BYTE_TAG_END:
                    $text .= '>';
                    $method = $method ?? $captured;

                    if (!isset($namespace) || !isset($method) || $state->splitter->context->context === Context::CONTEXT_DEAD || $this->renderingContext->getViewHelperResolver()->isNamespaceIgnored($namespace)) {
                        return $node->addChildNode(new TextNode($text))->flatten(false);
                    }

                    if ($closing && !$selfClosing) {
                        // Closing byte was more than two bytes back, meaning the tag is NOT self-closing, but is a
                        // closing tag for a previously opened+stacked node. Finalize the node now.
                        $closesNode = $state->popNodeFromStack();
                        $expectedClass = $this->renderingContext->getViewHelperResolver()->resolveViewHelperClassName($namespace, $method);
                        if ($closesNode instanceof $expectedClass) {
                            $arguments = $closesNode->getParsedArguments();
                            $viewHelperNode = $closesNode;
                        } else {
                            $this->throwErrorAtPosition(
                                'Mismatched closing tag. Expecting: ' . $namespace . ':' . $method . '. Found: ' . $closesNode->getNamespace() . ':' . $closesNode->getIdentifier(),
                                1557700789
                            );
                        }
                    }

                    if ($state->splitter->context->context === Context::CONTEXT_ATTRIBUTES && $captured !== null) {
                        // We are still capturing arguments and the last yield contained a value. Null-coalesce key
                        // with captured string so object accessor becomes key name (ECMA shorthand literal)
                        $arguments[$key ?? $captured] = $this->createObjectAccessorNodeOrRawValue($captured);
                    }

                    $this->escapingEnabled = $escapingEnabledBackup;

                    try {
                        $viewHelperNode = $viewHelperNode ?? $this->renderingContext->getViewHelperResolver()->createViewHelperInstance($namespace, $method);
                    } catch (\TYPO3Fluid\Fluid\Core\ViewHelper\Exception $exception) {
                        $this->throwErrorAtPosition($exception->getMessage(), $exception->getCode());
                    }

                    if (!$closing) {
                        $this->callInterceptor($viewHelperNode, $interceptionPoint, $state);
                        $viewHelperNode->setParsedArguments($arguments);
                        $state->pushNodeToStack($viewHelperNode);
                        return null;
                    }

                    $viewHelperNode->postParse($arguments, $state);

                    return $viewHelperNode;

                case Splitter::BYTE_WHITESPACE_TAB:
                case Splitter::BYTE_WHITESPACE_RETURN:
                case Splitter::BYTE_WHITESPACE_EOL:
                case Splitter::BYTE_WHITESPACE_SPACE:
                    if ($state->splitter->context->context === Context::CONTEXT_ATTRIBUTES) {
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
                            $viewHelperNode = $this->renderingContext->getViewHelperResolver()->createViewHelperInstance($namespace, $method);
                            $definitions = $viewHelperNode->prepareArguments();

                            // A whitespace character, in tag context, means the beginning of an array sequence (which may
                            // or may not contain any items; the next symbol may be a tag end or tag close). We sequence the
                            // arguments array and create a ViewHelper node.
                            $state->splitter->switch($state->contexts->attributes);
                            break;
                        }

                        // A whitespace before a colon means the tag is not a namespaced tag. We will ignore everything
                        // inside this tag, except for inline syntax, until the tag ends. For this we use a special,
                        // limited variant of the root context where instead of scanning for "<" we scan for ">".
                        // We continue in this same loop because it still matches the potential symbols being yielded.
                        // Most importantly: this new reduced context will NOT match a colon which is the trigger symbol
                        // for a ViewHelper tag.
                        //unset($namespace, $method);
                        $state->splitter->switch($state->contexts->dead);
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
     * @param ParsingState $state
     * @return NodeInterface
     */
    protected function sequenceInlineNodes(\Iterator $sequence, ParsingState $state, bool $allowArray = true): NodeInterface
    {
        $text = '{';

        $startingPosition = $state->splitter->index;
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

        $state->splitter->switch($state->contexts->inline);
        $sequence->next();
        foreach ($sequence as $symbol => $captured) {
            $text .= $captured;
            switch ($symbol) {
                case Splitter::BYTE_BACKSLASH:
                    // Add the next character to the expression and advance the Position index by 1 to skip the next.
                    ++$state->splitter->index;
                    break;

                case Splitter::BYTE_ARRAY_START:
                    $text .= '[';

                    ArrayStart:
                    if (!$allowArray) {
                        break;
                    }
                    $isArray = $allowArray;

                    // Sequence the node. Pass the "use numeric keys?" boolean based on the current byte. Only array
                    // start creates numeric keys. Inline start with keyless values creates ECMA style {foo:foo, bar:bar}
                    // from {foo, bar}.
                    $array[$key ?? $captured ?? 0] = $node = $this->sequenceArrayNode($sequence, $state, null, $symbol === Splitter::BYTE_ARRAY_START);
                    $state->splitter->switch($state->contexts->inline);
                    unset($key);
                    break;

                case Splitter::BYTE_INLINE:
                    // Encountering this case can mean different things: sub-syntax like {foo.{index}} or array, depending
                    // on presence of either a colon or comma before the inline. In protected mode it is simply added.
                    $text .= '{';
                    if ($state->splitter->context->context === Context::CONTEXT_PROTECTED) {
                        ++$ignoredEndingBraces;
                    } elseif ($allowArray && ($hasColon || $isArray)) {
                        $isArray = true;
                        $captured = $key ?? $captured ?? $potentialAccessor;
                        // This is a sub-syntax following a colon - meaning it is an array.
                        if ($captured !== null) {
                            goto ArrayStart;
                        }
                    } elseif ($state->splitter->index > ($startingPosition + 1)) {
                        // Ignore one ending additional curly brace. Subtracted in the BYTE_INLINE_END case below.
                        // The expression in this case looks like {{inline}.....} and we capture the curlies.
                        ++$ignoredEndingBraces;
                    } else {
                        goto ArrayStart;
                    }
                    break;

                case Splitter::BYTE_MINUS:
                    break;

                // Backtick may be encountered in two different contexts: normal inline context, in which case it has
                // the same meaning as any quote and causes sequencing of a quoted string. Or protected context, in
                // which case it also sequences a quoted node but appends the result instead of assigning to array.
                case Splitter::BYTE_BACKTICK:
                    if ($state->splitter->context->context === Context::CONTEXT_PROTECTED) {
                        #$text = substr($text, 0, -1) . $state->source->source[$state->splitter->index];
                        $node->addChildNode(new TextNode($text));
                        $node->addChildNode($this->sequenceQuotedNode($sequence, $state)->flatten(false));
                        $text = '';
                        break;
                    }
                    // Fallthrough is intentional: if not in protected context, consider the backtick a normal quote.

                // Case not normally countered in straight up "inline" context, but when encountered, means we have
                // explicitly found a quoted array key - and we extract it.
                case Splitter::BYTE_QUOTE_SINGLE:
                case Splitter::BYTE_QUOTE_DOUBLE:
                    isset($key) ? ($array[$key] = $this->sequenceQuotedNode($sequence, $state)->flatten()) && $key = null : $key = $this->sequenceQuotedNode($sequence, $state)->flatten();
                    $isArray = $allowArray;
                    break;

                case Splitter::BYTE_SEPARATOR_COMMA:
                    $text .= ',';
                    !isset($captured) ?: ($array[$key ?? $captured] = $this->createObjectAccessorNodeOrRawValue($captured)) && $key = null;
                    $isArray = $allowArray;
                    break;

                case Splitter::BYTE_SEPARATOR_EQUALS:
                    $text .= '=';
                    $isArray = $allowArray;
                    break;

                case Splitter::BYTE_SEPARATOR_COLON:
                    $text .= ':';
                    $hasColon = true;
                    $namespace = $key = $captured;
                    break;

                case Splitter::BYTE_WHITESPACE_SPACE:
                case Splitter::BYTE_WHITESPACE_EOL:
                case Splitter::BYTE_WHITESPACE_RETURN:
                case Splitter::BYTE_WHITESPACE_TAB:
                    // If we already collected some whitespace we must enter protected context.
                    $text .= $state->source->source[$state->splitter->index - 1];
                    if ($hasWhitespace && !$hasPass && !$allowArray) {
                        // Protection mode: this very limited context does not allow tags or inline syntax, and will
                        // protect things like CSS and JS - and will only enter a more reactive context if encountering
                        // the backtick character, meaning a quoted string will be sequenced. This backtick-quoted
                        // string can then contain inline syntax like variable accessors.
                        $node = new RootNode();
                        $state->splitter->switch($state->contexts->protected);
                        break;
                    }
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
                    $potentialAccessor = $potentialAccessor ?? $captured;
                    $text .=  $state->source->source[$state->splitter->index - 1];
                    !isset($potentialAccessor) ?: ($node = ($node ?? $this->createObjectAccessorNodeOrRawValue($potentialAccessor)));
                    unset($namespace, $method, $potentialAccessor, $key, $callDetected);
                    break;

                case Splitter::BYTE_PARENTHESIS_START:
                    $isArray = false;
                    // Special case: if a parenthesis start was preceded by whitespace but had no pass operator we are
                    // not dealing with a ViewHelper call and will continue the sequencing, grabbing the parenthesis as
                    // part of the expression.
                    $text .= '(';
                    if (!$hasColon || ($hasWhitespace && !$hasPass)) {
                        unset($namespace, $method);
                        break;
                    }

                    $callDetected = true;
                    $method = $captured;
                    $childNodeToAdd = $node;
                    try {
                        $node = $this->renderingContext->getViewHelperResolver()->createViewHelperInstance($namespace, $method);
                        $definitions = $node->prepareArguments();
                    } catch (\TYPO3Fluid\Fluid\Core\ViewHelper\Exception $exception) {
                        $this->throwErrorAtPosition($exception->getMessage(), $exception->getCode());
                    }
                    $state->splitter->switch($state->contexts->array);
                    $arguments = $this->sequenceArrayNode($sequence, $state, $definitions)->getInternalArray();
                    $state->splitter->switch($state->contexts->inline);
                    if ($childNodeToAdd) {
                        $escapingEnabledBackup = $this->escapingEnabled;
                        $this->escapingEnabled = (bool)$node->isChildrenEscapingEnabled();
                        if ($childNodeToAdd instanceof ObjectAccessorNode) {
                            $this->callInterceptor($childNodeToAdd, InterceptorInterface::INTERCEPT_OBJECTACCESSOR, $state);
                        } elseif ($childNodeToAdd instanceof ExpressionNodeInterface) {
                            $this->callInterceptor($childNodeToAdd, InterceptorInterface::INTERCEPT_EXPRESSION, $state);
                        }
                        $this->escapingEnabled = $escapingEnabledBackup;
                        $node->addChildNode($childNodeToAdd);
                    }
                    $text .= ')';
                    unset($potentialAccessor);
                    break;

                case Splitter::BYTE_INLINE_END:
                    if (--$ignoredEndingBraces >= 0) {
                        $text .= '}';
                        break;
                    }
                    $isArray = $allowArray && $isArray ?: ($hasColon && !$hasPass && !$callDetected);

                    // Decision: if we did not detect a ViewHelper we match the *entire* expression, from the cached
                    // starting index, to see if it matches a known type of expression. If it does, we must return the
                    // appropriate type of ExpressionNode.
                    if ($isArray) {
                        if ($captured !== null) {
                            $array[$key ?? $captured] = $this->createObjectAccessorNodeOrRawValue($captured);
                        }
                        return new ArrayNode($array);
                    } elseif ($state->splitter->context->context === Context::CONTEXT_PROTECTED) {
                        $node->addChildNode(new TextNode($text . '}'));
                        $interceptionPoint = InterceptorInterface::INTERCEPT_TEXT;
                    } elseif ($hasWhitespace && !$callDetected) {
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
                                $expressionNode = new $expressionNodeTypeClassName($matchedVariableSet[0], $matchedVariableSet, $state);
                                try {
                                    $interceptionPoint = InterceptorInterface::INTERCEPT_EXPRESSION;
                                    $node = $expressionNode;
                                } catch (ExpressionException $error) {
                                    $node = new TextNode($this->renderingContext->getErrorHandler()->handleExpressionError($error));
                                }
                                break;
                            }
                        }
                    } elseif ($callDetected) {
                        // The second-priority check is for a ViewHelper used right before the inline expression ends,
                        // in which case there is no further syntax to come.
                        $node->postParse($arguments, $state);
                        $interceptionPoint = InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER;
                    } elseif (!$hasPass && !$callDetected) {
                        // Third priority check is if there was no pass syntax and no ViewHelper, in which case we
                        // create a standard ObjectAccessorNode; alternatively, if nothing was captured (expression
                        // was empty, e.g. {} was used) we create a TextNode with the captured text to output "{}".
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
                        // Fourth priority check is for a pass to a ViewHelper alias, e.g. "{value | raw}" in which case
                        // we look for the alias used and create a ViewHelperNode with no arguments.
                        $childNodeToAdd = $node;
                        $node = $this->renderingContext->getViewHelperResolver()->createViewHelperInstance(null, $captured);
                        $node->addChildNode($childNodeToAdd);
                        $node->postParse($arguments, $state);
                        $interceptionPoint = InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER;
                    } else {
                        # TODO: should this be an error case?
                        $this->throwErrorAtPosition('Invalid inline syntax - not accessor, not expression, not array, not ViewHelper', 1558782228);
                    }

                    $escapingEnabledBackup = $this->escapingEnabled;
                    $this->escapingEnabled = (bool)((isset($viewHelper) && $node->isOutputEscapingEnabled()) || $escapingEnabledBackup);
                    $this->callInterceptor($node, $interceptionPoint, $state);
                    $this->escapingEnabled = $escapingEnabledBackup;
                    return $node;

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
     * @param ParsingState $state
     * @param ArgumentDefinition[] $definitions
     * @param bool $numeric
     * @return ArrayNode
     */
    protected function sequenceArrayNode(\Iterator $sequence, ParsingState $state, array $definitions = null, bool $numeric = false): ArrayNode
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
                    $array[$key] = $this->sequenceArrayNode($sequence, $state, null, $symbol === Splitter::BYTE_ARRAY_START);
                    unset($key);
                    ++$itemCount;
                    break;

                case Splitter::BYTE_QUOTE_SINGLE:
                case Splitter::BYTE_QUOTE_DOUBLE:
                    if (!isset($key)) {
                        $key = $this->sequenceQuotedNode($sequence, $state)->flatten();
                        if ($key !== null && $definitions !== null && !isset($definitions[$key])) {
                            $this->throwUnsupportedArgumentError($key, $definitions);
                        }
                    } else {
                        $array[$key] = $this->sequenceQuotedNode($sequence, $state)->flatten();
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
     * @param ParsingState $state
     * @return RootNode
     */
    protected function sequenceQuotedNode(\Iterator $sequence, ParsingState $state): RootNode
    {
        $startingByte = $state->source->bytes[$state->splitter->index];
        $contextToRestore = $state->splitter->switch($state->contexts->quoted);
        $node = new RootNode();
        $sequence->next();
        foreach ($sequence as $symbol => $captured) {
            switch ($symbol) {

                case Splitter::BYTE_ARRAY_START:
                    if ($captured === null) {
                        $state->splitter->switch($state->contexts->array);
                        $node->addChildNode($this->sequenceArrayNode($sequence, $state));
                        $state->splitter->switch($state->contexts->quoted);
                    } else {
                        $node->addChildNode(new TextNode($captured . '['));
                    }
                    break;

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
                        $this->callInterceptor($childNode, InterceptorInterface::INTERCEPT_TEXT, $state);
                        $node->addChildNode($childNode);
                    }

                    $node->addChildNode($this->sequenceInlineNodes($sequence, $state));
                    $state->splitter->switch($state->contexts->quoted);
                    break;

                // Note: although "case $startingByte:" could have been used here, it would not compile the switch
                // as a hash map and thus would not perform as well overall - when called frequently as it will be.
                // Backtick will only be encountered if the context is "protected" (insensitive inline sequencing)
                case Splitter::BYTE_QUOTE_SINGLE:
                case Splitter::BYTE_QUOTE_DOUBLE:
                case Splitter::BYTE_BACKTICK:
                    if ($symbol !== $startingByte) {
                        break;
                    }
                    if ($captured !== null) {
                        $childNode = new TextNode(trim($captured, '\\'));
                        $this->callInterceptor($childNode, InterceptorInterface::INTERCEPT_TEXT, $state);
                        $node->addChildNode($childNode);
                    }
                    $state->splitter->switch($contextToRestore);
                    return $node;

                default:
                    $this->throwErrorAtPosition('Unexpected token in quoted context', 1557700792);
                    break;
            }
        }
        $this->throwErrorAtPosition('Unterminated quoted expression', 1557700793);
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