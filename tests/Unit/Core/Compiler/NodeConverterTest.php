<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Compiler;

use TYPO3Fluid\Fluid\Core\Compiler\NodeConverter;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\EscapingNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\TernaryExpressionNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NumericNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

class NodeConverterTest extends UnitTestCase
{
    /**
     * @test
     */
    public function variableNameReturnsIncrementedName(): void
    {
        $subject = new NodeConverter(new TemplateCompiler());
        $subject->setVariableCounter(10);
        self::assertSame('$test10', $subject->variableName('test'));
        self::assertSame('$test11', $subject->variableName('test'));
    }

    public static function convertReturnsExpectedExecutionDataProvider(): array
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
                '$renderingContext->getVariableProvider()->getByPath(\'foo.bar\')'
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
                    new RenderingContext(),
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
        ];
    }

    /**
     * @test
     * @dataProvider convertReturnsExpectedExecutionDataProvider
     */
    public function convertReturnsExpectedExecution(NodeInterface $node, string $expected): void
    {
        $subject = new NodeConverter(new TemplateCompiler());
        $result = $subject->convert($node);
        self::assertEquals($expected, $result['execution']);
    }

    /**
     * @test
     */
    public function convertReturnsEmptyExecutionWithNodeOnlyImplementingNodeInterface(): void
    {
        $subject = new NodeConverter(new TemplateCompiler());
        $result = $subject->convert($this->createMock(NodeInterface::class));
        self::assertEquals('', $result['execution']);
    }
}
