<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Service;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * XML Schema (XSD) Generator. Will generate an XML schema which can be used for autocompletion
 * in schema-aware editors like Eclipse XML editor.
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class XSDGenerator {

	/**
	 * Object manager.
	 *
	 * @var \F3\FLOW3\Object\Manager
	 */
	protected $objectManager;

	/**
	 * The reflection class for AbstractViewHelper. Is needed quite often, that's why we use a pre-initialized one.
	 *
	 * @var \F3\FLOW3\Reflection\ClassReflection
	 */
	protected $abstractViewHelperReflectionClass;

	/**
	 * The doc comment parser.
	 *
	 * @var \F3\FLOW3\Reflection\DocCommentParser
	 */
	protected $docCommentParser;

	/**
	 * Constructor. Sets $this->abstractViewHelperReflectionClass
	 *
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function __construct() {
		$this->abstractViewHelperReflectionClass = new \F3\FLOW3\Reflection\ClassReflection('F3\Fluid\Core\ViewHelper\AbstractViewHelper');
		$this->docCommentParser = new \F3\FLOW3\Reflection\DocCommentParser();
	}

	/**
	 * Inject the object manager.
	 *
	 * @param \F3\FLOW3\Object\Manager $objectManager the object manager to inject
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function injectObjectManager(\F3\FLOW3\Object\ManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Generate the XML Schema definition for a given namespace.
	 * It will generate an XSD file for all view helpers in this namespace.
	 *
	 * @param string $namespace Namespace identifier to generate the XSD for, without leading Backslash.
	 * @return string XML Schema definition
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function generateXSD($namespace) {
		$tmp = str_replace('\\', '/', $namespace);

		if (substr($namespace, -1) !== '\\') {
			$namespace .= '\\';
		}

		$classNames = $this->getClassNamesInNamespace($namespace);

		$xmlRootNode = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
			<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema" targetNamespace="http://typo3.org/ns/fluid/' . $tmp . '"></xsd:schema>');

		foreach ($classNames as $className) {
			$this->generateXMLForClassName($className, $namespace, $xmlRootNode);
		}

		return $xmlRootNode->asXML();
	}

	/**
	 * Get all class names inside this namespace and return them as array.
	 *
	 * @param string $namespace
	 * @return array Array of all class names inside a given namespace.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function getClassNamesInNamespace($namespace) {
		$viewHelperClassNames = array();

		$registeredObjectNames = array_keys($this->objectManager->getRegisteredObjects());
		foreach ($registeredObjectNames as $registeredObjectName) {
			if (strncmp($namespace, $registeredObjectName, strlen($namespace)) === 0) {
				$viewHelperClassNames[] = $registeredObjectName;
			}
		}

		return $registeredObjectNames;
	}

	/**
	 * Generate the XML Schema for a given class name.
	 *
	 * @param string $className Class name to generate the schema for.
	 * @param string $namespace Namespace prefix. Used to split off the first parts of the class name.
	 * @param \SimpleXMLElement $xmlRootNode XML root node where the xsd:element is appended.
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function generateXMLForClassName($className, $namespace, \SimpleXMLElement $xmlRootNode) {
		$reflectionClass = new \F3\FLOW3\Reflection\ClassReflection($className);
		if (!$reflectionClass->isSubclassOf($this->abstractViewHelperReflectionClass)) {
			return;
		}

		$tagName = $this->getTagNameForClass($className, $namespace);

		$xsdElement = $xmlRootNode->addChild('xsd:element');
		$xsdElement['name'] = $tagName;
		$this->docCommentParser->parseDocComment($reflectionClass->getDocComment());
		$this->addDocumentation($this->docCommentParser->getDescription(), $xsdElement);

		$xsdComplexType = $xsdElement->addChild('xsd:complexType');
		$xsdComplexType['mixed'] = 'true';
		$xsdSequence = $xsdComplexType->addChild('xsd:sequence');
		$xsdAny = $xsdSequence->addChild('xsd:any');
		$xsdAny['minOccurs'] = '0';
		$xsdAny['maxOccurs'] = 'unbounded';

		$this->addAttributes($className, $xsdComplexType);
	}

	/**
	 * Get a tag name for a given ViewHelper class.
	 * Example: For the View Helper F3\Fluid\ViewHelpers\Form\SelectViewHelper, and the
	 * namespace prefix F3\Fluid\ViewHelpers\, this method returns "form.select".
	 *
	 * @param string $className Class name
	 * @param string $namespace Base namespace to use
	 * @return string Tag name
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function getTagNameForClass($className, $namespace) {
		$strippedClassName = substr($className, strlen($namespace));
		$classNameParts = explode('\\', $strippedClassName);

		if (count($classNameParts) == 1) {
			$tagName = lcfirst(substr($classNameParts[0], 0, -10)); // strip the "ViewHelper" ending
		} else {
			$tagName = lcfirst($classNameParts[0]) . '.' . lcfirst(substr($classNameParts[1], 0, -10));
		}
		return $tagName;
	}

	/**
	 * Add attribute descriptions to a given tag.
	 * Initializes the view helper and its arguments, and then reads out the list of arguments.
	 *
	 * @param string $className Class name where to add the attribute descriptions
	 * @param \SimpleXMLElement $xsdElement XML element to add the attributes to.
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function addAttributes($className, \SimpleXMLElement $xsdElement) {
		$viewHelper = $this->objectManager->getObject($className);
		$argumentDefinitions = $viewHelper->prepareArguments();

		foreach ($argumentDefinitions as $argumentDefinition) {
			$xsdAttribute = $xsdElement->addChild('xsd:attribute');
			$xsdAttribute['type'] = 'xsd:string';
			$xsdAttribute['name'] = $argumentDefinition->getName();
			$this->addDocumentation($argumentDefinition->getDescription(), $xsdAttribute);
			if ($argumentDefinition->isRequired()) {
				$xsdAttribute['use'] = 'required';
			}
		}
	}

	/**
	 * Add documentation XSD to a given XML node
	 *
	 * As Eclipse renders newlines only on new <xsd:documentation> tags, we wrap every line in a new
	 * <xsd:documentation> tag.
	 * Furthermore, eclipse strips out tags - the only way to prevent this is to have every line wrapped in a
	 * CDATA block AND to replace the < and > with their XML entities. (This is IMHO not XML conformant).
	 *
	 * @param string $documentation Documentation string to add.
	 * @param \SimpleXMLElement $xsdParentNode Node to add the documentation to
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function addDocumentation($documentation, \SimpleXMLElement $xsdParentNode) {
		$xsdAnnotation = $xsdParentNode->addChild('xsd:annotation');
		$documentationLines = explode("\n", $documentation);

		foreach ($documentationLines as $documentationLine) {
			$documentationLine = str_replace('<', '&lt;', $documentationLine);
			$documentationLine = str_replace('>', '&gt;', $documentationLine);
			$this->addChildWithCData($xsdAnnotation, 'xsd:documentation', $documentationLine);
		}
	}

	/**
	 * Add a child node to $parentXMLNode, and wrap the contents inside a CDATA section.
	 *
	 * @param \SimpleXMLElement $parentXMLNode Parent XML Node to add the child to
	 * @param string $childNodeName Name of the child node
	 * @param string $nodeValue Value of the child node. Will be placed inside CDATA.
	 * @return \SimpleXMLElement the new element
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function addChildWithCData(\SimpleXMLElement $parentXMLNode, $childNodeName, $childNodeValue) {
		$parentDomNode = dom_import_simplexml($parentXMLNode);
		$domDocument = new \DOMDocument();

		$childNode = $domDocument->appendChild($domDocument->createElement($childNodeName));
		$childNode->appendChild($domDocument->createCDATASection($childNodeValue));
		$childNodeTarget = $parentDomNode->ownerDocument->importNode($childNode, true);
		$parentDomNode->appendChild($childNodeTarget);
		return simplexml_import_dom($childNodeTarget);
	}
}
?>