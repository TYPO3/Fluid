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
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithToString;
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
     * @test
     */
    public function flattenWithExtractEvaluatesSingleChildNodeToBoolean(): void
    {
        $subject = new BooleanNode();
        $subject->addChild(new TextNode('true'));
        $this->assertSame(true, $subject->flatten(true));
    }

    /**
     * @test
     */
    public function flattenWithExtractReturnsSelfWithMoreThanOneChildNode(): void
    {
        $subject = new BooleanNode();
        $subject->addChild(new TextNode('true'))->addChild(new TextNode('true'));
        $this->assertSame($subject, $subject->flatten(true));
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
            'object1' => new UserWithToString('user'),
            'object2' => new UserWithToString('user'),
            'emptyArrayObject' => new \ArrayObject([]),
            'arrayObject' => new \ArrayObject(['foo', 'bar']),
        ]));
        return [
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
            'incorrect number of parts always true' => [
                $context,
                [new TextNode('false'), new TextNode('false')],
                true,
            ],
            'negated object accessor' => [
                $context,
                [new TextNode('!'), new ObjectAccessorNode('foo')],
                false,
            ],
            'negated object accessor and negated object accessor' => [
                $context,
                [(new BooleanNode())->addChild(new TextNode('!'))->addChild(new ObjectAccessorNode('foo'))->addChild(new TextNode('AND'))->addChild(new TextNode('!'))->addChild(new ObjectAccessorNode('foo'))],
                false,
            ],
            'lowercase or' => [
                $context,
                [new TextNode('false'), new TextNode('or'), new TextNode('true')],
                true,
            ],
            'uppercase OR' => [
                $context,
                [new TextNode('false'), new TextNode('OR'), new TextNode('true')],
                true,
            ],
            'lowercase and' => [
                $context,
                [new TextNode('true'), new TextNode('and'), new TextNode('false')],
                false,
            ],
            'uppercase AND' => [
                $context,
                [new TextNode('true'), new TextNode('AND'), new TextNode('false')],
                false,
            ],
            'string &&' => [
                $context,
                [new TextNode('false'), new TextNode('&&'), new TextNode('true')],
                false,
            ],
            'string XOR true/true is false' => [
                $context,
                [new TextNode('3'), new TextNode('XOR'), new TextNode('2')],
                false,
            ],
            'string XOR false/false is false' => [
                $context,
                [new TextNode('0'), new TextNode('XOR'), new TextNode('0')],
                false,
            ],
            'string xor false/true is true' => [
                $context,
                [new TextNode('0'), new TextNode('xor'), new TextNode('2')],
                true,
            ],
            'bitwise or' => [
                $context,
                [new TextNode('3'), new TextNode('|'), new TextNode('2')],
                true,
            ],
            'bitwise and' => [
                $context,
                [new TextNode('3'), new TextNode('&'), new TextNode('2')],
                true,
            ],
            'greater than' => [
                $context,
                [new TextNode('3'), new TextNode('>'), new TextNode('2')],
                true,
            ],
            'greater than or equal' => [
                $context,
                [new TextNode('3'), new TextNode('>='), new TextNode('2')],
                true,
            ],
            'less than' => [
                $context,
                [new TextNode('3'), new TextNode('<'), new TextNode('2')],
                false,
            ],
            'less than or equal' => [
                $context,
                [new TextNode('3'), new TextNode('<='), new TextNode('2')],
                false,
            ],
            'strictly not equal' => [
                $context,
                [new TextNode('3'), new TextNode('!=='), new TextNode('3.0')],
                true,
            ],
            'not equal' => [
                $context,
                [new TextNode('3'), new TextNode('!='), new TextNode('3.0')],
                false,
            ],
            'strictly equal' => [
                $context,
                [new TextNode('3'), new TextNode('==='), new TextNode('3')],
                true,
            ],
            'equal' => [
                $context,
                [new TextNode('3'), new TextNode('=='), new TextNode('3.0')],
                true,
            ],
            'modulo' => [
                $context,
                [new TextNode('3'), new TextNode('%'), new TextNode('3')],
                false,
            ],
            'modulo with non-numeric' => [
                $context,
                [new TextNode('3'), new TextNode('%'), new TextNode('not-numeric')],
                false,
            ],
            'string comparison with quoted part treats as string' => [
                $context,
                [(new RootNode())->setQuoted(true)->addChild(new TextNode('false')), new TextNode('!='), new TextNode('false')],
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
            'countable false if empty' => [
                $context,
                [new ObjectAccessorNode('emptyArrayObject')],
                false,
            ],
            'countable true if not empty' => [
                $context,
                [new ObjectAccessorNode('arrayObject')],
                true,
            ],
            'comparing objects equal forces strict' => [
                $context,
                [new ObjectAccessorNode('object1'), new TextNode('=='), new ObjectAccessorNode('object2')],
                false,
            ],
            'comparing objects not equal forces strict' => [
                $context,
                [new ObjectAccessorNode('object1'), new TextNode('!='), new ObjectAccessorNode('object2')],
                true,
            ],
            'multiple child object accessor with true-ish values with "OR" groups' => [
                $context,
                [new ObjectAccessorNode('foo'), new TextNode('||'), new ObjectAccessorNode('bar'), new TextNode('||'), new ObjectAccessorNode('baz')],
                true,
            ],
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
