<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Schema;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Schema\SchemaGenerator;
use TYPO3Fluid\Fluid\Schema\ViewHelperMetadata;

final class SchemaGeneratorTest extends TestCase
{
    public static function generateSchemaDataProvider(): iterable
    {
        return [
            'viewHelperWithoutDocumentation' => [
                'http://typo3.org/ns/Vendor/Package/ViewHelpers',
                [
                    new ViewHelperMetadata(
                        'Vendor\\Package\\ViewHelpers\\MyViewHelper',
                        'Vendor\\Package',
                        'MyViewHelper',
                        'myViewHelper',
                        '',
                        'http://typo3.org/ns/Vendor/Package/ViewHelpers',
                        [],
                        ['value' => new ArgumentDefinition('value', 'string', '', true)],
                        false,
                    ),
                ],
                '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
                '<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema" targetNamespace="http://typo3.org/ns/Vendor/Package/ViewHelpers">' .
                '<xsd:element name="myViewHelper">' .
                '<xsd:complexType mixed="true">' .
                '<xsd:sequence><xsd:any minOccurs="0"/></xsd:sequence>' .
                '<xsd:attribute type="xsd:string" name="value" use="required">' .
                '<xsd:annotation><xsd:documentation><![CDATA[@type string]]></xsd:documentation></xsd:annotation>' .
                '</xsd:attribute>' .
                '</xsd:complexType>' .
                '</xsd:element>' .
                '</xsd:schema>' . "\n",
            ],
            'viewHelperWithDocumentation' => [
                'http://typo3.org/ns/Vendor/Package/ViewHelpers',
                [
                    new ViewHelperMetadata(
                        'Vendor\\Package\\ViewHelpers\\MyViewHelper',
                        'Vendor\\Package',
                        'MyViewHelper',
                        'myViewHelper',
                        "This is a ViewHelper documentation\nwith newlines",
                        'http://typo3.org/ns/Vendor/Package/ViewHelpers',
                        [],
                        ['value' => new ArgumentDefinition('value', 'string', 'Argument description', false, 'default value')],
                        false,
                    ),
                ],
                '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
                '<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema" targetNamespace="http://typo3.org/ns/Vendor/Package/ViewHelpers">' .
                '<xsd:element name="myViewHelper">' .
                '<xsd:annotation><xsd:documentation><![CDATA[This is a ViewHelper documentation' . "\n" . 'with newlines]]></xsd:documentation></xsd:annotation>' .
                '<xsd:complexType mixed="true">' .
                '<xsd:sequence><xsd:any minOccurs="0"/></xsd:sequence>' .
                '<xsd:attribute type="xsd:string" name="value" default="default value">' .
                '<xsd:annotation><xsd:documentation><![CDATA[Argument description' . "\n" . '@type string]]></xsd:documentation></xsd:annotation>' .
                '</xsd:attribute>' .
                '</xsd:complexType>' .
                '</xsd:element>' .
                '</xsd:schema>' . "\n",
            ],
            'deprecatedViewHelper' => [
                'http://typo3.org/ns/Vendor/Package/ViewHelpers',
                [
                    new ViewHelperMetadata(
                        'Vendor\\Package\\ViewHelpers\\MyViewHelper',
                        'Vendor\\Package',
                        'MyViewHelper',
                        'myViewHelper',
                        '',
                        'http://typo3.org/ns/Vendor/Package/ViewHelpers',
                        ['@deprecated' => 'since 1.2.3, will be removed in 2.0.0'],
                        [],
                        false,
                    ),
                ],
                '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
                '<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema" targetNamespace="http://typo3.org/ns/Vendor/Package/ViewHelpers">' .
                '<xsd:element name="myViewHelper">' .
                '<xsd:annotation><xsd:documentation><![CDATA[@deprecated since 1.2.3, will be removed in 2.0.0]]></xsd:documentation></xsd:annotation>' .
                '<xsd:complexType mixed="true">' .
                '<xsd:sequence><xsd:any minOccurs="0"/></xsd:sequence>' .
                '</xsd:complexType>' .
                '</xsd:element>' .
                '</xsd:schema>' . "\n",
            ],
            'deprecatedViewHelperWithFurtherReading' => [
                'http://typo3.org/ns/Vendor/Package/ViewHelpers',
                [
                    new ViewHelperMetadata(
                        'Vendor\\Package\\ViewHelpers\\MyViewHelper',
                        'Vendor\\Package',
                        'MyViewHelper',
                        'myViewHelper',
                        '',
                        'http://typo3.org/ns/Vendor/Package/ViewHelpers',
                        [
                            '@deprecated' => 'since 1.2.3, will be removed in 2.0.0',
                            '@see' => 'https://docs.typo3.org/somelink',
                        ],
                        [],
                        false,
                    ),
                ],
                '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
                '<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema" targetNamespace="http://typo3.org/ns/Vendor/Package/ViewHelpers">' .
                '<xsd:element name="myViewHelper">' .
                '<xsd:annotation><xsd:documentation><![CDATA[@deprecated since 1.2.3, will be removed in 2.0.0' . "\n" . '@see https://docs.typo3.org/somelink]]></xsd:documentation></xsd:annotation>' .
                '<xsd:complexType mixed="true">' .
                '<xsd:sequence><xsd:any minOccurs="0"/></xsd:sequence>' .
                '</xsd:complexType>' .
                '</xsd:element>' .
                '</xsd:schema>' . "\n",
            ],
            'viewHelperWithFurtherReading' => [
                'http://typo3.org/ns/Vendor/Package/ViewHelpers',
                [
                    new ViewHelperMetadata(
                        'Vendor\\Package\\ViewHelpers\\MyViewHelper',
                        'Vendor\\Package',
                        'MyViewHelper',
                        'myViewHelper',
                        '',
                        'http://typo3.org/ns/Vendor/Package/ViewHelpers',
                        [
                            '@see' => 'https://docs.typo3.org/somelink',
                        ],
                        [],
                        false,
                    ),
                ],
                '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
                '<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema" targetNamespace="http://typo3.org/ns/Vendor/Package/ViewHelpers">' .
                '<xsd:element name="myViewHelper">' .
                '<xsd:annotation><xsd:documentation><![CDATA[@see https://docs.typo3.org/somelink]]></xsd:documentation></xsd:annotation>' .
                '<xsd:complexType mixed="true">' .
                '<xsd:sequence><xsd:any minOccurs="0"/></xsd:sequence>' .
                '</xsd:complexType>' .
                '</xsd:element>' .
                '</xsd:schema>' . "\n",
            ],
            'argumentTypes' => [
                'http://typo3.org/ns/Vendor/Package/ViewHelpers',
                [
                    new ViewHelperMetadata(
                        'Vendor\\Package\\ViewHelpers\\MyViewHelper',
                        'Vendor\\Package',
                        'MyViewHelper',
                        'myViewHelper',
                        '',
                        'http://typo3.org/ns/Vendor/Package/ViewHelpers',
                        [],
                        [
                            'intArg' => new ArgumentDefinition('intArg', 'integer', '', false, 123),
                            'arrayArg' => new ArgumentDefinition('arrayArg', 'array', '', false, ['one', 'two' => [3]]),
                            'boolArg' => new ArgumentDefinition('boolArg', 'bool', '', false, true),
                        ],
                        false,
                    ),
                ],
                '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
                '<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema" targetNamespace="http://typo3.org/ns/Vendor/Package/ViewHelpers">' .
                '<xsd:element name="myViewHelper">' .
                '<xsd:complexType mixed="true">' .
                '<xsd:sequence><xsd:any minOccurs="0"/></xsd:sequence>' .
                '<xsd:attribute type="xsd:integer" name="intArg" default="123">' .
                '<xsd:annotation><xsd:documentation><![CDATA[@type integer]]></xsd:documentation></xsd:annotation>' .
                '</xsd:attribute>' .
                '<xsd:attribute type="xsd:anySimpleType" name="arrayArg" default="{0: \'one\', \'two\': {0: 3}}">' .
                '<xsd:annotation><xsd:documentation><![CDATA[@type array]]></xsd:documentation></xsd:annotation>' .
                '</xsd:attribute>' .
                '<xsd:attribute type="xsd:boolean" name="boolArg" default="true">' .
                '<xsd:annotation><xsd:documentation><![CDATA[@type bool]]></xsd:documentation></xsd:annotation>' .
                '</xsd:attribute>' .
                '</xsd:complexType>' .
                '</xsd:element>' .
                '</xsd:schema>' . "\n",
            ],
            'arbitraryArguments' => [
                'http://typo3.org/ns/Vendor/Package/ViewHelpers',
                [
                    new ViewHelperMetadata(
                        'Vendor\\Package\\ViewHelpers\\MyViewHelper',
                        'Vendor\\Package',
                        'MyViewHelper',
                        'myViewHelper',
                        '',
                        'http://typo3.org/ns/Vendor/Package/ViewHelpers',
                        [],
                        [],
                        true,
                    ),
                ],
                '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
                '<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema" targetNamespace="http://typo3.org/ns/Vendor/Package/ViewHelpers">' .
                '<xsd:element name="myViewHelper">' .
                '<xsd:complexType mixed="true">' .
                '<xsd:sequence><xsd:any minOccurs="0"/></xsd:sequence>' .
                '<xsd:anyAttribute/>' .
                '</xsd:complexType>' .
                '</xsd:element>' .
                '</xsd:schema>' . "\n",
            ],
            'multipleViewHelpers' => [
                'http://typo3.org/ns/Vendor/Package/ViewHelpers',
                [
                    new ViewHelperMetadata(
                        'Vendor\\Package\\ViewHelpers\\MyViewHelper',
                        'Vendor\\Package',
                        'MyViewHelper',
                        'myViewHelper',
                        '',
                        'http://typo3.org/ns/Vendor/Package/ViewHelpers',
                        [],
                        [],
                        false,
                    ),
                    new ViewHelperMetadata(
                        'Vendor\\Package\\ViewHelpers\\Sub\\MyOtherViewHelper',
                        'Vendor\\Package',
                        'Sub\\MyOtherViewHelper',
                        'sub.myOtherViewHelper',
                        '',
                        'http://typo3.org/ns/Vendor/Package/ViewHelpers',
                        [],
                        [],
                        false,
                    ),
                ],
                '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
                '<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema" targetNamespace="http://typo3.org/ns/Vendor/Package/ViewHelpers">' .
                '<xsd:element name="myViewHelper">' .
                '<xsd:complexType mixed="true">' .
                '<xsd:sequence><xsd:any minOccurs="0"/></xsd:sequence>' .
                '</xsd:complexType>' .
                '</xsd:element>' .
                '<xsd:element name="sub.myOtherViewHelper">' .
                '<xsd:complexType mixed="true">' .
                '<xsd:sequence><xsd:any minOccurs="0"/></xsd:sequence>' .
                '</xsd:complexType>' .
                '</xsd:element>' .
                '</xsd:schema>' . "\n",
            ],
        ];
    }

    /**
     * @param ViewHelperMetadata[] $viewHelpers
     */
    #[Test]
    #[DataProvider('generateSchemaDataProvider')]
    public function generateSchema(string $xmlNamespace, array $viewHelpers, string $expected): void
    {
        $xml = (new SchemaGenerator())->generate($xmlNamespace, $viewHelpers);
        self::assertEquals($expected, $xml->asXML());
    }
}
