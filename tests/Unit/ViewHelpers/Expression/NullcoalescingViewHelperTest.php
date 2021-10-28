<?php

namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Expression;


/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestCase;
use TYPO3Fluid\Fluid\ViewHelpers\Expression\NullcoalescingViewHelper;

/**
 * Class TernaryExpressionNodeTest
 */
class NullcoalescingViewHelperTest extends ViewHelperBaseTestCase
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
        $result = NullcoalescingViewHelper::evaluateExpression($renderingContext, $expression, []);
        $this->assertEquals($expected, $result);
    }

    public function getStandardTestValues(): array
    {

        $context = new RenderingContextFixture();
        foreach(['a' => 'a', 'b' => 'b', 'c' => 'c'] as $key => $value) {
            $context->getVariableProvider()->add($key, $value);
        }

        return [
            'value not null, default integer' => ['a', $context, ['a' => 'a', 'b' => 1]],
            'value null, default to 1' => [1, $context, ['d' => null, 'b' => 1]],
            'value not null, default other value' => ['a', $context, ['a' => 'a', 'b' => 'b']],
            'value null, default to other value' => ['b', $context, ['d' => null, 'b' => 'b']],
            'value not null, default other value with additional fallback object' => ['a', $context, ['a' => 'a', 'b' => 'b', 'c' => 'c']],
            'value null, default other value with additional fallback object' => ['b', $context, ['d' => null, 'b' => 'b', 'c' => 'c']],
            'value null, default other value also null, with additional fallback object' => ['c', $context, ['d' => null, ['e' => null, 'c' => 'c']]],
            'value null, default other value also null, with additional fallback string (single quote)' => ['test', $context, ['d' => null, 'e' => null], [new TextNode('\'test\'')]],
            'value null, default other value also null, with additional fallback string (double quote)' => ['test', $context, ['d' => null, 'e' => null], [new TextNode('"test"')]],
        ];
    }
}
