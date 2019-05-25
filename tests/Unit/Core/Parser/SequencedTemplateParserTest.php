<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SequencedTemplateParser;
use TYPO3Fluid\Fluid\Core\Parser\Splitter;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\PostponedViewHelperNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\ViewHelpers\CommentViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\CViewHelper;


/**
 * Testcase for SequencedTemplateParser.
 */
class SequencedTemplateParserTest extends UnitTestCase
{
    /**
     * @test
     * @dataProvider getSequenceExpectations
     * @param string $template
     * @param RootNode $expectedRootNode
     */
    public function createsExpectedNodeStructure(string $template, RenderingContextInterface $context, RootNode $expectedRootNode)
    {
        $parser = new SequencedTemplateParser();
        $context->setTemplateParser($parser);
        $parser->setRenderingContext($context);
        $node = $parser->parse($template)->getRootNode();
        $this->assertNodeEquals($node, $expectedRootNode);
    }

    public function getSequenceExpectations(): array
    {
        $variableProvider = $this->getMockBuilder(VariableProviderInterface::class)->getMock();
        $variableProvider->expects($this->atLeastOnce())->method('getScopeCopy')->willReturnSelf();
        $viewHelperResolver = new ViewHelperResolver();
        $context = $this->getMockBuilder(RenderingContextInterface::class)->getMock();
        $context->setViewHelperResolver($viewHelperResolver);
        $context->expects($this->any())->method('getViewHelperResolver')->willReturn($viewHelperResolver);
        $context->expects($this->any())->method('getVariableProvider')->willReturn($variableProvider);
        $context->expects($this->any())->method('getExpressionNodeTypes')->willReturn([]);
        $context->expects($this->any())->method('getTemplateProcessors')->willReturn([]);
        $state = new ParsingState();

        return [
            'simple inline in root context' => [
                '{foo}',
                $context,
                (new RootNode())->addChildNode(new ObjectAccessorNode('foo')),
            ],
            'simple open+close non-active tag in root context' => [
                '<tag>x</tag>',
                $context,
                (new RootNode())->addChildNode(new TextNode('<tag>x</tag>')),
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
            'simple self-closed active tag with string argument in root context' => [
                '<f:c s="foo" />',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['s' => 'foo'], $state)),
            ],
            'simple self-closed active tag with array argument with single unquoted element in root context' => [
                '<f:c a="{foo:bar}" />',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['a' => new ArrayNode(['foo' => new ObjectAccessorNode('bar')])], $state)),
            ],
            'simple self-closed active tag with array argument with two unquoted elements with comma and space in root context' => [
                '<f:c a="{foo:bar, baz:honk}" />',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['a' => new ArrayNode(['foo' => new ObjectAccessorNode('bar'), ['baz' => new ObjectAccessorNode('honk')]])], $state)),
            ],
            'simple self-closed active tag with array argument with two unquoted elements with comma without space in root context' => [
                '<f:c a="{foo:bar,baz:honk}" />',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['a' => new ArrayNode(['foo' => new ObjectAccessorNode('bar'), ['baz' => new ObjectAccessorNode('honk')]])], $state)),
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
            'simple self-closed active tag with string argument with square bracket start not at first position in root context' => [
                '<f:c s="my [a:b]" />',
                $context,
                (new RootNode())->addChildNode((new CViewHelper())->postParse(['s' => 'my [a:b]'], $state)),
            ],
        ];
    }

    protected function assertNodeEquals(NodeInterface $subject, NodeInterface $expected, string $path = '')
    {
        $this->assertEquals(get_class($subject), get_class($expected), 'Node type ' . get_class($subject) . ' does not match expected ' . get_class($expected) . ', path: ' . $path);
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
                if ($argument instanceof NodeInterface) {
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
            $this->assertSame($subject->getObjectPath(), $expected->getObjectPath(), 'ObjectAccessors do not match at path ' . $path);
        } elseif ($subject instanceof TextNode) {
            $this->assertSame($subject->getText(), $expected->getText(), 'TextNodes do not match at path ' . $path);
        }
        foreach ($expectedChildren as $index => $expectedChild) {
            $child = $children[$index];
            $this->assertNodeEquals($child, $expectedChild, get_class($subject) . '.child@' . $index);
        }
    }
}