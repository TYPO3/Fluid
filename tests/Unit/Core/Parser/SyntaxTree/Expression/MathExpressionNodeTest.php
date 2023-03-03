<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree\Expression;

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\MathExpressionNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

class MathExpressionNodeTest extends UnitTestCase
{

    /**
     * @dataProvider getEvaluateExpressionTestValues
     * @param string $expression
     * @param array $variables
     * @param mixed $expected
     */
    public function testEvaluateExpression($expression, array $variables, $expected)
    {
        $renderingContext = new RenderingContext();
        $renderingContext->setVariableProvider(new StandardVariableProvider($variables));
        $result = MathExpressionNode::evaluateExpression($renderingContext, $expression, []);
        self::assertEquals($expected, $result);
    }

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
            ['4 ^ 2', [], 16],
            ['a + 1', ['a' => 1], 2],
            ['1 + b', ['b' => 1], 2],
            ['a + b', ['a' => 1, 'b' => 1], 2],
        ];
    }
}
