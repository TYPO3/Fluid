<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\ViewHelpers\IfViewHelper;

/**
 * Testcase for IfViewHelper
 */
class IfViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @test
     */
    public function viewHelperRendersThenChildIfConditionIsTrue(): void
    {
        $context = $this->getMockBuilder(RenderingContextInterface::class)->getMockForAbstractClass();
        $context->expects($this->once())->method('getViewHelperInvoker')->willReturn(new ViewHelperInvoker());
        $context->expects($this->once())->method('getViewHelperResolver')->willReturn(new ViewHelperResolver());
        $actualResult = IfViewHelper::renderStatic(['condition' => true, 'then' => 'THEN', 'else' => 'ELSE'], function(): void {}, $context);
        $this->assertEquals('THEN', $actualResult);
    }

    /**
     * @test
     */
    public function viewHelperRendersElseChildIfConditionIsFalse(): void
    {
        $context = $this->getMockBuilder(RenderingContextInterface::class)->getMockForAbstractClass();
        $context->expects($this->once())->method('getViewHelperInvoker')->willReturn(new ViewHelperInvoker());
        $context->expects($this->once())->method('getViewHelperResolver')->willReturn(new ViewHelperResolver());
        $actualResult = IfViewHelper::renderStatic(['condition' => false, 'then' => 'THEN', 'else' => 'ELSE'], function(): void {}, $context);
        $this->assertEquals('ELSE', $actualResult);
    }
}
