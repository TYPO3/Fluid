<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree\Expression;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\MathExpressionNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

final class MathExpressionNodeTest extends UnitTestCase
{
    public static function getEvaluateExpressionTestValues(): array
    {
        return [
            ['1 gabbagabbahey 1', [], 0],
            ['1 + 1', [], 2],
            ['1 +
                   1', [], 2],
            ['2 - 1', [], 1],
            ['2 % 4', [], 2],
            ['2 * 4', [], 8],
            ['4 / 2', [], 2],
            ['4 / 0.5', [], 8],
            ['4 / 0', [], 0],
            ['4 / 0.0', [], 0],
            ['4 / b', ['b' => '0'], 0],
            ['4 / b', ['b' => 0.0], 0],
            ['4 / b', ['b' => '0.5'], 8],
            ['4 / b', ['b' => ''], 0],
            ['4 / b', ['b' => 'string input'], 0],
            ['4 ^ 2', [], 16],
            ['a + 1', ['a' => 1], 2],
            ['1 + b', ['b' => 1], 2],
            ['a + b', ['a' => 1, 'b' => 1], 2],
            ['a + b', ['a' => '1', 'b' => '1'], 2],
        ];
    }

    /**
     * @param mixed $expected
     */
    #[DataProvider('getEvaluateExpressionTestValues')]
    #[Test]
    public function testEvaluateExpression(string $expression, array $variables, $expected): void
    {
        $renderingContext = new RenderingContext();
        $renderingContext->setVariableProvider(new StandardVariableProvider($variables));
        $result = MathExpressionNode::evaluateExpression($renderingContext, $expression, []);
        self::assertEquals($expected, $result);
    }
}
