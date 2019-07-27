<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for BooleanNode
 */
class BooleanNodeTest extends UnitTestCase
{
    /**
     * @var RenderingContextInterface
     */
    protected $renderingContext;

    /**
     * Setup fixture
     */
    public function setUp(): void
    {
        $this->renderingContext = new RenderingContextFixture();
    }

    /**
     * @param mixed $input
     * @param boolean $expected
     * @test
     * @dataProvider getStandardInputTypes
     */
    public function acceptsStandardTypesAsInput($input, bool $expected): void
    {
        $node = new BooleanNode($input);
        $this->assertEquals($expected, $node->evaluate($this->renderingContext));
    }

    /**
     * @return array
     */
    public function getStandardInputTypes(): array
    {
        return [
            [0, false],
            [1, true],
            [false, false],
            [true, true],
            [null, false],
            ['', false],
            ['0', false],
            ['1', true],
            [[1], true],
            [[0], true],
            [[false], true],
            [[null], true],
            [[], false],
        ];
    }

    /**
     * @test
     * @dataProvider getChildNodeTestValues
     * @param RenderingContextInterface $context
     * @param iterable $children
     * @param bool $expected
     */
    public function evaluatesChildNodesCorrectly(RenderingContextInterface $context, iterable $children, bool $expected): void
    {
        $subject = new BooleanNode();
        foreach ($children as $child) {
            $subject->addChild($child);
        }
        $this->assertSame($expected, $subject->evaluate($context));
    }

    public function getChildNodeTestValues(): array
    {
        $context = new RenderingContextFixture();
        $context->setVariableProvider(new StandardVariableProvider([
            'foo' => 'filled',
            'bar' => 'also filled',
            'baz' => new \DateTime('now'),
            'false' => false,
        ]));
        return [
            #/*
            'no children means false' => [
                $context,
                [],
                false,
            ],
            'single hardcoded "true" means true' => [
                $context,
                [new TextNode('true')],
                true,
            ],
            'single hardcoded "false" means false' => [
                $context,
                [new TextNode('false')],
                false,
            ],
            'string comparison with quoted part treats as string' => [
                $context,
                [(new RootNode())->addChild(new TextNode('false'))->setQuoted(true), new TextNode('!='), new TextNode('false')],
                true,
            ],
            'single hardcoded true-ish loose string comparison means true' => [
                $context,
                [new TextNode('1'), new TextNode('=='), new BooleanNode(true)],
                true,
            ],
            'single child object accessor with true-ish value' => [
                $context,
                [new ObjectAccessorNode('foo')],
                true,
            ],
            'multiple child object accessor with true-ish values with "OR" groups' => [
                $context,
                [new ObjectAccessorNode('foo'), new TextNode('||'), new ObjectAccessorNode('bar'), new TextNode('||'), new ObjectAccessorNode('baz')],
                true,
            ],
            #*/
            'multiple child object accessor with true-ish values with "AND" groups' => [
                $context,
                [new ObjectAccessorNode('foo'), new TextNode('&&'), new ObjectAccessorNode('bar'), new TextNode('&&'), new ObjectAccessorNode('baz')],
                true,
            ],
            'multiple child object accessor with true-ish values with "AND" groups with last false' => [
                $context,
                [new ObjectAccessorNode('foo'), new TextNode('&&'), new ObjectAccessorNode('bar'), new TextNode('&&'), new ObjectAccessorNode('false')],
                false,
            ],
        ];
    }

}
