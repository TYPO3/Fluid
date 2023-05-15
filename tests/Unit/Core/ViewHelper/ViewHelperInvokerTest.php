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

final class ViewHelperInvokerTest extends UnitTestCase
{
    public static function getInvocationTestValues(): array
    {
        return [
            [TestViewHelper::class, ['param1' => 'foo', 'param2' => ['bar']], 'foo'],
            [TestViewHelper::class, ['param1' => 'foo', 'param2' => ['bar'], 'add1' => 'baz', 'add2' => 'zap'], 'foo'],
        ];
    }

    /**
     * @test
     * @dataProvider getInvocationTestValues
     */
    public function testInvokeViewHelper(string $viewHelperClassName, array $arguments, string $expectedOutput): void
    {
        $invoker = new ViewHelperInvoker();
        $renderingContext = new RenderingContext();
        $result = $invoker->invoke($viewHelperClassName, $arguments, $renderingContext);
        self::assertEquals($expectedOutput, $result);
    }
}
