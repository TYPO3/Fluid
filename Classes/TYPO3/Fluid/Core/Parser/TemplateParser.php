<?php
namespace TYPO3\Fluid\Core\Parser;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Object\ObjectManagerInterface;
use TYPO3\Fluid\Core\Parser\SyntaxTree\AbstractNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3\Fluid\Core\ViewHelper\Facets\ChildNodeAccessInterface;
use TYPO3\Fluid\Core\ViewHelper\Facets\CompilableInterface;
use TYPO3\Fluid\Core\ViewHelper\Facets\PostParseInterface;

/**
 * Template parser building up an object syntax tree
 *
 */
class TemplateParser {

	static public $SCAN_PATTERN_NAMESPACEDECLARATION = '/(?<!\\\\){namespace\s*(?P<identifier>[a-zA-Z]+[a-zA-Z0-9]*)\s*=\s*(?P<phpNamespace>(?:[A-Za-z0-9\.]+|Tx)(?:\\\\\w+)+)\s*}/m';
	static public $SCAN_PATTERN_XMLNSDECLARATION = '/\sxmlns:(?P<identifier>.*?)="(?P<xmlNamespace>.*?)"/m';

	/**
	 * The following two constants are used for tracking whether we are currently
	 * parsing ViewHelper arguments or not. This is used to parse arrays only as
	 * ViewHelper argument.
	 */
	const CONTEXT_INSIDE_VIEWHELPER_ARGUMENTS = 1;
	const CONTEXT_OUTSIDE_VIEWHELPER_ARGUMENTS = 2;

	/**
	 * This regular expression splits the input string at all dynamic tags, AND
	 * on all <![CDATA[...]]> sections.
	 *
	 */
	static public $SPLIT_PATTERN_TEMPLATE_DYNAMICTAGS = '/
		(
			(?: <\/?                                      # Start dynamic tags
					(?:(?:NAMESPACE):[a-zA-Z0-9\\.]+)     # A tag consists of the namespace prefix and word characters
					(?:                                   # Begin tag arguments
						\s*[a-zA-Z0-9:-]+                  # Argument Keys
						=                                 # =
						(?>                               # either... If we have found an argument, we will not back-track (That does the Atomic Bracket)
							"(?:\\\"|[^"])*"              # a double-quoted string
							|\'(?:\\\\\'|[^\'])*\'        # or a single quoted string
						)\s*                              #
					)*                                    # Tag arguments can be replaced many times.
				\s*
				\/?>                                      # Closing tag
			)
			|(?:                                          # Start match CDATA section
				<!\[CDATA\[.*?\]\]>
			)
		)/xs';

	/**
	 * This regular expression scans if the input string is a ViewHelper tag
	 *
	 */
	static public $SCAN_PATTERN_TEMPLATE_VIEWHELPERTAG = '/
		^<                                                # A Tag begins with <
		(?P<NamespaceIdentifier>NAMESPACE):               # Then comes the Namespace prefix followed by a :
		(?P<MethodIdentifier>                             # Now comes the Name of the ViewHelper
			[a-zA-Z0-9\\.]+
		)
		(?P<Attributes>                                   # Begin Tag Attributes
			(?:                                           # A tag might have multiple attributes
				\s*
				[a-zA-Z0-9:-]+                             # The attribute name
				=                                         # =
				(?>                                       # either... # If we have found an argument, we will not back-track (That does the Atomic Bracket)
					"(?:\\\"|[^"])*"                      # a double-quoted string
					|\'(?:\\\\\'|[^\'])*\'                # or a single quoted string
				)                                         #
				\s*
			)*                                            # A tag might have multiple attributes
		)                                                 # End Tag Attributes
		\s*
		(?P<Selfclosing>\/?)                              # A tag might be selfclosing
		>$/x';

	/**
	 * This regular expression scans if the input string is a closing ViewHelper
	 * tag.
	 *
	 */
	static public $SCAN_PATTERN_TEMPLATE_CLOSINGVIEWHELPERTAG = '/^<\/(?P<NamespaceIdentifier>NAMESPACE):(?P<MethodIdentifier>[a-zA-Z0-9\\.]+)\s*>$/';

	/**
	 * This regular expression splits the tag arguments into its parts
	 *
	 */
	static public $SPLIT_PATTERN_TAGARGUMENTS = '/
		(?:                                              #
			\s*                                          #
			(?P<Argument>                                # The attribute name
				[a-zA-Z0-9:-]+                            #
			)                                            #
			=                                            # =
			(?>                                          # If we have found an argument, we will not back-track (That does the Atomic Bracket)
				(?P<ValueQuoted>                         # either...
					(?:"(?:\\\"|[^"])*")                 # a double-quoted string
					|(?:\'(?:\\\\\'|[^\'])*\')           # or a single quoted string
				)
			)\s*
		)
		/xs';

	/**
	 * This pattern detects CDATA sections and outputs the text between opening
	 * and closing CDATA.
	 *
	 */
	static public $SCAN_PATTERN_CDATA = '/^<!\[CDATA\[(.*?)\]\]>$/s';

	/**
	 * Pattern which splits the shorthand syntax into different tokens. The
	 * "shorthand syntax" is everything like {...}
	 *
	 */
	static public $SPLIT_PATTERN_SHORTHANDSYNTAX = '/
		(
			{                                # Start of shorthand syntax
				(?:                          # Shorthand syntax is either composed of...
					[a-zA-Z0-9\->_:,.()]     # Various characters
					|"(?:\\\"|[^"])*"        # Double-quoted strings
					|\'(?:\\\\\'|[^\'])*\'   # Single-quoted strings
					|(?R)                    # Other shorthand syntaxes inside, albeit not in a quoted string
					|\s+                     # Spaces
				)+
			}                                # End of shorthand syntax
		)/x';

	/**
	 * Pattern which detects the object accessor syntax:
	 * {object.some.value}, additionally it detects ViewHelpers like
	 * {f:for(param1:bla)} and chaining like
	 * {object.some.value->f:bla.blubb()->f:bla.blubb2()}
	 *
	 * THIS IS ALMOST THE SAME AS IN $SCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS
	 *
	 */
	static public $SCAN_PATTERN_SHORTHANDSYNTAX_OBJECTACCESSORS = '/
		^{                                                      # Start of shorthand syntax
			                                                # A shorthand syntax is either...
			(?P<Object>[a-zA-Z0-9\-_.]*)                                     # ... an object accessor
			\s*(?P<Delimiter>(?:->)?)\s*

			(?P<ViewHelper>                                 # ... a ViewHelper
				[a-zA-Z0-9]+                                # Namespace prefix of ViewHelper (as in $SCAN_PATTERN_TEMPLATE_VIEWHELPERTAG)
				:
				[a-zA-Z0-9\\.]+                             # Method Identifier (as in $SCAN_PATTERN_TEMPLATE_VIEWHELPERTAG)
				\(                                          # Opening parameter brackets of ViewHelper
					(?P<ViewHelperArguments>                # Start submatch for ViewHelper arguments. This is taken from $SCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS
						(?:
							\s*[a-zA-Z0-9\-_]+                  # The keys of the array
							\s*:\s*                             # Key|Value delimiter :
							(?:                                 # Possible value options:
								"(?:\\\"|[^"])*"                # Double qouoted string
								|\'(?:\\\\\'|[^\'])*\'          # Single quoted string
								|[a-zA-Z0-9\-_.]+               # variable identifiers
								|{(?P>ViewHelperArguments)}     # Another sub-array
							)                                   # END possible value options
							\s*,?                               # There might be a , to seperate different parts of the array
						)*                                  # The above cycle is repeated for all array elements
					)                                       # End ViewHelper Arguments submatch
				\)                                          # Closing parameter brackets of ViewHelper
			)?
			(?P<AdditionalViewHelpers>                      # There can be more than one ViewHelper chained, by adding more -> and the ViewHelper (recursively)
				(?:
					\s*->\s*
					(?P>ViewHelper)
				)*
			)
		}$/x';

	/**
	 * THIS IS ALMOST THE SAME AS $SCAN_PATTERN_SHORTHANDSYNTAX_OBJECTACCESSORS
	 *
	 */
	static public $SPLIT_PATTERN_SHORTHANDSYNTAX_VIEWHELPER = '/

		(?P<NamespaceIdentifier>[a-zA-Z0-9]+)       # Namespace prefix of ViewHelper (as in $SCAN_PATTERN_TEMPLATE_VIEWHELPERTAG)
		:
		(?P<MethodIdentifier>[a-zA-Z0-9\\.]+)
		\(                                          # Opening parameter brackets of ViewHelper
			(?P<ViewHelperArguments>                # Start submatch for ViewHelper arguments. This is taken from $SCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS
				(?:
					\s*[a-zA-Z0-9\-_]+                  # The keys of the array
					\s*:\s*                             # Key|Value delimiter :
					(?:                                 # Possible value options:
						"(?:\\\"|[^"])*"                # Double qouoted string
						|\'(?:\\\\\'|[^\'])*\'          # Single quoted string
						|[a-zA-Z0-9\-_.]+               # variable identifiers
						|{(?P>ViewHelperArguments)}     # Another sub-array
					)                                   # END possible value options
					\s*,?                               # There might be a , to seperate different parts of the array
				)*                                  # The above cycle is repeated for all array elements
			)                                       # End ViewHelper Arguments submatch
		\)                                          # Closing parameter brackets of ViewHelper
		/x';

	/**
	 * Pattern which detects the array/object syntax like in JavaScript, so it
	 * detects strings like:
	 * {object: value, object2: {nested: array}, object3: "Some string"}
	 *
	 * THIS IS ALMOST THE SAME AS IN SCAN_PATTERN_SHORTHANDSYNTAX_OBJECTACCESSORS
	 *
	 */
	static public $SCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS = '/^
		(?P<Recursion>                                  # Start the recursive part of the regular expression - describing the array syntax
			{                                           # Each array needs to start with {
				(?P<Array>                              # Start sub-match
					(?:
						\s*[a-zA-Z0-9\-_]+              # The keys of the array
						\s*:\s*                         # Key|Value delimiter :
						(?:                             # Possible value options:
							"(?:\\\"|[^"])*"            # Double quoted string
							|\'(?:\\\\\'|[^\'])*\'      # Single quoted string
							|[a-zA-Z0-9\-_.]+           # variable identifiers
							|(?P>Recursion)             # Another sub-array
						)                               # END possible value options
						\s*,?                           # There might be a , to separate different parts of the array
					)*                                  # The above cycle is repeated for all array elements
				)                                       # End array sub-match
			}                                           # Each array ends with }
		)$/x';

	/**
	 * This pattern splits an array into its parts. It is quite similar to the
	 * pattern above.
	 *
	 */
	static public $SPLIT_PATTERN_SHORTHANDSYNTAX_ARRAY_PARTS = '/
		(?P<ArrayPart>                                             # Start sub-match
			(?P<Key>[a-zA-Z0-9\-_]+)                               # The keys of the array
			\s*:\s*                                                   # Key|Value delimiter :
			(?:                                                       # Possible value options:
				(?P<QuotedString>                                     # Quoted string
					(?:"(?:\\\"|[^"])*")
					|(?:\'(?:\\\\\'|[^\'])*\')
				)
				|(?P<VariableIdentifier>[a-zA-Z][a-zA-Z0-9\-_.]*)    # variable identifiers have to start with a letter
				|(?P<Number>[0-9.]+)                                  # Number
				|{\s*(?P<Subarray>(?:(?P>ArrayPart)\s*,?\s*)+)\s*}              # Another sub-array
			)                                                         # END possible value options
		)                                                          # End array part sub-match
	/x';

	/**
	 * This pattern detects the default xml namespace
	 *
	 */
	static public $SCAN_PATTERN_DEFAULT_XML_NAMESPACE = '/^http\:\/\/typo3\.org\/ns\/(?P<PhpNamespace>.+)$/s';

	/**
	 * Namespace identifiers and their component name prefix (Associative array).
	 * @var array
	 */
	protected $namespaces = array(
		'f' => 'TYPO3\Fluid\ViewHelpers'
	);

	/**
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var Configuration
	 */
	protected $configuration;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * Injects Fluid settings
	 *
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * Inject object factory
	 *
	 * @param ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
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
	 * @return ParsingState Parsed template
	 * @throws Exception
	 */
	public function parse($templateString) {
		if (!is_string($templateString)) {
			throw new Exception('Parse requires a template string as argument, ' . gettype($templateString) . ' given.', 1224237899);
		}

		$this->reset();

		$templateString = $this->extractNamespaceDefinitions($templateString);
		$splitTemplate = $this->splitTemplateAtDynamicTags($templateString);
		$parsingState = $this->buildObjectTree($splitTemplate, self::CONTEXT_OUTSIDE_VIEWHELPER_ARGUMENTS);

		$variableContainer = $parsingState->getVariableContainer();
		if ($variableContainer !== NULL && $variableContainer->exists('layoutName')) {
			$parsingState->setLayoutNameNode($variableContainer->get('layoutName'));
		}

		return $parsingState;
	}

	/**
	 * Gets the namespace definitions found.
	 *
	 * @return array Namespace identifiers and their component name prefix
	 */
	public function getNamespaces() {
		return $this->namespaces;
	}

	/**
	 * Resets the parser to its default values.
	 *
	 * @return void
	 */
	protected function reset() {
		$this->namespaces = array(
			'f' => 'TYPO3\Fluid\ViewHelpers'
		);
	}

	/**
	 * Extracts namespace definitions out of the given template string and sets
	 * $this->namespaces.
	 *
	 * @param string $templateString Template string to extract the namespaces from
	 * @return string The updated template string without namespace declarations inside
	 * @throws Exception if a namespace can't be resolved or has been declared already
	 */
	protected function extractNamespaceDefinitions($templateString) {
		$matches = array();
		preg_match_all(self::$SCAN_PATTERN_XMLNSDECLARATION, $templateString, $matches, PREG_SET_ORDER);
		foreach ($matches as $match) {
			// skip reserved "f" namespace identifier
			if ($match['identifier'] === 'f') {
				continue;
			}
			if (array_key_exists($match['identifier'], $this->namespaces)) {
				throw new Exception(sprintf('Namespace identifier "%s" is already registered. Do not re-declare namespaces!', $match['identifier']), 1331135889);
			}
			if (isset($this->settings['namespaces'][$match['xmlNamespace']])) {
				$phpNamespace = $this->settings['namespaces'][$match['xmlNamespace']];
			} else {
				$matchedPhpNamespace = array();
				if (preg_match(self::$SCAN_PATTERN_DEFAULT_XML_NAMESPACE, $match['xmlNamespace'], $matchedPhpNamespace) === 0) {
					continue;
				}
				$phpNamespace = str_replace('/', '\\', $matchedPhpNamespace['PhpNamespace']);
			}
			$this->namespaces[$match['identifier']] = $phpNamespace;
		}
		$matches = array();
		preg_match_all(self::$SCAN_PATTERN_NAMESPACEDECLARATION, $templateString, $matches, PREG_SET_ORDER);
		foreach ($matches as $match) {
			if (array_key_exists($match['identifier'], $this->namespaces)) {
				throw new Exception(sprintf('Namespace identifier "%s" is already registered. Do not re-declare namespaces!', $match['identifier']), 1224241246);
			}
			$this->namespaces[$match['identifier']] = $match['phpNamespace'];
		}
		if ($matches !== array()) {
			$templateString = preg_replace(self::$SCAN_PATTERN_NAMESPACEDECLARATION, '', $templateString);
		}

		return $templateString;
	}

	/**
	 * Splits the template string on all dynamic tags found.
	 *
	 * @param string $templateString Template string to split.
	 * @return array Splitted template
	 */
	protected function splitTemplateAtDynamicTags($templateString) {
		$regularExpression = $this->prepareTemplateRegularExpression(self::$SPLIT_PATTERN_TEMPLATE_DYNAMICTAGS);
		return preg_split($regularExpression, $templateString, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
	}

	/**
	 * Build object tree from the split template
	 *
	 * @param array $splitTemplate The split template, so that every tag with a namespace declaration is already a seperate array element.
	 * @param integer $context one of the CONTEXT_* constants, defining whether we are inside or outside of ViewHelper arguments currently.
	 * @return ParsingState
	 * @throws Exception
	 */
	protected function buildObjectTree($splitTemplate, $context) {
		$regularExpression_openingViewHelperTag = $this->prepareTemplateRegularExpression(self::$SCAN_PATTERN_TEMPLATE_VIEWHELPERTAG);
		$regularExpression_closingViewHelperTag = $this->prepareTemplateRegularExpression(self::$SCAN_PATTERN_TEMPLATE_CLOSINGVIEWHELPERTAG);

		/** @var $state ParsingState */
		$state = $this->objectManager->get('TYPO3\Fluid\Core\Parser\ParsingState');
		/** @var $rootNode RootNode */
		$rootNode = $this->objectManager->get('TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode');
		$state->setRootNode($rootNode);
		$state->pushNodeToStack($rootNode);

		foreach ($splitTemplate as $templateElement) {
			$matchedVariables = array();
			if (preg_match(self::$SCAN_PATTERN_CDATA, $templateElement, $matchedVariables) > 0) {
				$this->textHandler($state, $matchedVariables[1]);
			} elseif (preg_match($regularExpression_openingViewHelperTag, $templateElement, $matchedVariables) > 0) {
				$this->openingViewHelperTagHandler($state, $matchedVariables['NamespaceIdentifier'], $matchedVariables['MethodIdentifier'], $matchedVariables['Attributes'], ($matchedVariables['Selfclosing'] === '' ? FALSE : TRUE));
			} elseif (preg_match($regularExpression_closingViewHelperTag, $templateElement, $matchedVariables) > 0) {
				$this->closingViewHelperTagHandler($state, $matchedVariables['NamespaceIdentifier'], $matchedVariables['MethodIdentifier']);
			} else {
				$this->textAndShorthandSyntaxHandler($state, $templateElement, $context);
			}
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
	 * @return void
	 */
	protected function openingViewHelperTagHandler(ParsingState $state, $namespaceIdentifier, $methodIdentifier, $arguments, $selfclosing) {
		$argumentsObjectTree = $this->parseArguments($arguments);
		$this->initializeViewHelperAndAddItToStack($state, $namespaceIdentifier, $methodIdentifier, $argumentsObjectTree);

		if ($selfclosing) {
			$node = $state->popNodeFromStack();
			$this->callInterceptor($node, InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER, $state);
		}
	}

	/**
	 * Initialize the given ViewHelper and adds it to the current node and to
	 * the stack.
	 *
	 * @param ParsingState $state Current parsing state
	 * @param string $namespaceIdentifier Namespace identifier - being looked up in $this->namespaces
	 * @param string $methodIdentifier Method identifier
	 * @param array $argumentsObjectTree Arguments object tree
	 * @return void
	 * @throws Exception
	 */
	protected function initializeViewHelperAndAddItToStack(ParsingState $state, $namespaceIdentifier, $methodIdentifier, $argumentsObjectTree) {
		if (!array_key_exists($namespaceIdentifier, $this->namespaces)) {
			throw new Exception('Namespace could not be resolved. This exception should never be thrown!', 1224254792);
		}
		$resolvedViewHelperClassName = $this->resolveViewHelperName($namespaceIdentifier, $methodIdentifier);
		$actualViewHelperClassName = $this->objectManager->getCaseSensitiveObjectName($resolvedViewHelperClassName);
		if ($actualViewHelperClassName === FALSE) {
			throw new Exception(sprintf(
				'The ViewHelper "<%s:%s>" could not be resolved.' . chr(10) .
				'Based on your spelling, the system would load the class "%s", however this class does not exist.',
				$namespaceIdentifier, $methodIdentifier, $resolvedViewHelperClassName), 1407060572);
		} elseif ($actualViewHelperClassName !== $resolvedViewHelperClassName) {
			throw new Exception(sprintf(
				'The ViewHelper "<%s:%s>" inside your template is not written correctly upper/lowercased.' . chr(10) .
				'Based on your spelling, the system would load the (non-existant) class "%s", however the real class name is "%s".' . chr(10) .
				'This error can be fixed by making sure the ViewHelper is written in the correct upper/lowercase form.',
				$namespaceIdentifier, $methodIdentifier, $resolvedViewHelperClassName, $actualViewHelperClassName), 1407060573);
		}
		$viewHelper = $this->objectManager->get($actualViewHelperClassName);

		// The following three checks are only done *in an uncached template*, and not needed anymore in the cached version
		$expectedViewHelperArguments = $viewHelper->prepareArguments();
		$this->abortIfUnregisteredArgumentsExist($expectedViewHelperArguments, $argumentsObjectTree);
		$this->abortIfRequiredArgumentsAreMissing($expectedViewHelperArguments, $argumentsObjectTree);
		$this->rewriteBooleanNodesInArgumentsObjectTree($expectedViewHelperArguments, $argumentsObjectTree);

		/** @var $currentViewHelperNode ViewHelperNode */
		$currentViewHelperNode = $this->objectManager->get('TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', $viewHelper, $argumentsObjectTree);
		$state->getNodeFromStack()->addChildNode($currentViewHelperNode);

		if ($viewHelper instanceof ChildNodeAccessInterface && !($viewHelper instanceof CompilableInterface)) {
			$state->setCompilable(FALSE);
		}

		// PostParse Facet
		if ($viewHelper instanceof PostParseInterface) {
			// Don't just use $viewHelper::postParseEvent(...),
			// as this will break with PHP < 5.3.
			call_user_func(array($viewHelper, 'postParseEvent'), $currentViewHelperNode, $argumentsObjectTree, $state->getVariableContainer());
		}

		$this->callInterceptor($currentViewHelperNode, InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER, $state);

		$state->pushNodeToStack($currentViewHelperNode);
	}

	/**
	 * Throw an exception if there are arguments which were not registered
	 * before.
	 *
	 * @param array $expectedArguments Array of \TYPO3\Fluid\Core\ViewHelper\ArgumentDefinition of all expected arguments
	 * @param array $actualArguments Actual arguments
	 * @throws Exception
	 */
	protected function abortIfUnregisteredArgumentsExist($expectedArguments, $actualArguments) {
		$expectedArgumentNames = array();
		/** @var $expectedArgument ArgumentDefinition */
		foreach ($expectedArguments as $expectedArgument) {
			$expectedArgumentNames[] = $expectedArgument->getName();
		}

		foreach (array_keys($actualArguments) as $argumentName) {
			if (!in_array($argumentName, $expectedArgumentNames)) {
				throw new Exception('Argument "' . $argumentName . '" was not registered.', 1237823695);
			}
		}
	}

	/**
	 * Throw an exception if required arguments are missing
	 *
	 * @param array $expectedArguments Array of \TYPO3\Fluid\Core\ViewHelper\ArgumentDefinition of all expected arguments
	 * @param array $actualArguments Actual arguments
	 * @throws Exception
	 */
	protected function abortIfRequiredArgumentsAreMissing($expectedArguments, $actualArguments) {
		$actualArgumentNames = array_keys($actualArguments);
		/** @var $expectedArgument ArgumentDefinition */
		foreach ($expectedArguments as $expectedArgument) {
			if ($expectedArgument->isRequired() && !in_array($expectedArgument->getName(), $actualArgumentNames)) {
				throw new Exception('Required argument "' . $expectedArgument->getName() . '" was not supplied.', 1237823699);
			}
		}
	}

	/**
	 * Wraps the argument tree, if a node is boolean, into a Boolean syntax tree node
	 *
	 * @param array $argumentDefinitions the argument definitions, key is the argument name, value is the ArgumentDefinition object
	 * @param array $argumentsObjectTree the arguments syntax tree, key is the argument name, value is an AbstractNode
	 * @return void
	 */
	protected function rewriteBooleanNodesInArgumentsObjectTree($argumentDefinitions, &$argumentsObjectTree) {
		/** @var $argumentDefinition ArgumentDefinition */
		foreach ($argumentDefinitions as $argumentName => $argumentDefinition) {
			if ($argumentDefinition->getType() === 'boolean' && isset($argumentsObjectTree[$argumentName])) {
				$argumentsObjectTree[$argumentName] = new BooleanNode($argumentsObjectTree[$argumentName]);
			}
		}
	}

	/**
	 * Resolve a viewhelper name.
	 *
	 * @param string $namespaceIdentifier Namespace identifier for the view helper.
	 * @param string $methodIdentifier Method identifier, might be hierarchical like "link.url"
	 * @return string The fully qualified class name of the viewhelper
	 */
	protected function resolveViewHelperName($namespaceIdentifier, $methodIdentifier) {
		$explodedViewHelperName = explode('.', $methodIdentifier);
		if (count($explodedViewHelperName) > 1) {
			$className = implode('\\', array_map('ucfirst', $explodedViewHelperName));
		} else {
			$className = ucfirst($explodedViewHelperName[0]);
		}
		$className .= 'ViewHelper';

		$name = $this->namespaces[$namespaceIdentifier] . '\\' . $className;

		return $name;
	}

	/**
	 * Handles a closing view helper tag
	 *
	 * @param ParsingState $state The current parsing state
	 * @param string $namespaceIdentifier Namespace identifier for the closing tag.
	 * @param string $methodIdentifier Method identifier.
	 * @return void
	 * @throws Exception
	 */
	protected function closingViewHelperTagHandler(ParsingState $state, $namespaceIdentifier, $methodIdentifier) {
		if (!array_key_exists($namespaceIdentifier, $this->namespaces)) {
			throw new Exception('Namespace could not be resolved. This exception should never be thrown!', 1224256186);
		}
		$lastStackElement = $state->popNodeFromStack();
		if (!($lastStackElement instanceof ViewHelperNode)) {
			throw new Exception('You closed a templating tag which you never opened!', 1224485838);
		}
		if ($lastStackElement->getViewHelperClassName() != $this->resolveViewHelperName($namespaceIdentifier, $methodIdentifier)) {
			throw new Exception('Templating tags not properly nested. Expected: ' . $lastStackElement->getViewHelperClassName() . '; Actual: ' . $this->resolveViewHelperName($namespaceIdentifier, $methodIdentifier), 1224485398);
		}
		$this->callInterceptor($lastStackElement, InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER, $state);
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
		if (strlen($viewHelperString) > 0 && preg_match_all(self::$SPLIT_PATTERN_SHORTHANDSYNTAX_VIEWHELPER, $viewHelperString, $matches, PREG_SET_ORDER) > 0) {
			// The last ViewHelper has to be added first for correct chaining.
			foreach (array_reverse($matches) as $singleMatch) {
				if (strlen($singleMatch['ViewHelperArguments']) > 0) {
					$arguments = $this->postProcessArgumentsForObjectAccessor($this->recursiveArrayHandler($singleMatch['ViewHelperArguments']));
				} else {
					$arguments = array();
				}
				$this->initializeViewHelperAndAddItToStack($state, $singleMatch['NamespaceIdentifier'], $singleMatch['MethodIdentifier'], $arguments);
				$numberOfViewHelpers++;
			}
		}

		// Object Accessor
		if (strlen($objectAccessorString) > 0) {

			/** @var $node ObjectAccessorNode */
			$node = $this->objectManager->get('TYPO3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode', $objectAccessorString);
			$this->callInterceptor($node, InterceptorInterface::INTERCEPT_OBJECTACCESSOR, $state);

			$state->getNodeFromStack()->addChildNode($node);
		}

		// Close ViewHelper Tags if needed.
		for ($i = 0; $i < $numberOfViewHelpers; $i++) {
			$node = $state->popNodeFromStack();
			$this->callInterceptor($node, InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER, $state);
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
		if ($this->configuration !== NULL) {
			// $this->configuration is UNSET inside the arguments of a ViewHelper.
			// That's why the interceptors are only called if the object accesor is not inside a ViewHelper Argument
			// This could be a problem if We have a ViewHelper as an argument to another ViewHelper, and an ObjectAccessor nested inside there.
			// TODO: Clean up this.
			$interceptors = $this->configuration->getInterceptors($interceptionPoint);
			if (count($interceptors) > 0) {
				/** @var $interceptor InterceptorInterface */
				foreach ($interceptors as $interceptor) {
					$node = $interceptor->process($node, $interceptionPoint, $state);
				}
			}
		}
	}

	/**
	 * Post process the arguments for the ViewHelpers in the object accessor
	 * syntax. We need to convert an array into an array of (only) nodes
	 *
	 * @param array $arguments The arguments to be processed
	 * @return array the processed array
	 * @todo This method should become superflous once the rest has been refactored, so that this code is not needed.
	 */
	protected function postProcessArgumentsForObjectAccessor(array $arguments) {
		foreach ($arguments as $argumentName => $argumentValue) {
			if (!($argumentValue instanceof AbstractNode)) {
				$arguments[$argumentName] = $this->objectManager->get('TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode', (string)$argumentValue);
			}
		}
		return $arguments;
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
		if (preg_match_all(self::$SPLIT_PATTERN_TAGARGUMENTS, $argumentsString, $matches, PREG_SET_ORDER) > 0) {
			$configurationBackup = $this->configuration;
			$this->configuration = NULL;
			foreach ($matches as $singleMatch) {
				$argument = $singleMatch['Argument'];
				$value = $this->unquoteString($singleMatch['ValueQuoted']);
				$argumentsObjectTree[$argument] = $this->buildArgumentObjectTree($value);
			}
			$this->configuration = $configurationBackup;
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
	 * @return SyntaxTree\AbstractNode the corresponding argument object tree.
	 */
	protected function buildArgumentObjectTree($argumentString) {
		if (strpos($argumentString, '{') === FALSE && strpos($argumentString, '<') === FALSE) {
			return $this->objectManager->get('TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode', $argumentString);
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
		switch ($quotedValue[0]) {
			case '"':
				$value = str_replace('\\"', '"', preg_replace('/(^"|"$)/', '', $quotedValue));
			break;
			case "'":
				$value = str_replace("\\'", "'", preg_replace('/(^\'|\'$)/', '', $quotedValue));
			break;
			default:
				$value = $quotedValue;
		}
		return str_replace('\\\\', '\\', $value);
	}

	/**
	 * Takes a regular expression template and replaces "NAMESPACE" with the
	 * currently registered namespace identifiers. Returns a regular expression
	 * which is ready to use.
	 *
	 * @param string $regularExpression Regular expression template
	 * @return string Regular expression ready to be used
	 */
	protected function prepareTemplateRegularExpression($regularExpression) {
		return str_replace('NAMESPACE', implode('|', array_keys($this->namespaces)), $regularExpression);
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
		$sections = preg_split($this->prepareTemplateRegularExpression(self::$SPLIT_PATTERN_SHORTHANDSYNTAX), $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

		foreach ($sections as $section) {
			$matchedVariables = array();
			if (preg_match(self::$SCAN_PATTERN_SHORTHANDSYNTAX_OBJECTACCESSORS, $section, $matchedVariables) > 0) {
				$this->objectAccessorHandler($state, $matchedVariables['Object'], $matchedVariables['Delimiter'], (isset($matchedVariables['ViewHelper']) ? $matchedVariables['ViewHelper'] : ''), (isset($matchedVariables['AdditionalViewHelpers']) ? $matchedVariables['AdditionalViewHelpers'] : ''));
			} elseif ($context === self::CONTEXT_INSIDE_VIEWHELPER_ARGUMENTS && preg_match(self::$SCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS, $section, $matchedVariables) > 0) {
				// We only match arrays if we are INSIDE viewhelper arguments
				$this->arrayHandler($state, $matchedVariables['Array']);
			} else {
				$this->textHandler($state, $section);
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
		/** @var $arrayNode ArrayNode */
		$arrayNode = $this->objectManager->get('TYPO3\Fluid\Core\Parser\SyntaxTree\ArrayNode', $this->recursiveArrayHandler($arrayText));
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
	 * @return array<NodeInterface> the array node built up
	 * @throws Exception
	 */
	protected function recursiveArrayHandler($arrayText) {
		$matches = array();
		if (preg_match_all(self::$SPLIT_PATTERN_SHORTHANDSYNTAX_ARRAY_PARTS, $arrayText, $matches, PREG_SET_ORDER) > 0) {
			$arrayToBuild = array();
			foreach ($matches as $singleMatch) {
				$arrayKey = $singleMatch['Key'];
				if (!empty($singleMatch['VariableIdentifier'])) {
					$arrayToBuild[$arrayKey] = $this->objectManager->get('TYPO3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode', $singleMatch['VariableIdentifier']);
				} elseif (array_key_exists('Number', $singleMatch) && ( !empty($singleMatch['Number']) || $singleMatch['Number'] === '0' ) ) {
					$arrayToBuild[$arrayKey] = floatval($singleMatch['Number']);
				} elseif ( ( array_key_exists('QuotedString', $singleMatch) && !empty($singleMatch['QuotedString']) ) ) {
					$argumentString = $this->unquoteString($singleMatch['QuotedString']);
					$arrayToBuild[$arrayKey] = $this->buildArgumentObjectTree($argumentString);
				} elseif ( array_key_exists('Subarray', $singleMatch) && !empty($singleMatch['Subarray'])) {
					$arrayToBuild[$arrayKey] = $this->objectManager->get('TYPO3\Fluid\Core\Parser\SyntaxTree\ArrayNode', $this->recursiveArrayHandler($singleMatch['Subarray']));
				} else {
					throw new Exception('This exception should never be thrown, as the array value has to be of some type (Value given: "' . var_export($singleMatch, TRUE) . '"). Please post your template to the bugtracker at forge.typo3.org.', 1225136013);
				}
			}
			return $arrayToBuild;
		} else {
			throw new Exception('This exception should never be thrown, there is most likely some error in the regular expressions. Please post your template to the bugtracker at forge.typo3.org.', 1225136013);
		}
	}

	/**
	 * Text node handler
	 *
	 * @param ParsingState $state
	 * @param string $text
	 * @return void
	 */
	protected function textHandler(ParsingState $state, $text) {
		/** @var $node TextNode */
		$node = $this->objectManager->get('TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode', $text);
		$this->callInterceptor($node, InterceptorInterface::INTERCEPT_TEXT, $state);

		$state->getNodeFromStack()->addChildNode($node);
	}
}
