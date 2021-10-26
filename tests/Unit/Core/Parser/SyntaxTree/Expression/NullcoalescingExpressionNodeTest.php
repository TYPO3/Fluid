<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\NullcoalescingExpressionNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Class TernaryExpressionNodeTest
 */
class NullcoalescingExpressionNodeTest extends UnitTestCase
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
        $result = NullcoalescingExpressionNode::evaluateExpression($renderingContext, $expression, []);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getEvaluateExpressionTestValues()
    {
        return [
            ['{a ?? 1}', ['a' => 'a'], 'a'],
            ['{a ?? 1}', ['a' => null], 1],
            ['{a ?? b}', ['a' => 'a', 'b' => 'b'], 'a'],
            ['{a ?? b}', ['a' => null, 'b' => 'b'], 'b'],
            ['{a ?? b ?? c}', ['a' => '1', 'b' => '2', 'c' => '3'], '1'],
            ['{a ?? b ?? c}', ['a' => '1', 'b' => null, 'c' => '3'], '1'],
            ['{a ?? b ?? c}', ['a' => null, 'b' => '2', 'c' => '3'], '2'],
            ['{a ?? b ?? c}', ['a' => null, 'b' => null, 'c' => '3'], '3'],
            ['{a ?? b ?? c}', ['a' => 'd', 'b' => 'e', 'c' => 'f'], 'd'],
            ['{a ?? b ?? c}', ['a' => 'd', 'b' => null, 'c' => 'f'], 'd'],
            ['{a ?? b ?? c}', ['a' => null, 'b' => 'e', 'c' => 'f'], 'e'],
            ['{a ?? b ?? c}', ['a' => null, 'b' => null, 'c' => 'f'], 'f'],
            ['{a ?? b ?? c}', ['a' => null, 'b' => null, 'c' => null], null],
            ['{a ?? b ?? \'test\'}', ['a' => null, 'b' => null], 'test'],
            ['{a ?? b ?? "test"}', ['a' => null, 'b' => null], 'test'],

        ];
    }
}
