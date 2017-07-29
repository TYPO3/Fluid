<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Compiler;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\NodeConverter;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\EscapingNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ExpressionNodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\TernaryExpressionNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NumericNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Class NodeConverterTest
 */
class NodeConverterTest extends UnitTestCase
{

    /**
     * @test
     */
    public function testSetVariableCounter()
    {
        $instance = new NodeConverter(new TemplateCompiler());
        $instance->setVariableCounter(10);
        $this->assertAttributeEquals(10, 'variableCounter', $instance);
    }

    /**
     * @test
     * @dataProvider getConvertMethodCallTestValues
     * @param NodeInterface $node
     * @param string $expected
     */
    public function testConvertCallsExpectedMethod(NodeInterface $node, $expected)
    {
        $instance = $this->getMock(NodeConverter::class, [$expected], [], '', false);
        $instance->expects($this->once())->method($expected);
        $instance->convert($node);
    }

    /**
     * @return array
     */
    public function getConvertMethodCallTestValues()
    {
        return [
            [$this->getMock(TextNode::class, [], [], '', false), 'convertTextNode'],
            [$this->getMock(ExpressionNodeInterface::class), 'convertExpressionNode'],
            [$this->getMock(NumericNode::class, [], [], '', false), 'convertNumericNode'],
            [$this->getMock(ViewHelperNode::class, [], [], '', false), 'convertViewHelperNode'],
            [$this->getMock(ObjectAccessorNode::class, [], [], '', false), 'convertObjectAccessorNode'],
            [$this->getMock(ArrayNode::class, [], [], '', false), 'convertArrayNode'],
            [$this->getMock(RootNode::class, [], [], '', false), 'convertListOfSubNodes'],
            [$this->getMock(BooleanNode::class, [], [], '', false), 'convertBooleanNode'],
            [$this->getMock(EscapingNode::class, [], [], '', false), 'convertEscapingNode'],
        ];
    }

    /**
     * @test
     * @dataProvider getConvertTestValues
     * @param NodeInterface $node
     * @param string $expected
     */
    public function testConvert(NodeInterface $node, $expected)
    {
        $instance = new NodeConverter(new TemplateCompiler());
        $result = $instance->convert($node);
        $this->assertEquals($expected, $result['execution']);
    }

    /**
     * @return array
     */
    public function getConvertTestValues()
    {
        $treeBooleanRoot = new RootNode();
        $treeBooleanRoot->addChildNode(new TextNode('1'));
        $treeBooleanRoot->addChildNode(new TextNode('!='));
        $treeBooleanRoot->addChildNode(new TextNode('2'));
        $treeBoolean = new BooleanNode($treeBooleanRoot);
        $simpleRoot = new RootNode();
        $simpleRoot->addChildNode(new TextNode('foobar'));
        $multiRoot = new RootNode();
        $multiRoot->addChildNode(new TextNode('foo'));
        $multiRoot->addChildNode(new TextNode('bar'));
        $multiRoot->addChildNode(new TextNode('baz'));
        return [
            [
                new ObjectAccessorNode('_all'),
                '$renderingContext->getVariableProvider()->getAll()'
            ],
            [
                new ObjectAccessorNode('foo.bar'),
                '$renderingContext->getVariableProvider()->getByPath(\'foo.bar\', $array0)'
            ],
            [
                new ObjectAccessorNode('foo.bar', ['array', 'array']),
                'isset($renderingContext->getVariableProvider()[\'foo\'][\'bar\']) ? $renderingContext->getVariableProvider()[\'foo\'][\'bar\'] : NULL'
            ],
            [
                new BooleanNode(new TextNode('TRUE')),
                'TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode::convertToBoolean(
					$expression1(
						TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode::gatherContext($renderingContext, $array0)
					),
					$renderingContext
				)'
            ],
            [
                new BooleanNode(new TextNode('1 = 1')),
                'TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode::convertToBoolean(
					$expression1(
						TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode::gatherContext($renderingContext, $array0)
					),
					$renderingContext
				)'
            ],
            [
                $treeBoolean,
                'TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode::convertToBoolean(
					$expression1(
						TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode::gatherContext($renderingContext, $array0)
					),
					$renderingContext
				)'
            ],
            [
                new TernaryExpressionNode('1 ? 2 : 3', [1, 2, 3]),
                '$ternaryExpression1(TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\TernaryExpressionNode::gatherContext($renderingContext, $array0[1]), $renderingContext)'
            ],
            [
                new EscapingNode(new TextNode('foo')),
                'call_user_func_array( function ($var) { return (is_string($var) || (is_object($var) && method_exists($var, \'__toString\')) ? htmlspecialchars((string) $var, ENT_QUOTES) : $var); }, [\'foo\'])'
            ],
            [
                new ViewHelperNode(
                    new RenderingContextFixture(),
                    'f',
                    'render',
                    ['section' => new TextNode('test'), 'partial' => 'test'],
                    new ParsingState()
                ),
                'TYPO3Fluid\Fluid\ViewHelpers\RenderViewHelper::renderStatic($arguments0, $renderChildrenClosure1, $renderingContext)'
            ],
            [$simpleRoot, '\'foobar\''],
            [$multiRoot, '$output0'],
            [new TextNode('test'), '\'test\''],
            [new NumericNode('3'), '3'],
            [new NumericNode('4.5'), '4.5'],
            [new ArrayNode(['foo', 'bar']), '$array0'],
            [new ArrayNode([0, new TextNode('test'), new ArrayNode(['foo', 'bar'])]), '$array0'],
            [$this->getMockBuilder(NodeInterface::class)->getMockForAbstractClass(), '']
        ];
    }
}
