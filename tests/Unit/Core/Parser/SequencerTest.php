<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\Configuration;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SequencingException;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\ViewHelpers\CViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\Format\RawViewHelper;


/**
 * Testcase for SequencedTemplateParser.
 */
class SequencerTest extends UnitTestCase
{
    /**
     * @test
     * @dataProvider getErrorExpectations
     * @param string $template
     * @param RootNode $expectedRootNode
     */
    public function failsWithExpectedSequencingException(string $template, RenderingContextInterface $context, int $expectedExceptionCode)
    {
        $this->setExpectedException(Exception::class, '', $expectedExceptionCode);
        $parser = new TemplateParser();
        $context->setTemplateParser($parser);
        $parser->setRenderingContext($context);
        $parser->parse($template)->getRootNode();
    }

    public function getErrorExpectations(): array
    {
        $context = $this->createContext();

        return [
            'missing end curly brace causes unexpected token in inline context' => [
                '{foo',
                $context,
                1557838506
            ],
            'unclosed inactive tag' => [
                '<foo',
                $context,
                1557700786
            ],
            'unclosed active tag' => [
                '<f:c',
                $context,
                1557700786
            ],
            'unclosed quoted attribute in active tag' => [
                '<f:c s="',
                $context,
                1557700793
            ],
            'unclosed array attribute with curly braces in active tag' => [
                '<f:c a="{foo:',
                $context,
                1557838506
            ],
            'unclosed array attribute with square brackets in active tag' => [
                '<f:c a="[foo:',
                $context,
                1557748574
            ],
            'unsupported argument before equals sign in active tag' => [
                '<f:c foo="no" />',
                $context,
                1558298976
            ],
            'quoted value without key in active tag' => [
                '<f:c "foo" />',
                $context,
                1558952412
            ],
            'mismatched open and close active tag' => [
                '<f:c></f:comment>',
                $context,
                1557700789
            ],
            'unresolved ViewHelper as active tag' => [
                '<f:notfound />',
                $context,
                1407060572
            ],
            'unresolved ViewHelper as inline' => [
                '{f:notfound()}',
                $context,
                1407060572
            ],
            'unsupported argument in ViewHelper as inline' => [
                '{f:c(unsupported: 1)}',
                $context,
                1558298976
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getSequenceExpectations
     * @param string $template
     * @param RootNode $expectedRootNode
     */
    public function createsExpectedNodeStructure(string $template, RenderingContextInterface $context, RootNode $expectedRootNode)
    {
        $parser = new TemplateParser();
        $context->setTemplateParser($parser);
        $parser->setRenderingContext($context);
        $node = $parser->parse($template)->getRootNode();
        $this->assertNodeEquals($node, $expectedRootNode);
    }

    public function getSequenceExpectations(): array
    {
        $state = $this->createState();
        $context = $this->createContext();

        return [

            /* TAGS */
            'simple open+close non-active tag in root context' => [
                '<tag>x</tag>',
                $context,
                (new RootNode())->addChildNode(new TextNode('<tag>x</tag>')),
            ],
            'simple open+close non-active tag with hardcoded attribute in root context' => [
                '<tag attr="foo">x</tag>',
                $context,
                (new RootNode())->addChildNode(new TextNode('<tag attr="foo">x</tag>')),
            ],
            'simple open+close non-active tag with object accessor attribute value in root context' => [
                '<tag attr="{string}">x</tag>',
                $context,
                (new RootNode())->addChildNode(new TextNode('<tag attr="'))->addChildNode(new ObjectAccessorNode('string'))->addChildNode(new TextNode('">x</tag>')),
            ],
            'simple open+close non-active tag with value-less object accessor attribute in root context' => [
                '<tag {string}>x</tag>',
                $context,
                (new RootNode())->addChildNode(new TextNode('<tag '))->addChildNode(new ObjectAccessorNode('string'))->addChildNode(new TextNode('>x</tag>')),
            ],
            'simple open+close non-active tag with object accessor attribute name in root context' => [
                '<tag {string}="foo">x</tag>',
                $context,
                (new RootNode())->addChildNode(new TextNode('<tag '))->addChildNode(new ObjectAccessorNode('string'))->addChildNode(new TextNode('="foo">x</tag>')),
            ],
            'simple open+close non-active tag with object accessor attribute name and value in root context' => [
                '<tag {string}="{string}">x</tag>',
                $context,
                (new RootNode())->addChildNode(new TextNode('<tag '))->addChildNode(new ObjectAccessorNode('string'))->addChildNode(new TextNode('="'))->addChildNode(new ObjectAccessorNode('string'))->addChildNode(new TextNode('">x</tag>')),
            ],
            'simple self-closed non-active, space before closing tag, tag in root context' => [
                '<tag />',
                $context,
                (new RootNode())->addChildNode(new TextNode('<tag />')),
            ],
            'simple self-closed non-active, no space before closing tag, tag in root context' => [
                '<tag/>',
                $context,
                (new RootNode())->addChildNode(new TextNode('<tag/>')),
            ],
            'simple open+close active tag in root context' => [
                '<f:c>x</f:c>',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->addChildNode(new TextNode('x'))->postParse([], $state)),
            ],
            'simple open+close active tag with string parameter in root context' => [
                '<f:c s="foo">x</f:c>',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->addChildNode(new TextNode('x'))->postParse(['s' => 'foo'], $state)),
            ],
            'simple self-closed active tag, space before tag close, in root context' => [
                '<f:c />',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse([], $state)),
            ],
            'simple self-closed active tag, no space before tag close, in root context' => [
                '<f:c/>',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse([], $state)),
            ],
            'simple self-closed active tag with hardcoded string argument in root context' => [
                '<f:c s="foo" />',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['s' => 'foo'], $state)),
            ],
            'simple self-closed active tag with hardcoded string and integer arguments in root context' => [
                '<f:c s="foo" i="1" />',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['s' => 'foo', 'i' => 1], $state)),
            ],
            'simple self-closed active tag with object accessor string argument in root context' => [
                '<f:c s="{string}" />',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['s' => new ObjectAccessorNode('string')], $state)),
            ],
            'simple self-closed active tag with object accessor string argument with string before accessor in root context' => [
                '<f:c s="before {string}" />',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['s' => (new RootNode())->addChildNode(new TextNode('before '))->addChildNode(new ObjectAccessorNode('string'))], $state)),
            ],
            'simple self-closed active tag with object accessor string argument with string after accessor in root context' => [
                '<f:c s="{string} after" />',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['s' => (new RootNode())->addChildNode(new ObjectAccessorNode('string'))->addChildNode(new TextNode(' after'))], $state)),
                #(new RootNode())->addChildNode((new CViewHelper())->postParse(['s' => new ObjectAccessorNode('string')], $state)),
            ],
            'simple self-closed active tag with object accessor string argument with string before and after accessor in root context' => [
                '<f:c s="before {string} after" />',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['s' => (new RootNode())->addChildNode(new TextNode('before '))->addChildNode(new ObjectAccessorNode('string'))->addChildNode(new TextNode(' after'))], $state)),
            ],
            'simple self-closed active tag with string argument containing different quotes inside in root context' => [
                '<f:c s="before \'quoted\' after" />',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['s' => 'before \'quoted\' after'], $state)),
            ],
            'simple self-closed active tag with string argument containing escaped same type quotes inside in root context' => [
                '<f:c s="before \\"quoted\\" after" />',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['s' => 'before "quoted" after'], $state)),
            ],
            'simple self-closed active tag with array argument with single unquoted element in root context' => [
                '<f:c a="{foo:bar}" />',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['a' => new ArrayNode(['foo' => new ObjectAccessorNode('bar')])], $state)),
            ],
            'simple self-closed active tag with array argument with two unquoted elements with comma and space in root context' => [
                '<f:c a="{foo:bar, baz:honk}" />',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['a' => new ArrayNode(['foo' => new ObjectAccessorNode('bar'), 'baz' => new ObjectAccessorNode('honk')])], $state)),
            ],
            'simple self-closed active tag with array argument with two unquoted elements with comma without space in root context' => [
                '<f:c a="{foo:bar,baz:honk}" />',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['a' => new ArrayNode(['foo' => new ObjectAccessorNode('bar'), 'baz' => new ObjectAccessorNode('honk')])], $state)),
            ],
            'simple self-closed active tag with array argument with single double-quoted element (no escaping) in root context' => [
                '<f:c a="{foo:"bar"}" />',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['a' => new ArrayNode(['foo' => 'bar'])], $state)),
            ],
            'simple self-closed active tag with array argument with single single-quoted element (no escaping) in root context' => [
                '<f:c a="{foo:\'bar\'}" />',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['a' => new ArrayNode(['foo' => 'bar'])], $state)),
            ],
            'simple self-closed active tag with array argument with single integer element in root context' => [
                '<f:c a="{foo:1}" />',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['a' => new ArrayNode(['foo' => 1])], $state)),
            ],
            'simple self-closed active tag with array argument with double-quoted key and single integer element in root context' => [
                '<f:c a="{"foo":1}" />',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['a' => new ArrayNode(['foo' => 1])], $state)),
            ],
            'simple self-closed active tag with square brackets array argument with double-quoted key and single integer element in root context' => [
                '<f:c a="["foo":1]" />',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['a' => new ArrayNode(['foo' => 1])], $state)),
            ],
            'simple self-closed active tag with square brackets array argument with sub-array element with quoted key and object accessor value in root context' => [
                '<f:c a="[foo:[baz:\'foo\']]" />',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['a' => new ArrayNode(['foo' => new ArrayNode(['baz' => 'foo'])])], $state)),
            ],
            'simple self-closed active tag with string argument with square bracket start not at first position in root context' => [
                '<f:c s="my [a:b]" />',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['s' => 'my [a:b]'], $state)),
            ],
            'simple self-closed active tag with value-less ECMA literal shorthand argument in root context' => [
                '<f:c s/>',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['s' => new ObjectAccessorNode('s')], $state)),
            ],
            'simple self-closed active tag with two value-less ECMA literal shorthand arguments in root context' => [
                '<f:c s i/>',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['s' => new ObjectAccessorNode('s'), 'i' => new ObjectAccessorNode('i')], $state)),
            ],
            'simple open + close active tag with value-less ECMA literal shorthand argument in root context' => [
                '<f:c s>x</f:c>',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['s' => new ObjectAccessorNode('s')], $state)->addChildNode(new TextNode('x'))),
            ],
            'simple open + close active tag with two value-less ECMA literal shorthand arguments in root context' => [
                '<f:c s i>x</f:c>',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['s' => new ObjectAccessorNode('s'), 'i' => new ObjectAccessorNode('i')], $state)->addChildNode(new TextNode('x'))),
            ],

            /* INLINE */
            'simple inline in root context' => [
                '{foo}',
                $context,
                (new RootNode())->addChildNode(new ObjectAccessorNode('foo')),
            ],
            'simple inline with text before in root context' => [
                'before {foo}',
                $context,
                (new RootNode())->addChildNode(new TextNode('before '))->addChildNode(new ObjectAccessorNode('foo')),
            ],
            'simple inline with text after in root context' => [
                '{foo} after',
                $context,
                (new RootNode())->addChildNode(new ObjectAccessorNode('foo'))->addChildNode(new TextNode(' after')),
            ],
            'simple inline with text before and after in root context' => [
                'before {foo} after',
                $context,
                (new RootNode())->addChildNode(new TextNode('before '))->addChildNode(new ObjectAccessorNode('foo'))->addChildNode(new TextNode(' after')),
            ],
            'escaped inline with text before and after in root context' => [
                'before \\{foo} after',
                $context,
                (new RootNode())->addChildNode(new TextNode('before {foo} after')),
            ],
            'simple inline ViewHelper without arguments in root context' => [
                '{f:c()}',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse([], $state)),
            ],
            'simple inline ViewHelper with single hardcoded integer argument in root context' => [
                '{f:c(i: 1)}',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['i' => 1], $state)),
            ],
            'simple inline ViewHelper with single hardcoded integer argument using tag attribute syntax in root context' => [
                '{f:c(i="1")}',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['i' => 1], $state)),
            ],
            'simple inline ViewHelper with two hardcoded arguments using tag attribute syntax without commas in root context' => [
                '{f:c(i="1" s="foo")}',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['i' => 1, 's' => 'foo'], $state)),
            ],
            'simple inline ViewHelper with two hardcoded arguments using tag attribute syntax with comma in root context' => [
                '{f:c(i="1", s="foo")}',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['i' => 1, 's' => 'foo'], $state)),
            ],
            'simple inline ViewHelper with two hardcoded arguments using tag attribute syntax with comma and tailing comma in root context' => [
                '{f:c(i="1", s="foo",)}',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['i' => 1, 's' => 'foo'], $state)),
            ],
            'simple inline ViewHelper with square brackets array argument using tag attribute syntax and array value using tag attribute syntax in root context' => [
                '{f:c(a="[foo="bar"]")}',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['a' => new ArrayNode(['foo' => 'bar'])], $state)),
            ],
            'simple inline ViewHelper with square brackets array argument with implied numeric keys with comma in root context' => [
                '{f:c(a="[foo, bar]")}',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['a' => new ArrayNode([new ObjectAccessorNode('foo'), new ObjectAccessorNode('bar')])], $state)),
            ],
            'simple inline ViewHelper with square brackets array argument with implied numeric keys without comma in root context' => [
                '{f:c(a="[foo bar]")}',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['a' => new ArrayNode([new ObjectAccessorNode('foo'), new ObjectAccessorNode('bar')])], $state)),
            ],
            'simple inline ViewHelper with curly braces array argument with explicit numeric keys in root context' => [
                '{f:c(a="{0: foo, 1: bar}")}',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['a' => new ArrayNode([new ObjectAccessorNode('foo'), new ObjectAccessorNode('bar')])], $state)),
            ],
            'simple inline ViewHelper with curly braces array argument with redundant escapes in root context' => [
                '{f:c(a: {0: \\\'foo\\\'})}',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['a' => new ArrayNode(['foo'])], $state)),
            ],
            'simple inline ViewHelper with curly brackets array argument using tag attribute syntax and array value using tag attribute syntax in root context' => [
                '{f:c(a="{foo="bar"}")}',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['a' => new ArrayNode(['foo' => 'bar'])], $state)),
            ],
            'simple inline ViewHelper with value pipe in root context' => [
                '{string | f:c()}',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse([], $state)->addChildNode(new ObjectAccessorNode('string'))),
            ],
            'simple inline ViewHelper with value pipe to ViewHelper alias in root context' => [
                '{string | raw}',
                $context,
                (new RootNode())->addChildNode((new RawViewHelper())->postParse([], $state)->addChildNode(new ObjectAccessorNode('string'))),
            ],

            # ARRAY PASS
            'inline ViewHelper with single-item quoted static key and value associative array in curly braces piped value in root context' => [
                '{{"foo": "bar"} | f:format.raw()}',
                $context,
                (new RootNode())->addChildNode((new RawViewHelper())->addChildNode(new ArrayNode(['foo' => 'bar']))),
            ],

            # PROTECTED INLINE
            'complex inline syntax not detected as Fluid in root context' => [
                '{string with < raw || something}',
                $context,
                (new RootNode())->addChildNode(new TextNode('{string with < raw || something}')),
            ],
            'likely JS syntax not detected as Fluid in root context' => [
                '{"object": "something"}',
                $context,
                (new RootNode())->addChildNode(new TextNode('{"object": "something"}')),
            ],
            'likely CSS syntax not detected as Fluid in root context' => [
                'something { background-color: #000000; }',
                $context,
                (new RootNode())->addChildNode(new TextNode('something { background-color: #000000; }')),
            ],
            'likely JS syntax not detected as Fluid in inactive attribute value' => [
                '<tag prop="if(x.y[f.z].v){w.l.h=(this.o[this.s].v);}" />',
                $context,
                (new RootNode())->addChildNode(new TextNode('<tag prop="if(x.y[f.z].v){w.l.h=(this.o[this.s].v);}" />')),
            ],
        ];
    }

    protected function createContext(): RenderingContextInterface
    {
        $variableProvider = $this->getMockBuilder(VariableProviderInterface::class)->getMock();
        $variableProvider->expects($this->any())->method('get')->with('s')->willReturn('I am a shorthand string');
        $variableProvider->expects($this->any())->method('get')->with('i')->willReturn(321);
        $variableProvider->expects($this->any())->method('get')->with('string')->willReturn('I am a string');
        $variableProvider->expects($this->any())->method('get')->with('integer')->willReturn(42);
        $variableProvider->expects($this->any())->method('get')->with('numericArray')->willReturn(['foo', 'bar']);
        $variableProvider->expects($this->any())->method('get')->with('associativeArray')->willReturn(['foo' => 'bar']);
        $variableProvider->expects($this->atLeastOnce())->method('getScopeCopy')->willReturnSelf();
        $viewHelperResolver = new ViewHelperResolver();
        $viewHelperResolver->addViewHelperAlias('raw', 'f', 'format.raw');
        $parserConfiguration = new Configuration();
        $parserConfiguration->setUseSequencer(true);
        $context = $this->getMockBuilder(RenderingContextInterface::class)->getMock();
        $context->setViewHelperResolver($viewHelperResolver);
        $context->expects($this->any())->method('buildParserConfiguration')->willReturn($parserConfiguration);
        $context->expects($this->any())->method('getViewHelperResolver')->willReturn($viewHelperResolver);
        $context->expects($this->any())->method('getVariableProvider')->willReturn($variableProvider);
        $context->expects($this->any())->method('getExpressionNodeTypes')->willReturn([]);
        $context->expects($this->any())->method('getTemplateProcessors')->willReturn([]);
        return $context;
    }

    protected function createState(): ParsingState
    {
        $state = new ParsingState();
        return $state;
    }

    protected function assertNodeEquals(NodeInterface $subject, NodeInterface $expected, string $path = '')
    {
        $this->assertEquals($expected, $subject, 'Node types not as expected at path: ' . $path, .0, 1);
        $children = $subject->getChildNodes();
        $expectedChildren = $expected->getChildNodes();
        if (count($children) !== count($expectedChildren)) {
            $this->fail('Nodes have an unequal number of child nodes. Got ' . count($children) . ', expected ' . count($expectedChildren) . '. Path: ' . $path);
        }
        foreach ($expectedChildren as $index => $expectedChild) {
            $this->assertNodeEquals($children[$index], $expectedChild, $path . get_class($subject) . '.child@' . $index);
        }
        if ($subject instanceof ViewHelperInterface) {
            $expectedArguments = $expected->getParsedArguments();
            foreach ($subject->getParsedArguments() as $name => $argument) {
                if ($argument instanceof NodeInterface && $expectedArguments[$name] instanceof NodeInterface) {
                    $this->assertNodeEquals($argument, $expectedArguments[$name], $path . '.argument@' . $name);
                } else {
                    $this->assertSame($expectedArguments[$name], $argument, 'Arguments at path ' . $path . '.argument@' . $name . ' did not match');
                }
                unset($expectedArguments[$name]);
            }
            if (!empty($expectedArguments)) {
                $this->fail('ViewHelper did not recevie expected arguments: ' . var_export($expectedArguments, true));
            }
        } elseif ($subject instanceof ObjectAccessorNode) {
            $this->assertSame($expected->getObjectPath(), $subject->getObjectPath(), 'ObjectAccessors do not match at path ' . $path);
        } elseif ($subject instanceof TextNode) {
            $this->assertSame($expected->getText(), $subject->getText(), 'TextNodes do not match at path ' . $path);
        } elseif ($subject instanceof ArrayNode) {
            $this->assertEquals($expected->getInternalArray(), $subject->getInternalArray(), 'Arrays do not match at path ' . $path);
        }
        foreach ($expectedChildren as $index => $expectedChild) {
            $child = $children[$index];
            $this->assertNodeEquals($child, $expectedChild, get_class($subject) . '.child@' . $index);
        }
    }
}