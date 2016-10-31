<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\Patterns;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessor\NamespaceDetectionTemplateProcessor;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for Regular expressions in parser
 */
class PatternsTest extends UnitTestCase
{

    /**
     * @test
     */
    public function testSCAN_PATTERN_NAMESPACEDECLARATION()
    {
        $this->assertEquals(preg_match(NamespaceDetectionTemplateProcessor::NAMESPACE_DECLARATION, '{namespace acme=Acme.MyPackage\Bla\blubb}'), 1, 'The SCAN_PATTERN_NAMESPACEDECLARATION pattern did not match a namespace declaration (1).');
        $this->assertEquals(preg_match(NamespaceDetectionTemplateProcessor::NAMESPACE_DECLARATION, '{namespace acme=Acme.MyPackage\Bla\Blubb }'), 1, 'The SCAN_PATTERN_NAMESPACEDECLARATION pattern did not match a namespace declaration (2).');
        $this->assertEquals(preg_match(NamespaceDetectionTemplateProcessor::NAMESPACE_DECLARATION, '    {namespace foo = Foo\Bla3\Blubb }    '), 1, 'The SCAN_PATTERN_NAMESPACEDECLARATION pattern did not match a namespace declaration (3).');
        $this->assertEquals(preg_match(NamespaceDetectionTemplateProcessor::NAMESPACE_DECLARATION, '    {namespace foo.bar  = Foo\Bla3\Blubb }    '), 1, 'The SCAN_PATTERN_NAMESPACEDECLARATION pattern did not match a namespace declaration (4).');
        $this->assertEquals(preg_match(NamespaceDetectionTemplateProcessor::NAMESPACE_DECLARATION, ' \{namespace fblubb = TYPO3.Fluid\Bla3\Blubb }'), 0, 'The SCAN_PATTERN_NAMESPACEDECLARATION pattern did match a namespace declaration even if it was escaped. (1)');
        $this->assertEquals(preg_match(NamespaceDetectionTemplateProcessor::NAMESPACE_DECLARATION, '\{namespace typo3 = TYPO3.TYPO3\Bla3\Blubb }'), 0, 'The SCAN_PATTERN_NAMESPACEDECLARATION pattern did match a namespace declaration even if it was escaped. (2)');
    }

    /**
     * @test
     */
    public function testSPLIT_PATTERN_DYNAMICTAGS()
    {
        $pattern = $this->insertNamespaceIntoRegularExpression(Patterns::$SPLIT_PATTERN_TEMPLATE_DYNAMICTAGS, ['typo3', 't3', 'f']);

        $source = '<html><head> <f:a.testing /> <f:blablubb> {testing}</f4:blz> </t3:hi.jo>';
        $expected = ['<html><head> ','<f:a.testing />', ' ', '<f:blablubb>', ' {testing}', '</f4:blz>', ' ', '</t3:hi.jo>'];
        $this->assertEquals(preg_split($pattern, $source, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY), $expected, 'The SPLIT_PATTERN_DYNAMICTAGS pattern did not split the input string correctly with simple tags.');

        $source = 'hi<f:testing attribute="Hallo>{yep}" nested:attribute="jup" />ja';
        $expected = ['hi', '<f:testing attribute="Hallo>{yep}" nested:attribute="jup" />', 'ja'];
        $this->assertEquals(preg_split($pattern, $source, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY), $expected, 'The SPLIT_PATTERN_DYNAMICTAGS pattern did not split the input string correctly with  > inside an attribute.');

        $source = 'hi<f:testing attribute="Hallo\"{yep}" nested:attribute="jup" />ja';
        $expected = ['hi', '<f:testing attribute="Hallo\"{yep}" nested:attribute="jup" />', 'ja'];
        $this->assertEquals(preg_split($pattern, $source, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY), $expected, 'The SPLIT_PATTERN_DYNAMICTAGS pattern did not split the input string correctly if a " is inside a double-quoted string.');

        $source = 'hi<f:testing attribute=\'Hallo>{yep}\' nested:attribute="jup" />ja';
        $expected = ['hi', '<f:testing attribute=\'Hallo>{yep}\' nested:attribute="jup" />', 'ja'];
        $this->assertEquals(preg_split($pattern, $source, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY), $expected, 'The SPLIT_PATTERN_DYNAMICTAGS pattern did not split the input string correctly with single quotes as attribute delimiters.');

        $source = 'hi<f:testing attribute=\'Hallo\\\'{yep}\' nested:attribute="jup" />ja';
        $expected = ['hi', '<f:testing attribute=\'Hallo\\\'{yep}\' nested:attribute="jup" />', 'ja'];
        $this->assertEquals(preg_split($pattern, $source, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY), $expected, 'The SPLIT_PATTERN_DYNAMICTAGS pattern did not split the input string correctly if \' is inside a single-quoted attribute.');

        $source = 'Hallo <f:testing><![CDATA[<f:notparsed>]]></f:testing>';
        $expected = ['Hallo ', '<f:testing>', '<![CDATA[<f:notparsed>]]>', '</f:testing>'];
        $this->assertEquals(preg_split($pattern, $source, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY), $expected, 'The SPLIT_PATTERN_DYNAMICTAGS pattern did not split the input string correctly if there is a CDATA section the parser should ignore.');

        $veryLongViewHelper = '<f:form enctype="multipart/form-data" onsubmit="void(0)" onreset="void(0)" action="someAction" arguments="{arg1: \'val1\', arg2: \'val2\'}" controller="someController" package="YourCompanyName.somePackage" subpackage="YourCompanyName.someSubpackage" section="someSection" format="txt" additionalParams="{param1: \'val1\', param2: \'val2\'}" absolute="true" addQueryString="true" argumentsToBeExcludedFromQueryString="{0: \'foo\'}" />';
        $source = $veryLongViewHelper . 'Begin' . $veryLongViewHelper . 'End';
        $expected = [$veryLongViewHelper, 'Begin', $veryLongViewHelper, 'End'];
        $this->assertEquals(preg_split($pattern, $source, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY), $expected, 'The SPLIT_PATTERN_DYNAMICTAGS pattern did not split the input string correctly if the VH has lots of arguments.');

        $source = '<f:a.testing data-bar="foo"> <f:a.testing>';
        $expected = ['<f:a.testing data-bar="foo">', ' ', '<f:a.testing>'];
        $this->assertEquals(preg_split($pattern, $source, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY), $expected, 'The SPLIT_PATTERN_DYNAMICTAGS pattern did not split the input string correctly with data- attribute.');

        $source = '<foo:a.testing someArgument="bar"> <f:a.testing>';
        $expected = ['<foo:a.testing someArgument="bar">', ' ', '<f:a.testing>'];
        $this->assertEquals(preg_split($pattern, $source, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY), $expected, 'The SPLIT_PATTERN_DYNAMICTAGS pattern did not split the input string correctly with custom namespace identifier.');

        $source = '<foo.bar:a.testing someArgument="baz"> <f:a.testing>';
        $expected = ['<foo.bar:a.testing someArgument="baz">', ' ', '<f:a.testing>'];
        $this->assertEquals(preg_split($pattern, $source, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY), $expected, 'The SPLIT_PATTERN_DYNAMICTAGS pattern did not split the input string correctly with custom multi-part namespace identifier.');
    }

    /**
     * @test
     */
    public function testSCAN_PATTERN_DYNAMICTAG()
    {
        $pattern = $this->insertNamespaceIntoRegularExpression(Patterns::$SCAN_PATTERN_TEMPLATE_VIEWHELPERTAG, ['f']);
        $source = '<f:crop attribute="Hallo">';
        $expected = [
            0 => '<f:crop attribute="Hallo">',
            'NamespaceIdentifier' => 'f',
            1 => 'f',
            'MethodIdentifier' => 'crop',
            2 => 'crop',
            'Attributes' => ' attribute="Hallo"',
            3 => ' attribute="Hallo"',
            'Selfclosing' => '',
            4 => ''
        ];
        preg_match($pattern, $source, $matches);
        $this->assertEquals($expected, $matches, 'The SCAN_PATTERN_DYNAMICTAG does not match correctly.');

        $pattern = $this->insertNamespaceIntoRegularExpression(Patterns::$SCAN_PATTERN_TEMPLATE_VIEWHELPERTAG, ['f']);
        $source = '<f:crop data-attribute="Hallo">';
        $expected = [
            0 => '<f:crop data-attribute="Hallo">',
            'NamespaceIdentifier' => 'f',
            1 => 'f',
            'MethodIdentifier' => 'crop',
            2 => 'crop',
            'Attributes' => ' data-attribute="Hallo"',
            3 => ' data-attribute="Hallo"',
            'Selfclosing' => '',
            4 => ''
        ];
        preg_match($pattern, $source, $matches);
        $this->assertEquals($expected, $matches, 'The SCAN_PATTERN_DYNAMICTAG does not match correctly with data- attributes.');

        $source = '<f:base />';
        $expected = [
            0 => '<f:base />',
            'NamespaceIdentifier' => 'f',
            1 => 'f',
            'MethodIdentifier' => 'base',
            2 => 'base',
            'Attributes' => '',
            3 => '',
            'Selfclosing' => '/',
            4 => '/'
        ];
        preg_match($pattern, $source, $matches);
        $this->assertEquals($expected, $matches, 'The SCAN_PATTERN_DYNAMICTAG does not match correctly when there is a space before the self-closing tag.');

        $source = '<f:crop attribute="Ha\"llo"/>';
        $expected = [
            0 => '<f:crop attribute="Ha\"llo"/>',
            'NamespaceIdentifier' => 'f',
            1 => 'f',
            'MethodIdentifier' => 'crop',
            2 => 'crop',
            'Attributes' => ' attribute="Ha\"llo"',
            3 => ' attribute="Ha\"llo"',
            'Selfclosing' => '/',
            4 => '/'
        ];
        preg_match($pattern, $source, $matches);
        $this->assertEquals($expected, $matches, 'The SCAN_PATTERN_DYNAMICTAG does not match correctly with self-closing tags.');

        $source = '<f:link.uriTo complex:attribute="Ha>llo" a="b" c=\'d\'/>';
        $expected = [
            0 => '<f:link.uriTo complex:attribute="Ha>llo" a="b" c=\'d\'/>',
            'NamespaceIdentifier' => 'f',
            1 => 'f',
            'MethodIdentifier' => 'link.uriTo',
            2 => 'link.uriTo',
            'Attributes' => ' complex:attribute="Ha>llo" a="b" c=\'d\'',
            3 => ' complex:attribute="Ha>llo" a="b" c=\'d\'',
            'Selfclosing' => '/',
            4 => '/'
        ];
        preg_match($pattern, $source, $matches);
        $this->assertEquals($expected, $matches, 'The SCAN_PATTERN_DYNAMICTAG does not match correctly with complex attributes and > inside the attributes.');
    }

    /**
     * @test
     */
    public function testSCAN_PATTERN_CLOSINGDYNAMICTAG()
    {
        $pattern = $this->insertNamespaceIntoRegularExpression(Patterns::$SCAN_PATTERN_TEMPLATE_CLOSINGVIEWHELPERTAG, ['f']);
        $this->assertEquals(preg_match($pattern, '</f:bla>'), 1, 'The SCAN_PATTERN_CLOSINGDYNAMICTAG does not match a tag it should match.');
        $this->assertEquals(preg_match($pattern, '</f:bla.a    >'), 1, 'The SCAN_PATTERN_CLOSINGDYNAMICTAG does not match a tag (with spaces included) it should match.');
        $this->assertEquals(preg_match($pattern, '</t:bla>'), 1, 'The SCAN_PATTERN_CLOSINGDYNAMICTAG does not match a unknown namespace tag it should match.');
    }

    /**
     * @test
     */
    public function testSPLIT_PATTERN_TAGARGUMENTS()
    {
        $pattern = Patterns::$SPLIT_PATTERN_TAGARGUMENTS;
        $source = ' test="Hallo" argument:post="\'Web" other=\'Single"Quoted\' data-foo="bar"';
        $this->assertEquals(preg_match_all($pattern, $source, $matches, PREG_SET_ORDER), 4, 'The SPLIT_PATTERN_TAGARGUMENTS does not match correctly.');
        $this->assertEquals('data-foo', $matches[3]['Argument']);
    }

    /**
     * @test
     */
    public function testSPLIT_PATTERN_SHORTHANDSYNTAX()
    {
        $pattern = $this->insertNamespaceIntoRegularExpression(Patterns::$SPLIT_PATTERN_SHORTHANDSYNTAX, ['f']);

        $source = 'some string{Object.bla}here as well';
        $expected = ['some string', '{Object.bla}', 'here as well'];
        $this->assertEquals(preg_split($pattern, $source, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY), $expected, 'The SPLIT_PATTERN_SHORTHANDSYNTAX pattern did not split the input string correctly with a simple example.');

        $source = 'some {}string\{Object.bla}here as well';
        $expected = ['some {}string\\', '{Object.bla}', 'here as well'];
        $this->assertEquals(preg_split($pattern, $source, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY), $expected, 'The SPLIT_PATTERN_SHORTHANDSYNTAX pattern did not split the input string correctly with an escaped example. (1)');

        $source = 'some {f:viewHelper()} as well';
        $expected = ['some ', '{f:viewHelper()}', ' as well'];
        $this->assertEquals(preg_split($pattern, $source, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY), $expected, 'The SPLIT_PATTERN_SHORTHANDSYNTAX pattern did not split the input string correctly with an escaped example. (2)');

        $source = 'abc {f:for(arg1: post)} def';
        $expected = ['abc ', '{f:for(arg1: post)}', ' def'];
        $this->assertEquals(preg_split($pattern, $source, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY), $expected, 'The SPLIT_PATTERN_SHORTHANDSYNTAX pattern did not split the input string correctly with an escaped example.(3)');

        $source = 'abc {bla.blubb->f:for(param:42)} def';
        $expected = ['abc ', '{bla.blubb->f:for(param:42)}', ' def'];
        $this->assertEquals(preg_split($pattern, $source, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY), $expected, 'The SPLIT_PATTERN_SHORTHANDSYNTAX pattern did not split the input string correctly with an escaped example.(4)');

        $source = 'abc {f:for(bla:"post{{")} def';
        $expected = ['abc ', '{f:for(bla:"post{{")}', ' def'];
        $this->assertEquals(preg_split($pattern, $source, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY), $expected, 'The SPLIT_PATTERN_SHORTHANDSYNTAX pattern did not split the input string correctly with an escaped example.(5)');

        $source = 'abc {f:for(param:"abc\"abc")} def';
        $expected = ['abc ', '{f:for(param:"abc\"abc")}', ' def'];
        $this->assertEquals(preg_split($pattern, $source, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY), $expected, 'The SPLIT_PATTERN_SHORTHANDSYNTAX pattern did not split the input string correctly with an escaped example.(6)');

        $source = 'abc {foo:for(arg1: post)} def';
        $expected = ['abc ', '{foo:for(arg1: post)}', ' def'];
        $this->assertEquals(preg_split($pattern, $source, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY), $expected, 'The SPLIT_PATTERN_SHORTHANDSYNTAX pattern did not split the input string correctly with a custom namespace identifier.(7)');

        $source = 'abc {bla.blubb->foo:for(param:42)} def';
        $expected = ['abc ', '{bla.blubb->foo:for(param:42)}', ' def'];
        $this->assertEquals(preg_split($pattern, $source, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY), $expected, 'The SPLIT_PATTERN_SHORTHANDSYNTAX pattern did not split the input string correctly with a custom namespace identifier.(8)');

        $source = 'abc {foo.bar:for(arg1: post)} def';
        $expected = ['abc ', '{foo.bar:for(arg1: post)}', ' def'];
        $this->assertEquals(preg_split($pattern, $source, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY), $expected, 'The SPLIT_PATTERN_SHORTHANDSYNTAX pattern did not split the input string correctly with a custom multi-part namespace identifier.(9)');

        $source = 'abc {bla.blubb->foo.bar:for(param:42)} def';
        $expected = ['abc ', '{bla.blubb->foo.bar:for(param:42)}', ' def'];
        $this->assertEquals(preg_split($pattern, $source, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY), $expected, 'The SPLIT_PATTERN_SHORTHANDSYNTAX pattern did not split the input string correctly with a custom multi-part namespace identifier.(10)');
    }

    /**
     * @test
     */
    public function testSPLIT_PATTERN_SHORTHANDSYNTAX_VIEWHELPER()
    {
        $pattern = Patterns::$SPLIT_PATTERN_SHORTHANDSYNTAX_VIEWHELPER;

        $source = 'f:for(each: bla)';
        $expected = [
            0 => [
                0 => 'f:for(each: bla)',
                1 => 'f',
                'NamespaceIdentifier' => 'f',
                2 => 'for',
                'MethodIdentifier' => 'for',
                3 => 'each: bla',
                'ViewHelperArguments' => 'each: bla'
            ]
        ];
        preg_match_all($pattern, $source, $matches, PREG_SET_ORDER);
        $this->assertEquals($matches, $expected, 'The SPLIT_PATTERN_SHORTHANDSYNTAX_VIEWHELPER');

        $source = 'f:for(each: bla)->foo.bar:bla(a:"b\"->(f:a()", cd: {a:b})';
        $expected = [
            0 => [
                0 => 'f:for(each: bla)',
                1 => 'f',
                'NamespaceIdentifier' => 'f',
                2 => 'for',
                'MethodIdentifier' => 'for',
                3 => 'each: bla',
                'ViewHelperArguments' => 'each: bla'
            ],
            1 => [
                0 => 'foo.bar:bla(a:"b\"->(f:a()", cd: {a:b})',
                1 => 'foo.bar',
                'NamespaceIdentifier' => 'foo.bar',
                2 => 'bla',
                'MethodIdentifier' => 'bla',
                3 => 'a:"b\"->(f:a()", cd: {a:b}',
                'ViewHelperArguments' => 'a:"b\"->(f:a()", cd: {a:b}'
            ]
        ];
        preg_match_all($pattern, $source, $matches, PREG_SET_ORDER);
        $this->assertEquals($matches, $expected, 'The SPLIT_PATTERN_SHORTHANDSYNTAX_VIEWHELPER');
    }

    /**
     * @test
     */
    public function testSCAN_PATTERN_SHORTHANDSYNTAX_OBJECTACCESSORS()
    {
        $pattern = Patterns::$SCAN_PATTERN_SHORTHANDSYNTAX_OBJECTACCESSORS;
        $this->assertEquals(preg_match($pattern, '{object}'), 1, 'Object accessor not identified!');
        $this->assertEquals(preg_match($pattern, '{oBject1}'), 1, 'Object accessor not identified if there is a number and capitals inside!');
        $this->assertEquals(preg_match($pattern, '{object.recursive}'), 1, 'Object accessor not identified if there is a dot inside!');
        $this->assertEquals(preg_match($pattern, '{object-with-dash.recursive_value}'), 1, 'Object accessor not identified if there is a _ or - inside!');
        $this->assertEquals(preg_match($pattern, '{f:for()}'), 1, 'Object accessor not identified if it contains only of a ViewHelper.');
        $this->assertEquals(preg_match($pattern, '{f:for()->f:for2()}'), 1, 'Object accessor not identified if it contains only of a ViewHelper (nested).');
        $this->assertEquals(preg_match($pattern, '{abc->f:for()}'), 1, 'Object accessor not identified if there is a ViewHelper inside!');
        $this->assertEquals(preg_match($pattern, '{bla-blubb.recursive_value->f:for()->f:for()}'), 1, 'Object accessor not identified if there is a recursive ViewHelper inside!');
        $this->assertEquals(preg_match($pattern, '{f:for(arg1:arg1value, arg2: "bla\"blubb")}'), 1, 'Object accessor not identified if there is an argument inside!');
        $this->assertEquals(preg_match($pattern, '{foo.bar:for(arg1:arg1value)}'), 1, 'Object accessor not identified multi-part namespace identifier!');
        $this->assertEquals(preg_match($pattern, '{dash:value}'), 0, 'Object accessor identified, but was array!');
        // $this->assertEquals(preg_match($pattern, '{}'), 0, 'Object accessor identified, and it was empty!');
    }

    /**
     * @return array
     */
    public function dataProviderSCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS()
    {
        return [
            ['string' => '{a:b}'],
            ['string' => '{a:b, c :   d}'],
            ['string' => '{  a:b, c :   d }'],

            // multiple lines
            ['string' => '{' . chr(10) . ' foo: 1,' . chr(10) . ' bar: 2' . chr(10) . '}'],

            // trailing comma
            ['string' => '{a:b, c :   d,}'],

            ['string' => '{a : 123}'],
            ['string' => '{a:"String"}'],
            ['string' => '{a:\'String\'}'],

            // empty string value
            ['string' => '{a:""}'],
            ['string' => '{a:\'\'}'],

            // nested arrays
            ['string' => '{a:{bla:{x:z}, b: a}}'],
            ['string' => '{a:"{bla{{}"}'],

            // quoted keys
            ['string' => '{"foo": "bar"}'],
            ['string' => '{"foo[bar]": "baz"}'],
            ['string' => '{"foo.bar": "baz"}'],
            ['string' => '{\'foo\': "bar"}'],
            ['string' => '{  \'foo\'  : "bar" }'],
            ['string' => '{"a":{bla:{x:z}, b: a}}'],
            ['string' => '{a:{bla:{"x":z}, b: a}}'],
            ['string' => '{"@a": "bar"}'],
            ['string' => '{\'_b\': "bar"}']
        ];
    }

    /**
     * @param string $string
     * @dataProvider dataProviderSCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS()
     * @test
     */
    public function testSCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS($string)
    {
        $success = preg_match(Patterns::$SCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS, $string, $matches) === 1;
        $this->assertTrue($success);
        $this->assertSame($string, $matches[0]);
    }

    /**
     * @return array
     */
    public function dataProviderInvalidSCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS()
    {
        return [
            ['string' => '{"foo\': "bar"}'],
            ['string' => '{"": "bar"}'],
            ['string' => '{\'\': "bar"}'],
        ];
    }

    /**
     * @param string $string
     * @dataProvider dataProviderInvalidSCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS()
     * @test
     */
    public function SCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS_doesNotMatchInvalidSyntax($string)
    {
        $success = preg_match(Patterns::$SCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS, $string, $matches) === 1;
        $this->assertFalse($success);
    }

    /**
     * @test
     */
    public function testSPLIT_PATTERN_SHORTHANDSYNTAX_ARRAY_PARTS()
    {
        $pattern = Patterns::$SPLIT_PATTERN_SHORTHANDSYNTAX_ARRAY_PARTS;

        $source = '{a: b, e: {c:d, "e#":f, \'g\': "h"}}';
        $success = preg_match_all($pattern, $source, $matches, PREG_SET_ORDER) > 0;

        $expected = [
            0 => [
                0 => 'a: b',
                'ArrayPart' => 'a: b',
                1 => 'a: b',
                'Key' => 'a',
                2 => 'a',
                'QuotedString' => '',
                3 => '',
                'VariableIdentifier' => 'b',
                4 => 'b'
            ],
            1 => [
                0 => 'e: {c:d, "e#":f, \'g\': "h"}',
                'ArrayPart' => 'e: {c:d, "e#":f, \'g\': "h"}',
                1 => 'e: {c:d, "e#":f, \'g\': "h"}',
                'Key' => 'e',
                2 => 'e',
                'QuotedString' => '',
                3 => '',
                'VariableIdentifier' => '',
                4 => '',
                'Number' => '',
                5 => '',
                'Subarray' => 'c:d, "e#":f, \'g\': "h"',
                6 => 'c:d, "e#":f, \'g\': "h"'
            ]
        ];
        $this->assertTrue($success);
        $this->assertEquals($expected, $matches, 'The regular expression splitting the array apart does not work!');
    }

    /**
     * @test
     */
    public function SPLIT_PATTERN_SHORTHANDSYNTAX_ARRAY_PARTS_matchesQuotedKeys()
    {
        $pattern = Patterns::$SPLIT_PATTERN_SHORTHANDSYNTAX_ARRAY_PARTS;

        $source = '{"a": b, \'c\': d}';
        $success = preg_match_all($pattern, $source, $matches, PREG_SET_ORDER) > 0;

        $expected = [
            0 => [
                0 => '"a": b',
                'ArrayPart' => '"a": b',
                1 => '"a": b',
                'Key' => '"a"',
                2 => '"a"',
                'QuotedString' => '',
                3 => '',
                'VariableIdentifier' => 'b',
                4 => 'b'
            ],
            1 => [
                0 => '\'c\': d',
                'ArrayPart' => '\'c\': d',
                1 => '\'c\': d',
                'Key' => '\'c\'',
                2 => '\'c\'',
                'QuotedString' => '',
                3 => '',
                'VariableIdentifier' => 'd',
                4 => 'd'
            ]
        ];
        $this->assertTrue($success);
        $this->assertEquals($expected, $matches, 'The regular expression splitting the array apart does not work!');
    }

    /**
     * @return array
     */
    public function dataProviderValidArrayExpressionsBeginningAndEndingOnDigits()
    {
        return [
            [
                'expression' => '{baz: foo.bar1}',
                'expected' => [
                    0 => [
                        0 => 'baz: foo.bar1',
                        'ArrayPart' => 'baz: foo.bar1',
                        1 => 'baz: foo.bar1',
                        'Key' => 'baz',
                        2 => 'baz',
                        'QuotedString' => '',
                        3 => '',
                        'VariableIdentifier' => 'foo.bar1',
                        4 => 'foo.bar1'
                    ]
                ]
            ],
            [
                'expression' => '{"foo2": "bar2"}',
                'expected' => [
                    0 => [
                        0 => '"foo2": "bar2"',
                        'ArrayPart' => '"foo2": "bar2"',
                        1 => '"foo2": "bar2"',
                        'Key' => '"foo2"',
                        2 => '"foo2"',
                        'QuotedString' => '"bar2"',
                        3 => '"bar2"'
                    ]
                ]
            ],
            [
                'expression' => '{foo3: bar.baz3}',
                'expected' => [
                    0 => [
                        0 => 'foo3: bar.baz3',
                        'ArrayPart' => 'foo3: bar.baz3',
                        1 => 'foo3: bar.baz3',
                        'Key' => 'foo3',
                        2 => 'foo3',
                        'QuotedString' => '',
                        3 => '',
                        'VariableIdentifier' => 'bar.baz3',
                        4 => 'bar.baz3'
                    ]
                ]
            ],
            [
                'expression' => '{foo4: _4bar.-baz4}',
                'expected' => [
                    0 => [
                        0 => 'foo4: _4bar.-baz4',
                        'ArrayPart' => 'foo4: _4bar.-baz4',
                        1 => 'foo4: _4bar.-baz4',
                        'Key' => 'foo4',
                        2 => 'foo4',
                        'QuotedString' => '',
                        3 => '',
                        'VariableIdentifier' => '_4bar.-baz4',
                        4 => '_4bar.-baz4'
                    ]
                ]
            ],
            [
                'expression' => '{5foo5: -5bar}',
                'expected' => [
                    0 => [
                        0 => '5foo5: -5bar',
                        'ArrayPart' => '5foo5: -5bar',
                        1 => '5foo5: -5bar',
                        'Key' => '5foo5',
                        2 => '5foo5',
                        'QuotedString' => '',
                        3 => '',
                        'VariableIdentifier' => '-5bar',
                        4 => '-5bar'
                    ]
                ]
            ],
            [
                'expression' => '{foo6: 66}',
                'expected' => [
                    0 => [
                        0 => 'foo6: 66',
                        'ArrayPart' => 'foo6: 66',
                        1 => 'foo6: 66',
                        'Key' => 'foo6',
                        2 => 'foo6',
                        'QuotedString' => '',
                        3 => '',
                        'VariableIdentifier' => '',
                        4 => '',
                        'Number' => '66',
                        5 => '66'
                    ]
                ]
            ]
        ];
    }

    /**
     * @param string $expression
     * @param string $expected
     * @dataProvider dataProviderValidArrayExpressionsBeginningAndEndingOnDigits()
     * @test
     */
    public function SPLIT_PATTERN_SHORTHANDSYNTAX_ARRAY_PARTS_matchesKeysEndingInDigits($expression, $expected)
    {
        $pattern = Patterns::$SPLIT_PATTERN_SHORTHANDSYNTAX_ARRAY_PARTS;
        $success = preg_match_all($pattern, $expression, $matches, PREG_SET_ORDER) > 0;
        $this->assertTrue($success);
        $this->assertEquals(
            $expected,
            $matches,
            'The regular expression splitting the array apart does not work!'
        );
    }

    /**
     * @return array
     */
    public function dataProviderInvalidSPLIT_PATTERN_SHORTHANDSYNTAX_ARRAY_PARTS()
    {
        return [
            ['string' => '{"a\': b}'],
            ['string' => '{"": "bar"}'],
            ['string' => '{\'\': "bar"}'],
        ];
    }

    /**
     * @param string $string
     * @dataProvider dataProviderInvalidSPLIT_PATTERN_SHORTHANDSYNTAX_ARRAY_PARTS()
     * @test
     */
    public function SPLIT_PATTERN_SHORTHANDSYNTAX_ARRAY_PARTS_doesNotMatchInvalidSyntax($string)
    {
        $pattern = Patterns::$SPLIT_PATTERN_SHORTHANDSYNTAX_ARRAY_PARTS;
        $success = preg_match_all($pattern, $string, $matches, PREG_SET_ORDER) > 0;
        $this->assertFalse($success);
    }

    /**
     * Test the SCAN_PATTERN_CDATA which should detect <![CDATA[...]]> (with no leading or trailing spaces!)
     *
     * @test
     */
    public function testSCAN_PATTERN_CDATA()
    {
        $pattern = Patterns::$SCAN_PATTERN_CDATA;
        $this->assertEquals(preg_match($pattern, '<!-- Test -->'), 0, 'The SCAN_PATTERN_CDATA matches a comment, but it should not.');
        $this->assertEquals(preg_match($pattern, '<![CDATA[This is some ]]>'), 1, 'The SCAN_PATTERN_CDATA does not match a simple CDATA string.');
        $this->assertEquals(preg_match($pattern, '<![CDATA[This is<bla:test> some ]]>'), 1, 'The SCAN_PATTERN_CDATA does not match a CDATA string with tags inside..');
    }

    /**
     * Helper method which replaces NAMESPACE in the regular expression with the real namespace used.
     *
     * @param string $regularExpression The regular expression in which to replace NAMESPACE
     * @param array $namespace List of namespace identifiers.
     * @return string working regular expression with NAMESPACE replaced.
     */
    protected function insertNamespaceIntoRegularExpression($regularExpression, $namespace)
    {
        return str_replace('NAMESPACE', implode('|', $namespace), $regularExpression);
    }
}
