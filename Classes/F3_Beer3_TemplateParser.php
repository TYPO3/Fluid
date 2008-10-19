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
 * [Enter description here]
 *
 * @package
 * @subpackage
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class TemplateParser {
	const SCAN_PATTERN_NAMESPACEDECLARATION = '/(?:^|[^\\\\]+){namespace\s*([a-zA-Z]+[a-zA-Z0-9]*)\s*=\s*(F3::(?:\w+|::)+)\s*}/m';
	const SPLIT_PATTERN_TEMPLATE_DYNAMICTAGS = '/(<\/?(?:(?:NAMESPACE):[a-zA-Z0-9:]+)(?:\s*[a-zA-Z0-9:]+=(?:"(?:\\\"|[^"])*"|\'(?:\\\\\'|[^\'])*\')\s*)*\/?>)/';
	const SCAN_PATTERN_TEMPLATE_DYNAMICTAG = '/^<(?P<NamespaceIdentifier>NAMESPACE):(?P<MethodIdentifier>[a-zA-Z0-9:]+)(?P<Attributes>(?:\s*[a-zA-Z0-9:]+=(?:"(?:\\\"|[^"])*"|\'(?:\\\\\'|[^\'])*\')\s*)*)(?P<Selfclosing>\/?)>$/';
	const SCAN_PATTERN_TEMPLATE_CLOSINGDYNAMICTAG = '/^<\/(?P<NamespaceIdentifier>NAMESPACE):(?P<MethodIdentifier>[a-zA-Z0-9:]+)\s*>$/';
	const SPLIT_PATTERN_TAGARGUMENTS = '/(?:\s*(?P<Argument>[a-zA-Z0-9:]+)=(?:"(?P<ValueDoubleQuoted>(?:\\\"|[^"])*)"|\'(?P<ValueSingleQuoted>(?:\\\\\'|[^\'])*)\')\s*)/';
	
	/**
	 * Namespace identifiers and their component name prefix (Associative array).
	 * @var array
	 */
	protected $namespaces = array();
	
	/**
	 * Stack of currently open tags. Needed to check for valid nesting of our tags.
	 * @var array
	 */
	protected $currentObjectStack = array();
	
	/**
	 * Parses a given template and returns an object tree, identified by a root node
	 *
	 * @param string $templateString
	 * @return TreeNode the root node
	 * @todo Refine doc comment
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function parse($templateString) {
		if (!is_string($templateString)) throw new F3::Beer3::Exception('Parse requires a template string as argument, ' . gettype($templateString) . ' given.', 1224237899);
		
		$this->initialize();
		
		$this->extractNamespaceDefinitions($templateString);
		$splittedTemplate = $this->splitTemplateAtDynamicTags();
		$this->buildMainObjectTree($splittedTemplate);
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
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function extractNamespaceDefinitions($templateString) {
		if (preg_match_all(self::SCAN_PATTERN_NAMESPACEDECLARATION, $templateString, $matchedVariables) > 0) {
			foreach ($matchedVariables[0] as $index => $tmp) {
				$namespaceIdentifier = $matchedVariables[1][$index];
				$fullyQualifiedNamespace = $matchedVariables[2][$index];
				if (key_exists($namespaceIdentifier, $this->namespaces)) {
					throw new F3::Beer3::Exception('Namespace identifier "' . $namespaceIdentifier . '" is already registered. Do not redeclare namespaces!', 1224241246);
				}
				$this->namespaces[$namespaceIdentifier] = $fullyQualifiedNamespace;
			}
		}
	}
	
	/**
	 * Splits the template string on all dynamic tags found.
	 * 
	 * @param string $templateString Template string to split.
	 * @return array Splitted template string
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function splitTemplateAtDynamicTags($templateString) {
		$regularExpression = $this->prepareTemplateRegularExpression(self::SPLIT_PATTERN_TEMPLATE_DYNAMICTAGS);
		return preg_split($regularExpression, $source, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
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
		
		foreach ($splittedTemplate as $templateElement) {
			if (preg_match($regularExpression_dynamicTag, $templateElement, $matchedVariables) > 0) {
				$namespaceIdentifier = $matchedVariables['NamespaceIdentifier'];
				$methodIdentifier = $matchedVariables['MethodIdentifier'];
				$selfclosing = $matchedVariables['Selfclosing'] === '' ? false : true;
				$arguments = $matchedVariables['Arguments'];
				
				$this->handler_openingDynamicTag($namespaceIdentifier, $methodIdentifier, $arguments, $selfclosing);
			} elseif (preg_match($regularExpression_closingDynamicTag, $templateElement, $matchedVariables) > 0) {
				$namespaceIdentifier = $matchedVariables['NamespaceIdentifier'];
				$methodIdentifier = $matchedVariables['MethodIdentifier'];
				
				$this->handler_closingDynamicTag($namespaceIdentifier, $methodIdentifier);
			} else {
				$this->handler_text($templateElement);
			}
		}
	}
	
	/**
	 * Handles an opening or self-closing dynamic tag.
	 *
	 * @param unknown_type $namespaceIdentifier
	 * @param unknown_type $methodIdentifier
	 * @param unknown_type $arguments
	 * @param unknown_type $selfclosing
	 */
	protected function handler_openingDynamicTag($namespaceIdentifier, $methodIdentifier, $arguments, $selfclosing) {
		if (array_key_exists($namespaceIdentifier, $this->namespaces)) {
			
			$this->parseArguments($arguments);
			// build up argument objects
			// build up the actual DynamicTreeNode and push it to stack
			if (!$selfclosing) {
				array_push($this->currentObjectStack, $OBJECT);
			}
		} else {
			throw new F3::Beer3::Exception('Namespace could not be resolved. This exception should never be thrown!', 1224254792);
		}
	}
	
	/**
	 * Handles a closing dynamic tag.
	 *
	 * @param unknown_type $namespaceIdentifier
	 * @param unknown_type $methodIdentifier
	 */
	protected function handler_closingDynamicTag($namespaceIdentifier, $methodIdentifier) {
		if (array_key_exists($namespaceIdentifier, $this->namespaces)) {
			// todo
		} else {
			throw new F3::Beer3::Exception('Namespace could not be resolved. This exception should never be thrown!', 1224256186);
		}
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
		if (preg_match_all(self::SPLIT_PATTERN_TAGARGUMENTS, $argumentsString, $matches, PREG_SET_ORDER) > 0) {
			foreach ($matches as $singleMatch) {
				$argument = $singleMatch['Argument'];
				$value = '';
				if ($singleMatch['ValueSingleQuoted'] != '') {
					$value = str_replace("\'", "'", $singleMatch['ValueSingleQuoted']);
				} else {
					$value = str_replace('\"', '"', $singleMatch['ValueDoubleQuoted']);
				}
				$value = str_replace('\\\\', '\\', $value);
				// argument and value have to be handled
			}
		}
		// return array of objects, and handle multiple dimensions in argument
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
	protected function handler_text() {
		
	}
}


?>