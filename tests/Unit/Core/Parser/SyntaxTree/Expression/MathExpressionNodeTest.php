<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree\Expression;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\MathExpressionNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;

final class MathExpressionNodeTest extends TestCase
{
    public static function getEvaluateExpressionTestValues(): array
    {
        return [
            ['1 + 1', [], 2],
            ['1 +
                   1', [], 2],
            [' 1 + 2', [], 3],
            ['1 + 2 ', [], 3],
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
        // evaluateExpression() will really only be called if the regular expression pattern matches in the first
        // place. So it doesn't make sense to test everything in isolation here. For now, we test the pattern manually
        // to at least make sure that the test case will actually work in templates.
        // @todo there should really be API for this, like canInterpret() or similar
        self::assertEquals(1, preg_match(MathExpressionNode::$detectionExpression, '{' . $expression . '}'));

        $renderingContext = new RenderingContext();
        $renderingContext->setVariableProvider(new StandardVariableProvider($variables));
        $result = MathExpressionNode::evaluateExpression($renderingContext, $expression, []);
        self::assertEquals($expected, $result);
    }
}
