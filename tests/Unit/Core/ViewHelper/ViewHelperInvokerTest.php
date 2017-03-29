<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Fixtures\TestViewHelper;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * Class ViewHelperInvokerTest
 */
class ViewHelperInvokerTest extends UnitTestCase
{

    /**
     * @param string $viewHelperClassName
     * @param array $arguments
     * @param mixed $expectedOutput
     * @param string|NULL $expectedException
     * @test
     * @dataProvider getInvocationTestValues
     */
    public function testInvokeViewHelper($viewHelperClassName, array $arguments, $expectedOutput, $expectedException)
    {
        $view = new TemplateView();
        $resolver = new ViewHelperResolver();
        $invoker = new ViewHelperInvoker($resolver);
        $renderingContext = new RenderingContext($view);
        if ($expectedException) {
            $this->setExpectedException($expectedException);
        }
        $result = $invoker->invoke($viewHelperClassName, $arguments, $renderingContext);
        $this->assertEquals($expectedOutput, $result);
    }

    /**
     * @return array
     */
    public function getInvocationTestValues()
    {
        return [
            [TestViewHelper::class, ['param1' => 'foo', 'param2' => ['bar']], 'foo', null],
            [TestViewHelper::class, ['param1' => 'foo', 'param2' => ['bar'], 'add1' => 'baz', 'add2' => 'zap'], 'foo', null],
        ];
    }
}
