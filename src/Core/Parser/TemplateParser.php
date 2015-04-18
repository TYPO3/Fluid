<?php
namespace TYPO3\Fluid\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3\Fluid\Core\ViewHelper\CompilableInterface;
use TYPO3\Fluid\Core\ViewHelper\ViewHelperResolver;

/**
 * Template parser building up an object syntax tree
 */
class TemplateParser {

	/**
	 * The following two constants are used for tracking whether we are currently
	 * parsing ViewHelper arguments or not. This is used to parse arrays only as
	 * ViewHelper argument.
	 */
	const CONTEXT_INSIDE_VIEWHELPER_ARGUMENTS = 1;
	const CONTEXT_OUTSIDE_VIEWHELPER_ARGUMENTS = 2;

	/**
	 * Whether or not the escaping interceptors are active
	 *
	 * @var boolean
	 */
	protected $escapingEnabled = TRUE;

	/**
	 * @var Configuration
	 */
	protected $configuration;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @var ViewHelperResolver
	 */
	protected $viewHelperResolver;

	/**
	 * Constructor
	 */
	public function __construct(ViewHelperResolver $viewHelperResolver = NULL) {
		if (!$viewHelperResolver) {
			$viewHelperResolver = new ViewHelperResolver();
		}
		$this->viewHelperResolver = $viewHelperResolver;
	}

	/**
	 * @param ViewHelperResolver $viewHelperResolver
	 * @return void
	 */
	public function setViewHelperResolver(ViewHelperResolver $viewHelperResolver) {
		$this->viewHelperResolver = $viewHelperResolver;
	}

	/**
	 * Set the configuration for the parser.
	 *
	 * @param Configuration $configuration
	 * @return void
	 */
	public function setConfiguration(Configuration $configuration = NULL) {
		$this->configuration = $configuration;
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
	 * @return ParsedTemplateInterface Parsed template
	 * @throws Exception
	 */
	public function parse($templateString) {
		if (!is_string($templateString)) {
			throw new Exception('Parse requires a template string as argument, ' . gettype($templateString) . ' given.', 1224237899);
		}

		$this->reset();

		$splitTemplate = $this->splitTemplateAtDynamicTags($templateString);
		$parsingState = $this->buildObjectTree($splitTemplate, self::CONTEXT_OUTSIDE_VIEWHELPER_ARGUMENTS);

		$variableContainer = $parsingState->getVariableContainer();
		if ($variableContainer !== NULL && $variableContainer->exists('layoutName')) {
			$parsingState->setLayoutNameNode($variableContainer->get('layoutName'));
		}

		return $parsingState;
	}

	/**
	 * Resets the parser to its default values.
	 *
	 * @return void
	 */
	protected function reset() {
		$this->escapingEnabled = TRUE;
	}

	/**
	 * Splits the template string on all dynamic tags found.
	 *
	 * @param string $templateString Template string to split.
	 * @return array Splitted template
	 */
	protected function splitTemplateAtDynamicTags($templateString) {
		return preg_split(Patterns::$SPLIT_PATTERN_TEMPLATE_DYNAMICTAGS, $templateString, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
	}

	/**
	 * Build object tree from the split template
	 *
	 * @param array $splitTemplate The split template, so that every tag with a namespace declaration is already a seperate array element.
	 * @param integer $context one of the CONTEXT_* constants, defining whether we are inside or outside of ViewHelper arguments currently.
	 * @return ParsingState
	 * @throws Exception
	 */
	protected function buildObjectTree(array $splitTemplate, $context) {
		$state = $this->getParsingState();

		foreach ($splitTemplate as $templateElement) {
			$matchedVariables = array();
			if (preg_match_all(Patterns::$SPLIT_PATTERN_TEMPLATE_OPEN_NAMESPACETAG, $templateElement, $matchedVariables, PREG_SET_ORDER) > 0) {
				foreach ($matchedVariables as $namespaceMatch) {
					$viewHelperNamespace = $this->unquoteString($namespaceMatch[2]);
					$phpNamespace = $this->viewHelperResolver->resolvePhpNamespaceFromFluidNamespace($viewHelperNamespace);
					$this->viewHelperResolver->registerNamespace($namespaceMatch[1], $phpNamespace);
				}
				continue;
			} elseif (trim($templateElement) === '</f:fluid>' || trim($templateElement) === '</fluid>') {
				continue;
			} elseif (preg_match(Patterns::$SCAN_PATTERN_CDATA, $templateElement, $matchedVariables) > 0) {
				$this->textHandler($state, $matchedVariables[1]);
				continue;
			} elseif (preg_match(Patterns::$SCAN_PATTERN_TEMPLATE_VIEWHELPERTAG, $templateElement, $matchedVariables) > 0) {
				$viewHelperWasOpened = $this->openingViewHelperTagHandler(
					$state,
					$matchedVariables['NamespaceIdentifier'],
					$matchedVariables['MethodIdentifier'],
					$matchedVariables['Attributes'],
					($matchedVariables['Selfclosing'] === '' ? FALSE : TRUE)
				);
				if ($viewHelperWasOpened === TRUE) {
					continue;
				}
			} elseif (preg_match(Patterns::$SCAN_PATTERN_TEMPLATE_CLOSINGVIEWHELPERTAG, $templateElement, $matchedVariables) > 0) {
				$viewHelperWasClosed = $this->closingViewHelperTagHandler(
					$state,
					$matchedVariables['NamespaceIdentifier'],
					$matchedVariables['MethodIdentifier']
				);
				if ($viewHelperWasClosed === TRUE) {
					continue;
				}
			}

			$this->textAndShorthandSyntaxHandler($state, $templateElement, $context);
		}

		if ($state->countNodeStack() !== 1) {
			throw new Exception('Not all tags were closed!', 1238169398);
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
	 * @param boolean $selfclosing true, if the tag is a self-closing tag.
	 * @return boolean
	 */
	protected function openingViewHelperTagHandler(ParsingState $state, $namespaceIdentifier, $methodIdentifier, $arguments, $selfclosing) {
		$argumentsObjectTree = $this->parseArguments($arguments);
		$viewHelperWasOpened = $this->initializeViewHelperAndAddItToStack($state, $namespaceIdentifier, $methodIdentifier, $argumentsObjectTree);

		if ($viewHelperWasOpened === TRUE && $selfclosing === TRUE) {
			$node = $state->popNodeFromStack();
			$this->callInterceptor($node, InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER, $state);
			// This needs to be called here because closingViewHelperTagHandler() is not triggered for self-closing tags
			$state->getNodeFromStack()->addChildNode($node);
		}

		return $viewHelperWasOpened;
	}

	/**
	 * Initialize the given ViewHelper and adds it to the current node and to
	 * the stack.
	 *
	 * @param ParsingState $state Current parsing state
	 * @param string $namespaceIdentifier Namespace identifier - being looked up in $this->namespaces
	 * @param string $methodIdentifier Method identifier
	 * @param array $argumentsObjectTree Arguments object tree
	 * @return boolean whether the viewHelper was found and added to the stack or not
	 * @throws Exception
	 */
	protected function initializeViewHelperAndAddItToStack(ParsingState $state, $namespaceIdentifier, $methodIdentifier, $argumentsObjectTree) {
		if ($this->viewHelperResolver->isNamespaceValid($namespaceIdentifier, $methodIdentifier) === FALSE) {
			return FALSE;
		}
		$currentViewHelperNode = new ViewHelperNode(
			$this->viewHelperResolver,
			$namespaceIdentifier,
			$methodIdentifier,
			$argumentsObjectTree,
			$state
		);
		$viewHelper = $currentViewHelperNode->getUninitializedViewHelper();

		$this->callInterceptor($currentViewHelperNode, InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER, $state);

		if ($viewHelper instanceof CompilableInterface) {
			$state->setCompilable(FALSE);
		}

		if (method_exists($viewHelper, 'postParseEvent')) {
			$viewHelper::postParseEvent($currentViewHelperNode, $argumentsObjectTree, $state->getVariableContainer());
		}

		$state->pushNodeToStack($currentViewHelperNode);

		return TRUE;
	}

	/**
	 * Handles a closing view helper tag
	 *
	 * @param ParsingState $state The current parsing state
	 * @param string $namespaceIdentifier Namespace identifier for the closing tag.
	 * @param string $methodIdentifier Method identifier.
	 * @return boolean whether the viewHelper was found and added to the stack or not
	 * @throws Exception
	 */
	protected function closingViewHelperTagHandler(ParsingState $state, $namespaceIdentifier, $methodIdentifier) {
		if ($this->viewHelperResolver->isNamespaceValid($namespaceIdentifier, $methodIdentifier) === FALSE) {
			return FALSE;
		}

		$lastStackElement = $state->popNodeFromStack();
		if (!($lastStackElement instanceof ViewHelperNode)) {
			throw new Exception('You closed a templating tag which you never opened!', 1224485838);
		}
		$actualViewHelperClassName = $this->viewHelperResolver->resolveViewHelperClassName($namespaceIdentifier, $methodIdentifier);
		$expectedViewHelperClassName = $lastStackElement->getViewHelperClassName();
		if ($actualViewHelperClassName !== $expectedViewHelperClassName) {
			throw new Exception(
				'Templating tags not properly nested. Expected: ' . $expectedViewHelperClassName . '; Actual: ' .
				$actualViewHelperClassName,
				1224485398
			);
		}
		$this->callInterceptor($lastStackElement, InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER, $state);
		$state->getNodeFromStack()->addChildNode($lastStackElement);

		return TRUE;
	}

	/**
	 * Handles the appearance of an object accessor (like {posts.author.email}).
	 * Creates a new instance of \TYPO3\Fluid\ObjectAccessorNode.
	 *
	 * Handles ViewHelpers as well which are in the shorthand syntax.
	 *
	 * @param ParsingState $state The current parsing state
	 * @param string $objectAccessorString String which identifies which objects to fetch
	 * @param string $delimiter
	 * @param string $viewHelperString
	 * @param string $additionalViewHelpersString
	 * @return void
	 */
	protected function objectAccessorHandler(ParsingState $state, $objectAccessorString, $delimiter, $viewHelperString, $additionalViewHelpersString) {
		$viewHelperString .= $additionalViewHelpersString;
		$numberOfViewHelpers = 0;

		// The following post-processing handles a case when there is only a ViewHelper, and no Object Accessor.
		// Resolves bug #5107.
		if (strlen($delimiter) === 0 && strlen($viewHelperString) > 0) {
			$viewHelperString = $objectAccessorString . $viewHelperString;
			$objectAccessorString = '';
		}

		// ViewHelpers
		$matches = array();
		if (strlen($viewHelperString) > 0 && preg_match_all(Patterns::$SPLIT_PATTERN_SHORTHANDSYNTAX_VIEWHELPER, $viewHelperString, $matches, PREG_SET_ORDER) > 0) {
			// The last ViewHelper has to be added first for correct chaining.
			foreach (array_reverse($matches) as $singleMatch) {
				if (strlen($singleMatch['ViewHelperArguments']) > 0) {
					$arguments = $this->recursiveArrayHandler($singleMatch['ViewHelperArguments']);
				} else {
					$arguments = array();
				}
				$viewHelperWasAdded = $this->initializeViewHelperAndAddItToStack($state, $singleMatch['NamespaceIdentifier'], $singleMatch['MethodIdentifier'], $arguments);
				if ($viewHelperWasAdded === TRUE) {
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
	}

	/**
	 * Call all interceptors registered for a given interception point.
	 *
	 * @param NodeInterface $node The syntax tree node which can be modified by the interceptors.
	 * @param integer $interceptionPoint the interception point. One of the \TYPO3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_* constants.
	 * @param ParsingState $state the parsing state
	 * @return void
	 */
	protected function callInterceptor(NodeInterface &$node, $interceptionPoint, ParsingState $state) {
		if ($this->configuration === NULL) {
			return;
		}
		if ($this->escapingEnabled) {
			/** @var $interceptor InterceptorInterface */
			foreach ($this->configuration->getEscapingInterceptors($interceptionPoint) as $interceptor) {
				$node = $interceptor->process($node, $interceptionPoint, $state);
			}
		}

		/** @var $interceptor InterceptorInterface */
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
	protected function parseArguments($argumentsString) {
		$argumentsObjectTree = array();
		$matches = array();
		if (preg_match_all(Patterns::$SPLIT_PATTERN_TAGARGUMENTS, $argumentsString, $matches, PREG_SET_ORDER) > 0) {
			$escapingEnabledBackup = $this->escapingEnabled;
			$this->escapingEnabled = FALSE;
			foreach ($matches as $singleMatch) {
				$argument = $singleMatch['Argument'];
				$value = $this->unquoteString($singleMatch['ValueQuoted']);
				$argumentsObjectTree[$argument] = $this->buildArgumentObjectTree($value);
			}
			$this->escapingEnabled = $escapingEnabledBackup;
		}
		return $argumentsObjectTree;
	}

	/**
	 * Build up an argument object tree for the string in $argumentString.
	 * This builds up the tree for a single argument value.
	 *
	 * This method also does some performance optimizations, so in case
	 * no { or < is found, then we just return a TextNode.
	 *
	 * @param string $argumentString
	 * @return SyntaxTree\NodeInterface the corresponding argument object tree.
	 */
	protected function buildArgumentObjectTree($argumentString) {
		if (strpos($argumentString, '{') === FALSE && strpos($argumentString, '<') === FALSE) {
			return new TextNode($argumentString);
		}
		$splitArgument = $this->splitTemplateAtDynamicTags($argumentString);
		$rootNode = $this->buildObjectTree($splitArgument, self::CONTEXT_INSIDE_VIEWHELPER_ARGUMENTS)->getRootNode();
		return $rootNode;
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
	protected function unquoteString($quotedValue) {
		$value = $quotedValue;
		if ($quotedValue{0} === '"') {
			$value = str_replace('\\"', '"', preg_replace('/(^"|"$)/', '', $quotedValue));
		} elseif ($quotedValue{0} === '\'') {
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
	 * @param integer $context one of the CONTEXT_* constants, defining whether we are inside or outside of ViewHelper arguments currently.
	 * @return void
	 */
	protected function textAndShorthandSyntaxHandler(ParsingState $state, $text, $context) {
		$sections = preg_split(Patterns::$SPLIT_PATTERN_SHORTHANDSYNTAX, $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		foreach ($sections as $section) {
			$matchedVariables = array();
			$expressionNode = NULL;
			if (preg_match(Patterns::$SCAN_PATTERN_SHORTHANDSYNTAX_OBJECTACCESSORS, $section, $matchedVariables) > 0) {
				$this->objectAccessorHandler(
					$state,
					$matchedVariables['Object'],
					$matchedVariables['Delimiter'],
					(isset($matchedVariables['ViewHelper']) ? $matchedVariables['ViewHelper'] : ''),
					(isset($matchedVariables['AdditionalViewHelpers']) ? $matchedVariables['AdditionalViewHelpers'] : '')
				);
			} elseif (
				$context === self::CONTEXT_INSIDE_VIEWHELPER_ARGUMENTS
				&& preg_match(Patterns::$SCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS, $section, $matchedVariables) > 0
			) {
				// We only match arrays if we are INSIDE viewhelper arguments
				$this->arrayHandler($state, $this->recursiveArrayHandler($matchedVariables['Array']));
			} else {
				// We ask custom ExpressionNode instances from ViewHelperResolver
				// if any match our expression:
				foreach ($this->viewHelperResolver->getExpressionNodeTypes() as $expressionNodeTypeClassName) {
					$detetionExpression = $expressionNodeTypeClassName::$detectionExpression;
					$matchedVariables = array();
					if (preg_match($detetionExpression, $section, $matchedVariables) > 0) {
						$expressionNode = new $expressionNodeTypeClassName($matchedVariables[0]);
						$state->getNodeFromStack()->addChildNode($expressionNode);
						break;
					}
				}

				// As fallback we simply render the expression back as template content.
				if (!$expressionNode) {
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
	 * @param string $arrayText The array as string.
	 * @return void
	 */
	protected function arrayHandler(ParsingState $state, $arrayText) {
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
	 * @return NodeInterface[] the array node built up
	 * @throws Exception
	 */
	protected function recursiveArrayHandler($arrayText) {
		$matches = array();
		preg_match_all(Patterns::$SPLIT_PATTERN_SHORTHANDSYNTAX_ARRAY_PARTS, $arrayText, $matches, PREG_SET_ORDER);
		$arrayToBuild = array();
		foreach ($matches as $singleMatch) {
			$arrayKey = $singleMatch['Key'];
			if (!empty($singleMatch['VariableIdentifier'])) {
				$arrayToBuild[$arrayKey] = new ObjectAccessorNode($singleMatch['VariableIdentifier']);
			} elseif (array_key_exists('Number', $singleMatch) && (!empty($singleMatch['Number']) || $singleMatch['Number'] === '0' )) {
				$arrayToBuild[$arrayKey] = floatval($singleMatch['Number']);
			} elseif ((array_key_exists('QuotedString', $singleMatch) && !empty($singleMatch['QuotedString']))) {
				$argumentString = $this->unquoteString($singleMatch['QuotedString']);
				$arrayToBuild[$arrayKey] = $this->buildArgumentObjectTree($argumentString);
			} elseif (array_key_exists('Subarray', $singleMatch) && !empty($singleMatch['Subarray'])) {
				$arrayToBuild[$arrayKey] = new ArrayNode($this->recursiveArrayHandler($singleMatch['Subarray']));
			}
		}
		return $arrayToBuild;
	}

	/**
	 * Text node handler
	 *
	 * @param ParsingState $state
	 * @param string $text
	 * @return void
	 */
	protected function textHandler(ParsingState $state, $text) {
		$node = new TextNode($text);
		$this->callInterceptor($node, InterceptorInterface::INTERCEPT_TEXT, $state);
		$state->getNodeFromStack()->addChildNode($node);
	}

	/**
	 * @return ParsingState
	 */
	protected function getParsingState() {
		$rootNode = new RootNode();
		$state = new ParsingState();
		$state->setVariableProvider(new StandardVariableProvider());
		$state->setViewHelperResolver($this->viewHelperResolver);
		$state->setRootNode($rootNode);
		$state->pushNodeToStack($rootNode);
		return $state;
	}

}
