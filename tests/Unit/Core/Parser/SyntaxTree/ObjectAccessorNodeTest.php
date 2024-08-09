<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;

final class ObjectAccessorNodeTest extends TestCase
{
    public static function getEvaluateTestValues(): array
    {
        return [
            [['foo' => 'bar'], 'foo.notaproperty', null],
            [['foo' => 'bar'], '_all', ['foo' => 'bar']],
            [['foo' => 'bar'], 'foo', 'bar'],
            [['foo' => ['bar' => 'test']], 'foo.bar', 'test'],
            [['foo' => ['bar' => 'test'], 'dynamic' => 'bar'], 'foo.{dynamic}', 'test'],
            [['foo' => ['bar' => 'test'], 'dynamic' => ['sub' => 'bar']], 'foo.{dynamic.sub}', 'test'],
            [['foo' => ['bar' => 'test'], 'dynamic' => ['sub' => 'bar'], 'baz' => 'sub'], 'foo.{dynamic.{baz}}', 'test'],
            [[], 'true', true],
            [[], 'false', false],
            [[], 'null', null],
        ];
    }

    /**
     * @param mixed $expected
     */
    #[DataProvider('getEvaluateTestValues')]
    #[Test]
    public function testEvaluateGetsExpectedValue(array $variables, string $path, $expected): void
    {
        $node = new ObjectAccessorNode($path);
        $renderingContext = $this->createMock(RenderingContextInterface::class);
        $variableContainer = new StandardVariableProvider($variables);
        $renderingContext->expects(self::any())->method('getVariableProvider')->willReturn($variableContainer);
        $value = $node->evaluate($renderingContext);
        self::assertSame($expected, $value);
    }

    #[Test]
    public function testEvaluatedUsesVariableProviderGetByPath(): void
    {
        $node = new ObjectAccessorNode('foo.bar');
        $renderingContext = $this->createMock(RenderingContextInterface::class);
        $variableContainer = $this->createMock(StandardVariableProvider::class);
        $variableContainer->expects(self::once())->method('getByPath')->with('foo.bar')->willReturn('foo');
        $renderingContext->expects(self::any())->method('getVariableProvider')->willReturn($variableContainer);
        $value = $node->evaluate($renderingContext);
        self::assertEquals('foo', $value);
    }
}
