<?php
declare(ENCODING = 'utf-8');
namespace F3::Beer3;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 * @package Beer3
 * @version $Id:$
 */
/**
 * Template parser building up an object syntax tree
 *
 * @package Beer3
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class TemplateParser {
	const SCAN_PATTERN_NAMESPACEDECLARATION = '/(?:^|[^\\\\]+){namespace\s*([a-zA-Z]+[a-zA-Z0-9]*)\s*=\s*(F3::(?:\w+|::)+)\s*}/m';
	const SPLIT_PATTERN_TEMPLATE_DYNAMICTAGS = '/(<\/?(?:(?:NAMESPACE):[a-zA-Z0-9\\.]+)(?:\s*[a-zA-Z0-9:]+=(?:"(?:\\\"|[^"])*"|\'(?:\\\\\'|[^\'])*\')\s*)*\s*\/?>)/';
	const SCAN_PATTERN_TEMPLATE_DYNAMICTAG = '/^<(?P<NamespaceIdentifier>NAMESPACE):(?P<MethodIdentifier>[a-zA-Z0-9\\.]+)(?P<Attributes>(?:\s*[a-zA-Z0-9:]+=(?:"(?:\\\"|[^"])*"|\'(?:\\\\\'|[^\'])*\')\s*)*)\s*(?P<Selfclosing>\/?)>$/';
	const SCAN_PATTERN_TEMPLATE_CLOSINGDYNAMICTAG = '/^<\/(?P<NamespaceIdentifier>NAMESPACE):(?P<MethodIdentifier>[a-zA-Z0-9\\.]+)\s*>$/';
	const SPLIT_PATTERN_TAGARGUMENTS = '/(?:\s*(?P<Argument>[a-zA-Z0-9:]+)=(?:"(?P<ValueDoubleQuoted>(?:\\\"|[^"])*)"|\'(?P<ValueSingleQuoted>(?:\\\\\'|[^\'])*)\')\s*)/';
	
	const SPLIT_PATTERN_SHORTHANDSYNTAX = '/(\\\\?{[^}]+})/';
	const SCAN_PATTERN_SHORTHANDSYNTAX_OBJECTACCESSORS = '/(?:^|[^\\\\]+){(?P<Object>[a-zA-Z0-9\-_.]+)}/';
	
	/**
	 * Namespace identifiers and their component name prefix (Associative array).
	 * @var array
	 */
	protected $namespaces = array();
	
	/**
	 * @var F3::FLOW3::Component::FactoryInterface
	 */
	protected $componentFactory;
	
	/**
	 * Inject component factory
	 *
	 * @param F3::FLOW3::Component::FactoryInterface $componentFactory
	 */
	public function injectComponentFactory(F3::FLOW3::Component::FactoryInterface $componentFactory) {
		$this->componentFactory = $componentFactory;
	}
	
	/**
	 * Parses a given template and returns an object tree, identified by a root node
	 *
	 * @param string $templateString
	 * @return TreeNode the root node
	 * @todo Refine doc comment
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function parse($templateString) {
		if (!is_string($templateString)) throw new F3::Beer3::ParsingException('Parse requires a template string as argument, ' . gettype($templateString) . ' given.', 1224237899);
		
		$this->initialize();
		
		$templateString = $this->extractNamespaceDefinitions($templateString);
		$splittedTemplate = $this->splitTemplateAtDynamicTags($templateString);
		$rootNode = $this->buildMainObjectTree($splittedTemplate);
		
		return $rootNode;
	}
	
	/**
	 * Gets the namespace definitions found.
	 *
	 * @return array Namespace identifiers and their component name prefix
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getNamespaces() {
		return $this->namespaces;
	}
	
	/**
	 * Resets the parser to its default values.
	 * 
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function initialize() {
		$this->namespaces = array();
	}
	
	/**
	 * Extracts given namespace definitions and sets $this->namespaces.
	 *
	 * @param string $templateString Template string to extract the namespaces from
	 * @return string The updated template string
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function extractNamespaceDefinitions($templateString) {
		if (preg_match_all(self::SCAN_PATTERN_NAMESPACEDECLARATION, $templateString, $matchedVariables) > 0) {
			foreach ($matchedVariables[0] as $index => $tmp) {
				$namespaceIdentifier = $matchedVariables[1][$index];
				$fullyQualifiedNamespace = $matchedVariables[2][$index];
				if (key_exists($namespaceIdentifier, $this->namespaces)) {
					throw new F3::Beer3::ParsingException('Namespace identifier "' . $namespaceIdentifier . '" is already registered. Do not redeclare namespaces!', 1224241246);
				}
				$this->namespaces[$namespaceIdentifier] = $fullyQualifiedNamespace;
			}
			
			$templateString = preg_replace(self::SCAN_PATTERN_NAMESPACEDECLARATION, '', $templateString);
		}
		return $templateString;
	}
	
	/**
	 * Splits the template string on all dynamic tags found.
	 * 
	 * @param string $templateString Template string to split.
	 * @return array Splitted template
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function splitTemplateAtDynamicTags($templateString) {
		$regularExpression = $this->prepareTemplateRegularExpression(self::SPLIT_PATTERN_TEMPLATE_DYNAMICTAGS);
		return preg_split($regularExpression, $templateString, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
	}
	
	/**
	 * Build object tree from the splitted template
	 *
	 * @param array $splittedTemplate The splitted template, so that every tag with a namespace declaration is already a seperate array element.
	 * @return TreeNode the main tree node.
	 * @todo Handle return values?
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function buildMainObjectTree($splittedTemplate) {
		$regularExpression_dynamicTag = $this->prepareTemplateRegularExpression(self::SCAN_PATTERN_TEMPLATE_DYNAMICTAG);
		$regularExpression_closingDynamicTag = $this->prepareTemplateRegularExpression(self::SCAN_PATTERN_TEMPLATE_CLOSINGDYNAMICTAG);
		
		$state = $this->componentFactory->getComponent('F3::Beer3::ParsingState');
		$rootNode = $this->componentFactory->getComponent('F3::Beer3::RootNode');
		$state->setRootNode($rootNode);
		$state->pushNodeToStack($rootNode);
		
		foreach ($splittedTemplate as $templateElement) {
			if (preg_match($regularExpression_dynamicTag, $templateElement, $matchedVariables) > 0) {
				$namespaceIdentifier = $matchedVariables['NamespaceIdentifier'];
				$methodIdentifier = $matchedVariables['MethodIdentifier'];
				$selfclosing = $matchedVariables['Selfclosing'] === '' ? FALSE : TRUE;
				$arguments = $matchedVariables['Attributes'];

				$this->handler_openingDynamicTag($state, $namespaceIdentifier, $methodIdentifier, $arguments, $selfclosing);
			} elseif (preg_match($regularExpression_closingDynamicTag, $templateElement, $matchedVariables) > 0) {
				$namespaceIdentifier = $matchedVariables['NamespaceIdentifier'];
				$methodIdentifier = $matchedVariables['MethodIdentifier'];
				
				$this->handler_closingDynamicTag($state, $namespaceIdentifier, $methodIdentifier);
			} else {
				$this->handler_textAndShorthandSyntax($state, $templateElement);
			}
		}
		return $state->getRootNode();
	}
	
	/**
	 * Handles an opening or self-closing dynamic tag.
	 *
	 * @param string $namespaceIdentifier Namespace identifier - being looked up in $this->namespaces
	 * @param string $methodIdentifier Method identifier
	 * @param string $arguments Arguments string, not yet parsed
	 * @param boolean $selfclosing true, if the tag is a self-closing tag.
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function handler_openingDynamicTag(F3::Beer3::ParsingState $state, $namespaceIdentifier, $methodIdentifier, $arguments, $selfclosing) {
		if (!array_key_exists($namespaceIdentifier, $this->namespaces)) {
			throw new F3::Beer3::ParsingException('Namespace could not be resolved. This exception should never be thrown!', 1224254792);
		}
		$argumentsObjectTree = $this->parseArguments($arguments);
		$objectToCall = $this->resolveViewHelper($namespaceIdentifier, $methodIdentifier);
		$currentDynamicNode = $this->componentFactory->getComponent('F3::Beer3::DynamicNode', $this->namespaces[$namespaceIdentifier], $methodIdentifier, $objectToCall, $argumentsObjectTree);
		
		$state->getNodeFromStack()->addChildNode($currentDynamicNode);
		
		if (!$selfclosing) {
			$state->pushNodeToStack($currentDynamicNode);
		}
	}
	
	protected function resolveViewHelper($namespaceIdentifier, $methodIdentifier) {
		$explodedViewHelperName = explode('.', $methodIdentifier);
		$methodName = '';
		$className = '';
		if (count($explodedViewHelperName) > 1) {
			$methodName = $explodedViewHelperName[1];
			$className = F3::PHP6::Functions::ucfirst($explodedViewHelperName[0]);
		} else {
			$methodName = $methodIdentifier;
			$className = 'Default';
		}
		$className .= 'ViewHelper';
		$methodName .= 'Method';
		$name = $this->namespaces[$namespaceIdentifier] . '::' . $className;
		try {
			$object = $this->componentFactory->getComponent($name);
		} catch(F3::FLOW3::Component::Exception::UnknownComponent $e) {
			throw new F3::Beer3::ÜarsingException('View helper ' . $name . ' does not exist.', 1224532429);
		}
		if (!method_exists($object, $methodName)) {
			throw new F3::Beer3::ParsingException('Method ' . $methodName . ' in view helper ' . $name . ' does not exist.', 1224532421);
		}
		return array($object, $methodName);
	}
	
	/**
	 * Handles a closing dynamic tag.
	 *
	 * @param unknown_type $namespaceIdentifier
	 * @param unknown_type $methodIdentifier
	 */
	protected function handler_closingDynamicTag(F3::Beer3::ParsingState $state, $namespaceIdentifier, $methodIdentifier) {
		if (!array_key_exists($namespaceIdentifier, $this->namespaces)) {
			throw new F3::Beer3::ParsingException('Namespace could not be resolved. This exception should never be thrown!', 1224256186);
		}
		$lastStackElement = $state->popNodeFromStack();
		if (!($lastStackElement instanceof F3::Beer3::DynamicNode)) {
			throw new F3::Beer3::ParsingException('You closed a templating tag which you never opened!', 1224485838);
		}
		if ($lastStackElement->getViewHelperName() != $methodIdentifier || $lastStackElement->getViewHelperNamespace() != $this->namespaces[$namespaceIdentifier]) {
			throw new F3::Beer3::ParsingException('Templating tags not properly nested.', 1224485398);
		}
	}
	
	/**
	 * Handles the appearance of an object accessor. Creates a new instance of F3::Beer3::ObjectAccessorNode
	 *
	 * @param string $objectAccessorString
	 */
	protected function handler_objectAccessor(F3::Beer3::ParsingState $state, $objectAccessorString) {
		$node = $this->componentFactory->getComponent('F3::Beer3::ObjectAccessorNode', $objectAccessorString);
		$state->getNodeFromStack()->addChildNode($node);
	}
	
	/**
	 * Parse arguments of a given tag, and build up the Arguments Object Tree for each argument.
	 * Returns an associative array, where the key is the name of the argument,
	 * and the value is either an array of Argument Object Trees (for parameter-lists), or a single Argument Object Tree.
	 *
	 * @param string $argumentsString All arguments
	 * @return array An associative array of objects, where the key is 
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function parseArguments($argumentsString) {
		$argumentsObjectTree = array();
		if (preg_match_all(self::SPLIT_PATTERN_TAGARGUMENTS, $argumentsString, $matches, PREG_SET_ORDER) > 0) {
			foreach ($matches as $singleMatch) {
				$argument = $singleMatch['Argument'];
				$value = $this->unquoteArgumentString($singleMatch['ValueSingleQuoted'], $singleMatch['ValueDoubleQuoted']);
				$argumentArray = explode(':', $argument);
				if (count($argumentArray) > 1) {
					$argumentsObjectTree[$argumentArray[0]][$argumentArray[1]] = $this->buildArgumentObjectTree($value);
				} else {
					$argumentsObjectTree[$argument] = $this->buildArgumentObjectTree($value);
				}
			}
		}
		return $argumentsObjectTree;
	}
	
	/**
	 * Build up an argument object tree for the string in $argumentsString
	 *
	 * @param string $argumentsString
	 * @return ArgumentObject the corresponding argument object tree.
	 * @todo Refine doc comment and write method
	 */
	protected function buildArgumentObjectTree($argumentsString) {
		$splittedArguments = $this->splitTemplateAtDynamicTags($argumentsString);
		$rootNode = $this->buildMainObjectTree($splittedArguments);
		return $rootNode;
	}
	
	/**
	 * Removes escapings from a given argument string. Expects two string parameters, with one of them being empty.
	 * The first parameter should be non-empty if the argument was quoted by single quotes, and the second parameter should be non-empty
	 * if the argument was quoted by double quotes.
	 * 
	 * This method is meant as a helper for regular expression results.
	 *
	 * @param string $singleQuotedValue Value, if quoted by single quotes
	 * @param string $doubleQuotedValue Value, if quoted by double quotes
	 * @return string Unquoted value
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function unquoteArgumentString($singleQuotedValue, $doubleQuotedValue) {
		if ($singleQuotedValue != '') {
			$value = str_replace("\'", "'", $singleQuotedValue);
		} else {
			$value = str_replace('\"', '"', $doubleQuotedValue);
		}
		return str_replace('\\\\', '\\', $value);
	}
	
	/**
	 * Takes a regular expression template and replaces "NAMESPACE" with the currently registered namespace identifiers. Returns a regular expression which is ready to use.
	 *
	 * @param string $regularExpression Regular expression template
	 * @return string Regular expression ready to be used
	 */
	protected function prepareTemplateRegularExpression($regularExpression) {
		return str_replace('NAMESPACE', implode('|', array_keys($this->namespaces)), $regularExpression);
	}
	
	/**
	 * Enter description here...
	 *
	 */
	protected function handler_textAndShorthandSyntax(F3::Beer3::ParsingState $state, $text) {
		$sections = preg_split(self::SPLIT_PATTERN_SHORTHANDSYNTAX, $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		foreach ($sections as $section) {
			if (preg_match(self::SCAN_PATTERN_SHORTHANDSYNTAX_OBJECTACCESSORS, $section, $matchedVariables) > 0) {
				$this->handler_objectAccessor($state, $matchedVariables['Object']);
			} else {
				$this->handler_text($state, $section);
			}
		}		
	}
	
	/**
	 * Text node handler
	 *
	 * @param string $text
	 */
	protected function handler_text(F3::Beer3::ParsingState $state, $text) {
		$node = $this->componentFactory->getComponent('F3::Beer3::TextNode', $text);
		$state->getNodeFromStack()->addChildNode($node);	
	}
}


?>