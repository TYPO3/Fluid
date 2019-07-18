<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree\Expression;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ExpressionException;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\TernaryExpressionNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * Class TernaryExpressionNodeTest
 */
class TernaryExpressionNodeTest extends UnitTestCase
{
    /**
     * @dataProvider getTernaryExpressionDetection
     * @param array $parts
     * @param bool $expected
     */
    public function testTernaryExpressionDetection(array $parts, bool $expected): void
    {
        $this->assertSame($expected, TernaryExpressionNode::matches($parts));
    }

    /**
     * @return array
     */
    public function getTernaryExpressionDetection(): array
    {
        return [
            [['true', '?', 'foo', ':', 'bar'], true],
            [['true', '?', ':', 'foo'], true],
            [['true', '?', ':', 'foo', 'wrong'], false],
            [['!true', '?', 'foo', ':', 'bar'], true],
            [['!true', '?', ':', 'bar'], true],
        ];
    }

    /**
     * @dataProvider getEvaluateExpressionTestValues
     * @param array $parts
     * @param array $variables
     * @param mixed $expected
     */
    public function testEvaluateExpression(array $parts, array $variables, $expected): void
    {
        $view = new TemplateView();
        $renderingContext = new RenderingContext($view);
        $renderingContext->setVariableProvider(new StandardVariableProvider($variables));
        $result = (new TernaryExpressionNode($parts))->evaluate($renderingContext);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getEvaluateExpressionTestValues(): array
    {
        return [
            [['fooIsFalse', '?', 2, ':', 3], ['fooIsFalse' => false], 3],
            [['fooIsTrue', '?', 2, ':', 3], ['fooIsTrue' => true], 2],
            [['fooIsTrue', '?', ':', 3], ['fooIsTrue' => true], true],
            [['fooIsFalse', '?', ':', 3], ['fooIsFalse' => false], 3],
        ];
    }
}
