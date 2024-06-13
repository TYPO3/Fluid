<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Schema;

/**
 * @internal
 */
final class SchemaGenerator
{
    /**
     * @param ViewHelperMetadata[] $viewHelpers
     */
    public function generate(string $xmlNamespace, array $viewHelpers): \SimpleXMLElement
    {
        $file = $this->createXmlRootElement($xmlNamespace);
        foreach ($viewHelpers as $metadata) {
            $xsdElement = $file->addChild('xsd:element');

            $xsdElement->addAttribute('name', $metadata->tagName);

            $documentation = $metadata->documentation;
            // Add deprecation information to ViewHelper documentation
            if (isset($metadata->docTags['@deprecated'])) {
                $documentation .= "\n@deprecated " . $metadata->docTags['@deprecated'];
            }
            $documentation = trim($documentation);

            // Add documentation to xml
            if ($documentation !== '') {
                $xsdAnnotation = $xsdElement->addChild('xsd:annotation');
                $xsdDocumentation = $xsdAnnotation->addChild('xsd:documentation');
                $this->appendWithCdata($xsdDocumentation, $documentation);
            }

            $xsdComplexType = $xsdElement->addChild('xsd:complexType');

            // Allow text as well as subelements
            $xsdComplexType->addAttribute('mixed', 'true');

            // Allow a sequence of arbitrary subelements of any type
            $xsdSequence = $xsdComplexType->addChild('xsd:sequence');
            $xsdAny = $xsdSequence->addChild('xsd:any');
            $xsdAny->addAttribute('minOccurs', '0');

            // Add argument definitions to xml
            foreach ($metadata->argumentDefinitions as $argumentDefinition) {
                $default = $argumentDefinition->getDefaultValue();
                $type = $argumentDefinition->getType();

                $xsdAttribute = $xsdComplexType->addChild('xsd:attribute');
                $xsdAttribute->addAttribute('type', $this->convertPhpTypeToXsdType($type));
                $xsdAttribute->addAttribute('name', $argumentDefinition->getName());
                if ($argumentDefinition->isRequired()) {
                    $xsdAttribute->addAttribute('use', 'required');
                } else {
                    $xsdAttribute->addAttribute('default', $this->createFluidRepresentation($default));
                }

                // Add PHP type to documentation text
                // TODO check if there is a better field for this
                $documentation = $argumentDefinition->getDescription();
                $documentation .= "\n@type $type";
                $documentation = trim($documentation);

                // Add documentation for argument to xml
                $xsdAnnotation = $xsdAttribute->addChild('xsd:annotation');
                $xsdDocumentation = $xsdAnnotation->addChild('xsd:documentation');
                $this->appendWithCdata($xsdDocumentation, $documentation);
            }

            if ($metadata->allowsArbitraryArguments) {
                $xsdComplexType->addChild('xsd:anyAttribute');
            }
        }

        return $file;
    }

    private function appendWithCdata(\SimpleXMLElement $parent, string $text): \SimpleXMLElement
    {
        $parentDomNode = dom_import_simplexml($parent);
        $parentDomNode->appendChild($parentDomNode->ownerDocument->createCDATASection($text));
        return simplexml_import_dom($parentDomNode);
    }

    private function createXmlRootElement(string $targetNamespace): \SimpleXMLElement
    {
        return new \SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?><xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema" targetNamespace="' . $targetNamespace . '"></xsd:schema>',
        );
    }

    private function convertPhpTypeToXsdType(string $type): string
    {
        switch ($type) {
            case 'integer':
                return 'xsd:integer';
            case 'float':
                return 'xsd:float';
            case 'double':
                return 'xsd:double';
            case 'boolean':
            case 'bool':
                return 'xsd:boolean';
            case 'string':
                return 'xsd:string';
            case 'array':
            case 'mixed':
            default:
                return 'xsd:anySimpleType';
        }
    }

    private function createFluidRepresentation(mixed $input, bool $isRoot = true): string
    {
        if (is_array($input)) {
            $fluidArray = [];
            foreach ($input as $key => $value) {
                $fluidArray[] = $this->createFluidRepresentation($key, false) . ': ' . $this->createFluidRepresentation($value, false);
            }
            return '{' . implode(', ', $fluidArray) . '}';
        }

        if (is_string($input) && !$isRoot) {
            return "'" . addcslashes($input, "'") . "'";
        }

        if (is_bool($input)) {
            return ($input) ? 'true' : 'false';
        }

        if (is_null($input)) {
            return 'NULL';
        }

        // Generally, this wouldn't be correct, since it's not the correct representation,
        // but in the context of XSD files we merely need to provide *any* string representation
        if (is_object($input)) {
            return '';
        }

        return (string)$input;
    }
}
