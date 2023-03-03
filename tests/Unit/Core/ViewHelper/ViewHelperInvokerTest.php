<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Fixtures\TestViewHelper;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

class ViewHelperInvokerTest extends UnitTestCase
{
    /**
     * @param string $viewHelperClassName
     * @param array $arguments
     * @param mixed $expectedOutput
     * @param string|null $expectedException
     * @test
     * @dataProvider getInvocationTestValues
     */
    public function testInvokeViewHelper($viewHelperClassName, array $arguments, $expectedOutput, $expectedException)
    {
        $invoker = new ViewHelperInvoker();
        $renderingContext = new RenderingContext();
        if ($expectedException) {
            $this->setExpectedException($expectedException);
        }
        $result = $invoker->invoke($viewHelperClassName, $arguments, $renderingContext);
        self::assertEquals($expectedOutput, $result);
    }

    public static function getInvocationTestValues(): array
    {
        return [
            [TestViewHelper::class, ['param1' => 'foo', 'param2' => ['bar']], 'foo', null],
            [TestViewHelper::class, ['param1' => 'foo', 'param2' => ['bar'], 'add1' => 'baz', 'add2' => 'zap'], 'foo', null],
        ];
    }
}
