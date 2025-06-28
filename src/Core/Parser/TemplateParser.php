<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Parser;

use TYPO3Fluid\Fluid\Core\Compiler\StopCompilingException;
use TYPO3Fluid\Fluid\Core\Compiler\UncompilableTemplateInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ExpressionException;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ExpressionNodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NumericNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\InheritedNamespaceException;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperNodeInitializedEventInterface;

/**
 * Template parser building up an object syntax tree.
 *
 * @internal Nobody should need to override this class. There
 *           are various different ways to extend Fluid, the main
 *           syntax tree should not be tampered with.
 * @todo: Declare final with next major.
 * @todo: fix underlying types and activate strict types in this file
 * @todo: Remove state from this class and introduce separate context from RenderingContext
 *        (maybe existing Configuration class can be used for that?)
 */
class TemplateParser
{
    /**
     * The following two constants are used for tracking whether we are currently
     * parsing ViewHelper arguments or not. This is used to parse arrays only as
     * ViewHelper argument.
     */
    public const CONTEXT_INSIDE_VIEWHELPER_ARGUMENTS = 1;
    public const CONTEXT_OUTSIDE_VIEWHELPER_ARGUMENTS = 2;

    /**
     * Whether or not the escaping interceptors are active
     */
    protected bool $escapingEnabled = true;

    protected ?Configuration $configuration = null;

    protected RenderingContextInterface $renderingContext;

    protected int $pointerLineNumber = 1;

    protected int $pointerLineCharacter = 1;

    protected ?string $pointerTemplateCode = null;

    /**
     * @var ParsedTemplateInterface[]
     */
    protected array $parsedTemplates = [];

    public function setRenderingContext(RenderingContextInterface $renderingContext): void
    {
        $this->renderingContext = $renderingContext;
        $this->configuration = $renderingContext->buildParserConfiguration();
    }

    /**
     * Returns an array of current line number, character in line and reference template code;
     * for extraction when catching parser-related Exceptions during parsing.
     */
    public function getCurrentParsingPointers(): array
    {
        return [$this->pointerLineNumber, $this->pointerLineCharacter, $this->pointerTemplateCode];
    }

    public function isEscapingEnabled(): bool
    {
        return $this->escapingEnabled;
    }

    public function setEscapingEnabled($escapingEnabled): void
    {
        $this->escapingEnabled = (bool)$escapingEnabled;
    }

    /**
     * Parses a given template string and returns a parsed template object.
     *
     * The resulting ParsedTemplate can then be rendered by calling evaluate() on it.
     *
     * Normally, you should use a subclass of AbstractTemplateView instead of calling the
     * TemplateParser directly.
     *
     * @param string $templateString The template to parse as a string
     * @param string|null $templateIdentifier If the template has an identifying string it can be passed here to improve error reporting.
     * @throws Exception
     */
    public function parse(string $templateString, ?string $templateIdentifier = null): ParsingState
    {
        try {
            $this->reset();

            $templateString = $this->preProcessTemplateSource($templateString);

            $splitTemplate = $this->splitTemplateAtDynamicTags($templateString);
            $parsingState = $this->buildObjectTree($this->createParsingState($templateIdentifier), $splitTemplate, self::CONTEXT_OUTSIDE_VIEWHELPER_ARGUMENTS);
        } catch (Exception $error) {
            throw $this->createParsingRelatedExceptionWithContext($error, $templateIdentifier);
        }
        $this->parsedTemplates[$templateIdentifier] = $parsingState;
        return $parsingState;
    }

    public function createParsingRelatedExceptionWithContext(\Exception $error, ?string $templateIdentifier): \Exception
    {
        list($line, $character, $templateCode) = $this->getCurrentParsingPointers();
        $exceptionClass = get_class($error);
        return new $exceptionClass(
            sprintf(
                'Fluid parse error in template %s, line %d at character %d. Error: %s (error code %d). Template source chunk: %s',
                (string)$templateIdentifier,
                $line,
                $character,
                $error->getMessage(),
                $error->getCode(),
                $templateCode,
            ),
            $error->getCode(),
            $error,
        );
    }

    /**
     * @param \Closure $templateSourceClosure Closure which returns the template source if needed
     */
    public function getOrParseAndStoreTemplate(string $templateIdentifier, \Closure $templateSourceClosure): ParsedTemplateInterface
    {
        $compiler = $this->renderingContext->getTemplateCompiler();
        if (isset($this->parsedTemplates[$templateIdentifier])) {
            $parsedTemplate = $this->parsedTemplates[$templateIdentifier];
        } elseif ($compiler->has($templateIdentifier)) {
            $parsedTemplate = $compiler->get($templateIdentifier);
            if ($parsedTemplate instanceof UncompilableTemplateInterface) {
                $parsedTemplate = $this->parseTemplateSource($templateIdentifier, $templateSourceClosure);
            }
        } else {
            $parsedTemplate = $this->parseTemplateSource($templateIdentifier, $templateSourceClosure);
            try {
                $compiler->store($templateIdentifier, $parsedTemplate);
            } catch (StopCompilingException $stop) {
                $this->renderingContext->getErrorHandler()->handleCompilerError($stop);
                $parsedTemplate->setCompilable(false);
                $compiler->store($templateIdentifier, $parsedTemplate);
            }
        }
        return $parsedTemplate;
    }

    protected function parseTemplateSource(string $templateIdentifier, \Closure $templateSourceClosure): ParsingState
    {
        return $this->parse(
            $templateSourceClosure($this, $this->renderingContext->getTemplatePaths()),
            $templateIdentifier,
        );
    }

    /**
     * Pre-process the template source, making all registered TemplateProcessors
     * do what they need to do with the template source before it is parsed.
     */
    protected function preProcessTemplateSource(string $templateSource): string
    {
        foreach ($this->renderingContext->getTemplateProcessors() as $templateProcessor) {
            $templateSource = $templateProcessor->preProcessSource($templateSource);
        }
        return $templateSource;
    }

    /**
     * Resets the parser to its default values.
     */
    protected function reset(): void
    {
        $this->escapingEnabled = true;
        $this->pointerLineNumber = 1;
        $this->pointerLineCharacter = 1;
    }

    /**
     * Splits the template string on all dynamic tags found.
     *
     * @param string $templateString Template string to split.
     * @return array Splitted template
     */
    protected function splitTemplateAtDynamicTags(string $templateString): array
    {
        return preg_split(Patterns::$SPLIT_PATTERN_TEMPLATE_DYNAMICTAGS, $templateString, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Build object tree from the split template
     *
     * @param array $splitTemplate The split template, so that every tag with a namespace declaration is already a seperate array element.
     * @param int $context one of the CONTEXT_* constants, defining whether we are inside or outside of ViewHelper arguments currently.
     * @throws Exception
     */
    protected function buildObjectTree(ParsingState $state, array $splitTemplate, int $context): ParsingState
    {
        $previousBlock = '';

        foreach ($splitTemplate as $templateElement) {
            if ($context === self::CONTEXT_OUTSIDE_VIEWHELPER_ARGUMENTS) {
                // Store a neat reference to the outermost chunk of Fluid template code.
                // Don't store the reference if parsing ViewHelper arguments object tree;
                // we want the reference code to contain *all* of the ViewHelper call.
                $this->pointerTemplateCode = $templateElement;
            }
            $this->pointerLineNumber += substr_count($templateElement, PHP_EOL);
            $this->pointerLineCharacter = strlen(substr($previousBlock, strrpos($previousBlock, PHP_EOL))) + 1;
            $previousBlock = $templateElement;
            $matchedVariables = [];

            if (preg_match(Patterns::$SCAN_PATTERN_TEMPLATE_VIEWHELPERTAG, $templateElement, $matchedVariables) > 0) {
                try {
                    if ($this->openingViewHelperTagHandler(
                        $state,
                        $matchedVariables['NamespaceIdentifier'],
                        $matchedVariables['MethodIdentifier'],
                        $matchedVariables['Attributes'],
                        ($matchedVariables['Selfclosing'] === '' ? false : true),
                        $templateElement,
                    )) {
                        continue;
                    }
                } catch (\TYPO3Fluid\Fluid\Core\ViewHelper\Exception $error) {
                    $this->textHandler(
                        $state,
                        $this->renderingContext->getErrorHandler()->handleViewHelperError($error),
                    );
                } catch (Exception $error) {
                    $this->textHandler(
                        $state,
                        $this->renderingContext->getErrorHandler()->handleParserError($error),
                    );
                }
            } elseif (preg_match(Patterns::$SCAN_PATTERN_TEMPLATE_CLOSINGVIEWHELPERTAG, $templateElement, $matchedVariables) > 0) {
                // @todo if exceptions happen here, they should be handled by the error handler as well.
                //       Currently, this isn't possible because the parsing state is inconsistent afterwards
                if ($this->closingViewHelperTagHandler(
                    $state,
                    $matchedVariables['NamespaceIdentifier'],
                    $matchedVariables['MethodIdentifier'],
                )) {
                    continue;
                }
            }
            $this->textAndShorthandSyntaxHandler($state, $templateElement, $context);
        }

        if ($state->countNodeStack() !== 1) {
            throw new Exception(
                'Not all tags were closed!',
                1238169398,
            );
        }
        return $state;
    }
    /**
     * Handles an opening or self-closing view helper tag.
     *
     * @param ParsingState $state Current parsing state
     * @param string $namespaceIdentifier Namespace identifier - being looked up in $this->namespaces
     * @param string $methodIdentifier Method identifier
     * @param string $arguments Arguments string, not yet parsed
     * @param bool $selfclosing true, if the tag is a self-closing tag.
     * @param string $templateElement The template code containing the ViewHelper call
     */
    protected function openingViewHelperTagHandler(ParsingState $state, string $namespaceIdentifier, string $methodIdentifier, string $arguments, bool $selfclosing, string $templateElement): ?NodeInterface
    {
        $viewHelperResolver = $this->renderingContext->getViewHelperResolver();
        if ($viewHelperResolver->isNamespaceIgnored($namespaceIdentifier)) {
            return null;
        }
        try {
            if (!$viewHelperResolver->isNamespaceValid($namespaceIdentifier)) {
                throw new UnknownNamespaceException('Unknown Namespace: ' . $namespaceIdentifier);
            }
        } catch (InheritedNamespaceException) {
            // @todo remove with Fluid 5
        }

        $viewHelperNode = $this->initializeViewHelperAndAddItToStack(
            $state,
            $namespaceIdentifier,
            $methodIdentifier,
            fn(ViewHelperNode $viewHelperNode): array => $this->parseArguments($state, $arguments, $viewHelperNode),
        );

        if ($viewHelperNode && $selfclosing === true) {
            $state->popNodeFromStack();
            $this->callInterceptor($viewHelperNode, InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER, $state);
            // This needs to be called here because closingViewHelperTagHandler() is not triggered for self-closing tags
            $state->getNodeFromStack()->addChildNode($viewHelperNode);
        }

        return $viewHelperNode;
    }

    /**
     * Initialize the given ViewHelper and adds it to the current node and to
     * the stack.
     *
     * @param ParsingState $state Current parsing state
     * @param string $namespaceIdentifier Namespace identifier - being looked up in $this->namespaces
     * @param string $methodIdentifier Method identifier
     * @param \Closure $argumentsClosure Closure that generates array of arguments that are passed to the ViewHelper instance
     * @return ViewHelperNode|null An instance of ViewHelperNode if identity was valid - null if the namespace/identity was not registered
     * @throws Exception
     */
    protected function initializeViewHelperAndAddItToStack(ParsingState $state, string $namespaceIdentifier, string $methodIdentifier, \Closure $argumentsClosure): ?NodeInterface
    {
        $viewHelperResolver = $this->renderingContext->getViewHelperResolver();
        if ($viewHelperResolver->isNamespaceIgnored($namespaceIdentifier)) {
            return null;
        }
        try {
            if (!$viewHelperResolver->isNamespaceValid($namespaceIdentifier)) {
                throw new UnknownNamespaceException('Unknown Namespace: ' . $namespaceIdentifier);
            }
        } catch (InheritedNamespaceException) {
            // @todo remove with Fluid 5
            trigger_error(sprintf(
                'ViewHelper call <%1$s:%2$s> in "%3$s" only works because "%1$s" namespace was added in parent template. This will break with Fluid v5.',
                $namespaceIdentifier,
                $methodIdentifier,
                $state->getIdentifier(),
            ), E_USER_DEPRECATED);
        }
        try {
            $currentViewHelperNode = new ViewHelperNode(
                $this->renderingContext,
                $namespaceIdentifier,
                $methodIdentifier,
            );
            $currentViewHelperNode->setArguments($argumentsClosure($currentViewHelperNode));

            $this->callInterceptor($currentViewHelperNode, InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER, $state);
            // @todo We cannot be sure that the interceptor still returns a ViewHelper node, which is why
            //       the following code has phpstan warnings. However, it is currently not easily possible to
            //       add own interceptors, and the existing ones don't affect opening ViewHelpers, so for now
            //       it works.
            $viewHelper = $currentViewHelperNode->getUninitializedViewHelper();
            $viewHelperClassName = $currentViewHelperNode->getViewHelperClassName();
            // @todo Remove fallback implementation with Fluid v5
            if (method_exists($viewHelperClassName, 'postParseEvent')) {
                trigger_error('postParseEvent() has been deprecated and will be removed in Fluid v5.', E_USER_DEPRECATED);
                $viewHelperClassName::postParseEvent($currentViewHelperNode, $currentViewHelperNode->getArguments(), $state->getVariableContainer());
            }
            if ($viewHelper instanceof ViewHelperNodeInitializedEventInterface) {
                $viewHelperClassName::nodeInitializedEvent($currentViewHelperNode, $currentViewHelperNode->getArguments(), $state);
            }
            $state->pushNodeToStack($currentViewHelperNode);
            return $currentViewHelperNode;
        } catch (\TYPO3Fluid\Fluid\Core\ViewHelper\Exception $error) {
            $this->textHandler(
                $state,
                $this->renderingContext->getErrorHandler()->handleViewHelperError($error),
            );
        } catch (Exception $error) {
            $this->textHandler(
                $state,
                $this->renderingContext->getErrorHandler()->handleParserError($error),
            );
        }
        return null;
    }

    /**
     * Handles a closing view helper tag
     *
     * @param ParsingState $state The current parsing state
     * @param string $namespaceIdentifier Namespace identifier for the closing tag.
     * @param string $methodIdentifier Method identifier.
     * @return bool whether the viewHelper was found and added to the stack or not
     * @throws Exception
     */
    protected function closingViewHelperTagHandler(ParsingState $state, string $namespaceIdentifier, string $methodIdentifier): bool
    {
        $viewHelperResolver = $this->renderingContext->getViewHelperResolver();
        if ($viewHelperResolver->isNamespaceIgnored($namespaceIdentifier)) {
            return false;
        }
        try {
            if (!$viewHelperResolver->isNamespaceValid($namespaceIdentifier)) {
                throw new UnknownNamespaceException('Unknown Namespace: ' . $namespaceIdentifier);
            }
        } catch (InheritedNamespaceException) {
            // @todo remove with Fluid 5
        }
        $lastStackElement = $state->popNodeFromStack();
        if (!($lastStackElement instanceof ViewHelperNode)) {
            throw new Exception('You closed a templating tag which you never opened!', 1224485838);
        }
        $actualViewHelperClassName = $viewHelperResolver->resolveViewHelperClassName($namespaceIdentifier, $methodIdentifier);
        $expectedViewHelperClassName = $lastStackElement->getViewHelperClassName();
        if ($actualViewHelperClassName !== $expectedViewHelperClassName) {
            throw new Exception(
                'Templating tags not properly nested. Expected: ' . $expectedViewHelperClassName . '; Actual: ' .
                $actualViewHelperClassName,
                1224485398,
            );
        }
        $this->callInterceptor($lastStackElement, InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER, $state);
        $state->getNodeFromStack()->addChildNode($lastStackElement);

        return true;
    }

    /**
     * Handles the appearance of an object accessor (like {posts.author.email}).
     * Creates a new instance of \TYPO3Fluid\Fluid\ObjectAccessorNode.
     *
     * Handles ViewHelpers as well which are in the shorthand syntax.
     *
     * @param ParsingState $state The current parsing state
     * @param string $objectAccessorString String which identifies which objects to fetch
     * @return bool  true if the object accessor has been added to the node tree
     */
    protected function objectAccessorHandler(ParsingState $state, string $objectAccessorString, string $delimiter, string $viewHelperString, string $additionalViewHelpersString): bool
    {
        $viewHelperString .= $additionalViewHelpersString;
        $numberOfViewHelpers = 0;

        // The following post-processing handles a case when there is only a ViewHelper, and no Object Accessor.
        // Resolves bug #5107.
        if (strlen($delimiter) === 0 && strlen($viewHelperString) > 0) {
            $viewHelperString = $objectAccessorString . $viewHelperString;
            $objectAccessorString = '';
        }

        // ViewHelpers
        $matches = [];
        if (strlen($viewHelperString) > 0 && preg_match_all(Patterns::$SPLIT_PATTERN_SHORTHANDSYNTAX_VIEWHELPER, $viewHelperString, $matches, PREG_SET_ORDER) > 0) {
            // First validate all ViewHelper namespace in the chain.
            // The last ViewHelper has to be processed first for correct chaining.
            $matches = array_reverse($matches);
            $ignoredNamespaceInChain = false;
            $viewHelperResolver = $this->renderingContext->getViewHelperResolver();
            foreach ($matches as $singleMatch) {
                // Check for ignored ViewHelper namespace
                if ($viewHelperResolver->isNamespaceIgnored($singleMatch['NamespaceIdentifier'])) {
                    $ignoredNamespaceInChain = true;
                    continue;
                }
                // There still should be an exception if a ViewHelper namespace in the chain cannot
                // be resolved, even if the whole chain is ignored later
                try {
                    if (!$viewHelperResolver->isNamespaceValid($singleMatch['NamespaceIdentifier'])) {
                        throw new UnknownNamespaceException('Unknown Namespace: ' . $singleMatch['NamespaceIdentifier']);
                    }
                } catch (InheritedNamespaceException) {
                    // @todo remove with Fluid 5
                }
            }

            // If (at least) one ViewHelper's namespace is ignored, the whole chain of ViewHelpers
            // is skipped and left as-is in the template.
            if ($ignoredNamespaceInChain) {
                return false;
            }

            foreach ($matches as $singleMatch) {
                $viewHelperNode = $this->initializeViewHelperAndAddItToStack(
                    $state,
                    $singleMatch['NamespaceIdentifier'],
                    $singleMatch['MethodIdentifier'],
                    fn(ViewHelperNode $viewHelperNode): array => (strlen($singleMatch['ViewHelperArguments']) > 0) ? $this->recursiveArrayHandler($state, $singleMatch['ViewHelperArguments'], $viewHelperNode) : [],
                );
                if ($viewHelperNode) {
                    $numberOfViewHelpers++;
                }
            }
        }

        // Object Accessor
        if (strlen($objectAccessorString) > 0) {
            $node = new ObjectAccessorNode($objectAccessorString);
            $this->callInterceptor($node, InterceptorInterface::INTERCEPT_OBJECTACCESSOR, $state);
            $state->getNodeFromStack()->addChildNode($node);
        }

        // Close ViewHelper Tags if needed.
        for ($i = 0; $i < $numberOfViewHelpers; $i++) {
            $node = $state->popNodeFromStack();
            $this->callInterceptor($node, InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER, $state);
            $state->getNodeFromStack()->addChildNode($node);
        }

        return true;
    }

    /**
     * Call all interceptors registered for a given interception point.
     *
     * @todo switch from call-by-reference to return value
     * @param NodeInterface $node The syntax tree node which can be modified by the interceptors.
     * @param int $interceptionPoint the interception point. One of the \TYPO3Fluid\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_* constants.
     * @param ParsingState $state the parsing state
     */
    protected function callInterceptor(NodeInterface &$node, int $interceptionPoint, ParsingState $state): void
    {
        if ($this->configuration === null) {
            return;
        }
        if ($this->escapingEnabled) {
            /** @var InterceptorInterface $interceptor */
            foreach ($this->configuration->getEscapingInterceptors($interceptionPoint) as $interceptor) {
                $node = $interceptor->process($node, $interceptionPoint, $state);
            }
        }

        /** @var InterceptorInterface $interceptor */
        foreach ($this->configuration->getInterceptors($interceptionPoint) as $interceptor) {
            $node = $interceptor->process($node, $interceptionPoint, $state);
        }
    }

    /**
     * Parse arguments of a given tag, and build up the Arguments Object Tree
     * for each argument.
     * Returns an associative array, where the key is the name of the argument,
     * and the value is a single Argument Object Tree.
     *
     * @param string $argumentsString All arguments as string
     * @return array An associative array of objects, where the key is the argument name.
     */
    protected function parseArguments(ParsingState $state, string $argumentsString, ViewHelperNode $viewHelperNode): array
    {
        $argumentDefinitions = $viewHelperNode->getArgumentDefinitions();
        $argumentsObjectTree = [];
        $undeclaredArguments = [];
        $matches = [];
        if (preg_match_all(Patterns::$SPLIT_PATTERN_TAGARGUMENTS, $argumentsString, $matches, PREG_SET_ORDER) > 0) {
            foreach ($matches as $singleMatch) {
                $argument = $singleMatch['Argument'];
                $value = $this->unquoteString($singleMatch['ValueQuoted']);
                $escapingEnabledBackup = $this->escapingEnabled;
                if (isset($argumentDefinitions[$argument])) {
                    $argumentDefinition = $argumentDefinitions[$argument];
                    $this->escapingEnabled = $this->escapingEnabled && $this->isArgumentEscaped($viewHelperNode->getUninitializedViewHelper(), $argumentDefinition);
                    $argumentsObjectTree[$argument] = $this->buildArgumentObjectTree($state, $value);
                    if ($argumentDefinition->isBooleanType()) {
                        $argumentsObjectTree[$argument] = new BooleanNode($argumentsObjectTree[$argument]);
                    }
                } else {
                    $this->escapingEnabled = false;
                    $undeclaredArguments[$argument] = $this->buildArgumentObjectTree($state, $value);
                }
                $this->escapingEnabled = $escapingEnabledBackup;
            }
        }
        $this->abortIfRequiredArgumentsAreMissing($argumentDefinitions, $argumentsObjectTree);
        $viewHelperNode->getUninitializedViewHelper()->validateAdditionalArguments($undeclaredArguments);
        return $argumentsObjectTree + $undeclaredArguments;
    }

    protected function isArgumentEscaped(ViewHelperInterface $viewHelper, ?ArgumentDefinition $argumentDefinition = null): bool
    {
        $hasDefinition = $argumentDefinition instanceof ArgumentDefinition;
        $isBoolean = $hasDefinition && $argumentDefinition->isBooleanType();
        $escapingEnabled = $this->configuration->isViewHelperArgumentEscapingEnabled();
        $isArgumentEscaped = $hasDefinition && $argumentDefinition->getEscape() === true;
        $isContentArgument = $hasDefinition && $argumentDefinition->getName() === $viewHelper->getContentArgumentName();
        if ($isContentArgument) {
            return !$isBoolean && ($viewHelper->isChildrenEscapingEnabled() || $isArgumentEscaped);
        }
        return !$isBoolean && $escapingEnabled && $isArgumentEscaped;
    }

    /**
     * Build up an argument object tree for the string in $argumentString.
     * This builds up the tree for a single argument value.
     *
     * This method also does some performance optimizations, so in case
     * no { or < is found, then we just return a TextNode.
     *
     * @return RootNode|NumericNode|TextNode the corresponding argument object tree.
     */
    protected function buildArgumentObjectTree(ParsingState $state, string $argumentString): RootNode|NumericNode|TextNode
    {
        // @todo Evaluate if it's worth it to have this detail optimization if
        //       the majority of the templates are cached anyways. This would
        //       simplify the method signature
        if (strpos($argumentString, '{') === false && strpos($argumentString, '<') === false) {
            if (is_numeric($argumentString)) {
                return new NumericNode($argumentString);
            }
            return new TextNode($argumentString);
        }
        $splitArgument = $this->splitTemplateAtDynamicTags($argumentString);
        // At this stage, Fluid creates a sub template with its own ParsingState
        // and RootNode. While this currently works in practice, conceptually
        // this is problematic: There is no way to influence the resulting
        // parsed template from the sub template, e. g. from a ViewHelper
        // event. All changes made to the inner ParsingState are not propagated
        // to the outer ParsingState.
        // In practice this is relevant for <f:slot /> when the slot output
        // is used within a Fluid string:
        // {f:if(condition: '{f:slot()}', then: '{f:slot()}', else: 'fallback')}
        // The SlotViewHelper adds an available slot to the inner ParsingState,
        // but this is not applied to the ParsingState of the whole template
        // @todo there should be separate state objects for the whole template
        //       and template parts that are parsed separately. Some changes
        //       should be applied to the global state, while others should only
        //       affect the local state. Maybe it's also possible to get rid
        //       of the local state altogether.
        $innerState = $this->buildObjectTree($this->createParsingState(null), $splitArgument, self::CONTEXT_INSIDE_VIEWHELPER_ARGUMENTS);
        // This can be removed once the outer-inner-state issue is resolved
        $state->setAvailableSlots(array_unique(array_merge(
            $state->getAvailableSlots(),
            $innerState->getAvailableSlots(),
        )));
        return $innerState->getRootNode();
    }

    /**
     * Removes escapings from a given argument string and trims the outermost
     * quotes.
     *
     * This method is meant as a helper for regular expression results.
     *
     * @param string $quotedValue Value to unquote
     * @return string Unquoted value
     */
    public function unquoteString(string $quotedValue): string
    {
        $value = $quotedValue;
        if ($value === '') {
            return $value;
        }
        if ($quotedValue[0] === '"') {
            $value = str_replace('\\"', '"', preg_replace('/(^"|"$)/', '', $quotedValue));
        } elseif ($quotedValue[0] === '\'') {
            $value = str_replace("\\'", "'", preg_replace('/(^\'|\'$)/', '', $quotedValue));
        }
        return str_replace('\\\\', '\\', $value);
    }

    /**
     * Handler for everything which is not a ViewHelperNode.
     *
     * This includes Text, array syntax, and object accessor syntax.
     *
     * @param ParsingState $state Current parsing state
     * @param string $text Text to process
     * @param int $context one of the CONTEXT_* constants, defining whether we are inside or outside of ViewHelper arguments currently.
     */
    protected function textAndShorthandSyntaxHandler(ParsingState $state, string $text, int $context): void
    {
        $sections = preg_split(Patterns::$SPLIT_PATTERN_SHORTHANDSYNTAX, $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        if ($sections === false) {
            // String $text was not possible to split; we must return a text node with the full text instead.
            $this->textHandler($state, $text);
            return;
        }
        foreach ($sections as $section) {
            $matchedVariables = [];
            $expressionNode = null;
            if (preg_match(Patterns::$SCAN_PATTERN_SHORTHANDSYNTAX_OBJECTACCESSORS, $section, $matchedVariables) > 0) {
                try {
                    if (!$this->objectAccessorHandler(
                        $state,
                        $matchedVariables['Object'],
                        $matchedVariables['Delimiter'],
                        (isset($matchedVariables['ViewHelper']) ? $matchedVariables['ViewHelper'] : ''),
                        (isset($matchedVariables['AdditionalViewHelpers']) ? $matchedVariables['AdditionalViewHelpers'] : ''),
                    )) {
                        // As fallback we simply render the accessor back as template content.
                        $this->textHandler($state, $section);
                    }
                } catch (\TYPO3Fluid\Fluid\Core\ViewHelper\Exception $error) {
                    $this->textHandler(
                        $state,
                        $this->renderingContext->getErrorHandler()->handleViewHelperError($error),
                    );
                } catch (Exception $error) {
                    $this->textHandler(
                        $state,
                        $this->renderingContext->getErrorHandler()->handleParserError($error),
                    );
                }
            } elseif ($context === self::CONTEXT_INSIDE_VIEWHELPER_ARGUMENTS
                && preg_match(Patterns::$SCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS, $section, $matchedVariables) > 0
            ) {
                // We only match arrays if we are INSIDE viewhelper arguments
                $this->arrayHandler($state, $this->recursiveArrayHandler($state, $matchedVariables['Array']));
            } else {
                // We ask custom ExpressionNode instances from ViewHelperResolver
                // if any match our expression:
                foreach ($this->renderingContext->getExpressionNodeTypes() as $expressionNodeTypeClassName) {
                    $detectionExpression = $expressionNodeTypeClassName::$detectionExpression;
                    $matchedVariables = [];
                    preg_match_all($detectionExpression, $section, $matchedVariables, PREG_SET_ORDER);
                    foreach ($matchedVariables as $matchedVariableSet) {
                        $expressionStartPosition = strpos($section, $matchedVariableSet[0]);
                        /** @var ExpressionNodeInterface $expressionNode */
                        $expressionNode = new $expressionNodeTypeClassName($matchedVariableSet[0], $matchedVariableSet, $state);
                        try {
                            if ($expressionStartPosition > 0) {
                                $state->getNodeFromStack()->addChildNode(new TextNode(substr($section, 0, $expressionStartPosition)));
                            }

                            $this->callInterceptor($expressionNode, InterceptorInterface::INTERCEPT_EXPRESSION, $state);
                            $state->getNodeFromStack()->addChildNode($expressionNode);

                            $expressionEndPosition = $expressionStartPosition + strlen($matchedVariableSet[0]);
                            if ($expressionEndPosition < strlen($section)) {
                                $this->textAndShorthandSyntaxHandler($state, substr($section, $expressionEndPosition), $context);
                                break;
                            }
                        } catch (ExpressionException $error) {
                            $this->textHandler(
                                $state,
                                $this->renderingContext->getErrorHandler()->handleExpressionError($error),
                            );
                        }
                    }
                }

                if (!$expressionNode) {
                    // As fallback we simply render the expression back as template content.
                    $this->textHandler($state, $section);
                }
            }
        }
    }

    /**
     * Handler for array syntax. This creates the array object recursively and
     * adds it to the current node.
     *
     * @param ParsingState $state The current parsing state
     * @param NodeInterface[] $arrayText The array as string.
     * @todo determine if NodeInterface[] is really correct here, maybe it's also string[] in cached context?
     */
    protected function arrayHandler(ParsingState $state, array $arrayText): void
    {
        $arrayNode = new ArrayNode($arrayText);
        $state->getNodeFromStack()->addChildNode($arrayNode);
    }

    /**
     * Recursive function which takes the string representation of an array and
     * builds an object tree from it.
     *
     * Deals with the following value types:
     * - Numbers (Integers and Floats)
     * - Strings
     * - Variables
     * - sub-arrays
     *
     * @param string $arrayText Array text
     * @param ViewHelperNode|null $viewHelperNode ViewHelper node - passed only if the array is a collection of arguments for an inline ViewHelper
     * @return NodeInterface[] the array node built up
     * @throws Exception
     */
    protected function recursiveArrayHandler(ParsingState $state, string $arrayText, ?ViewHelperNode $viewHelperNode = null): array
    {
        $undeclaredArguments = [];
        $argumentDefinitions = [];
        if ($viewHelperNode instanceof ViewHelperNode) {
            $argumentDefinitions = $viewHelperNode->getArgumentDefinitions();
        }
        $matches = [];
        $arrayToBuild = [];
        if (preg_match_all(Patterns::$SPLIT_PATTERN_SHORTHANDSYNTAX_ARRAY_PARTS, $arrayText, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $singleMatch) {
                $arrayKey = $this->unquoteString($singleMatch['Key']);
                $assignInto = &$arrayToBuild;
                $isBoolean = false;
                $argumentDefinition = null;
                if (isset($argumentDefinitions[$arrayKey])) {
                    $argumentDefinition = $argumentDefinitions[$arrayKey];
                    $isBoolean = $argumentDefinitions[$arrayKey]->isBooleanType();
                } else {
                    $assignInto = &$undeclaredArguments;
                }

                $escapingEnabledBackup = $this->escapingEnabled;
                $this->escapingEnabled = $this->escapingEnabled && $viewHelperNode instanceof ViewHelperNode && $this->isArgumentEscaped($viewHelperNode->getUninitializedViewHelper(), $argumentDefinition);

                if (array_key_exists('Subarray', $singleMatch) && !empty($singleMatch['Subarray'])) {
                    $assignInto[$arrayKey] = new ArrayNode($this->recursiveArrayHandler($state, $singleMatch['Subarray']));
                } elseif (!empty($singleMatch['VariableIdentifier'])) {
                    $assignInto[$arrayKey] = new ObjectAccessorNode($singleMatch['VariableIdentifier']);
                    if ($viewHelperNode instanceof ViewHelperNode && !$isBoolean) {
                        $this->callInterceptor($assignInto[$arrayKey], InterceptorInterface::INTERCEPT_OBJECTACCESSOR, $state);
                    }
                } elseif (array_key_exists('Number', $singleMatch) && (!empty($singleMatch['Number']) || $singleMatch['Number'] === '0')) {
                    // Note: this method of casting picks "int" when value is a natural number and "float" if any decimals are found. See also NumericNode.
                    $assignInto[$arrayKey] = $singleMatch['Number'] + 0;
                } elseif ((array_key_exists('QuotedString', $singleMatch) && !empty($singleMatch['QuotedString']))) {
                    $argumentString = $this->unquoteString($singleMatch['QuotedString']);
                    $assignInto[$arrayKey] = $this->buildArgumentObjectTree($state, $argumentString);
                }

                if ($isBoolean) {
                    $assignInto[$arrayKey] = new BooleanNode($assignInto[$arrayKey]);
                }

                $this->escapingEnabled = $escapingEnabledBackup;
            }
        }
        if ($viewHelperNode instanceof ViewHelperNode) {
            $this->abortIfRequiredArgumentsAreMissing($argumentDefinitions, $arrayToBuild);
            $viewHelperNode->getUninitializedViewHelper()->validateAdditionalArguments($undeclaredArguments);
        }
        return $arrayToBuild + $undeclaredArguments;
    }

    /**
     * Text node handler
     */
    protected function textHandler(ParsingState $state, string $text): void
    {
        $node = new TextNode($text);
        $this->callInterceptor($node, InterceptorInterface::INTERCEPT_TEXT, $state);
        $state->getNodeFromStack()->addChildNode($node);
    }

    protected function createParsingState(?string $templateIdentifier): ParsingState
    {
        $rootNode = new RootNode();
        $variableProvider = $this->renderingContext->getVariableProvider();
        $state = new ParsingState();
        $state->setIdentifier($templateIdentifier ?? '');
        $state->setRootNode($rootNode);
        $state->pushNodeToStack($rootNode);
        $state->setVariableProvider($variableProvider->getScopeCopy($variableProvider->getAll()));
        return $state;
    }

    /**
     * Throw an exception if required arguments are missing
     *
     * @param ArgumentDefinition[] $expectedArguments Array of all expected arguments
     * @param NodeInterface[] $actualArguments Actual arguments
     * @throws Exception
     */
    protected function abortIfRequiredArgumentsAreMissing(array $expectedArguments, array $actualArguments): void
    {
        $actualArgumentNames = array_keys($actualArguments);
        foreach ($expectedArguments as $name => $expectedArgument) {
            if ($expectedArgument->isRequired() && !in_array($name, $actualArgumentNames)) {
                throw new Exception('Required argument "' . $name . '" was not supplied.', 1237823699);
            }
        }
    }
}
