<?php
namespace TYPO3\Fluid\Service;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * XML Schema (XSD) Generator. Will generate an XML schema which can be used for autocompletion
 * in schema-aware editors like Eclipse XML editor.
 *
 */
class XsdGenerator extends \TYPO3\Fluid\Service\AbstractGenerator {

	/**
	 * Generate the XML Schema definition for a given namespace.
	 * It will generate an XSD file for all view helpers in this namespace.
	 *
	 * @param string $namespace Namespace identifier to generate the XSD for, without leading Backslash.
	 * @return string XML Schema definition
	 */
	public function generateXsd($namespace) {
		$tmp = str_replace('\\', '/', $namespace);

		if (substr($namespace, -1) !== '\\') {
			$namespace .= '\\';
		}

		$classNames = $this->getClassNamesInNamespace($namespace);

		$xmlRootNode = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
			<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema" targetNamespace="http://typo3.org/ns/fluid/' . $tmp . '"></xsd:schema>');

		foreach ($classNames as $className) {
			$this->generateXmlForClassName($className, $namespace, $xmlRootNode);
		}

		return $xmlRootNode->asXML();
	}

	/**
	 * Generate the XML Schema for a given class name.
	 *
	 * @param string $className Class name to generate the schema for.
	 * @param string $namespace Namespace prefix. Used to split off the first parts of the class name.
	 * @param \SimpleXMLElement $xmlRootNode XML root node where the xsd:element is appended.
	 * @return void
	 */
	protected function generateXmlForClassName($className, $namespace, \SimpleXMLElement $xmlRootNode) {
		$reflectionClass = new \TYPO3\FLOW3\Reflection\ClassReflection($className);
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
	 * Add attribute descriptions to a given tag.
	 * Initializes the view helper and its arguments, and then reads out the list of arguments.
	 *
	 * @param string $className Class name where to add the attribute descriptions
	 * @param \SimpleXMLElement $xsdElement XML element to add the attributes to.
	 * @return void
	 */
	protected function addAttributes($className, \SimpleXMLElement $xsdElement) {
		$viewHelper = $this->objectManager->get($className);
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
}
?>