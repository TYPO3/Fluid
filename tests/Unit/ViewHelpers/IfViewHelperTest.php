<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\ViewHelpers\IfViewHelper;

/**
 * Testcase for IfViewHelper
 */
class IfViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @test
     */
    public function viewHelperRendersThenChildIfConditionIsTrue()
    {
        $context = $this->getMockBuilder(RenderingContextInterface::class)->getMockForAbstractClass();
        $actualResult = IfViewHelper::renderStatic(['condition' => true, 'then' => 'THEN', 'else' => 'ELSE'], function() {}, $context);
        $this->assertEquals('THEN', $actualResult);
    }

    /**
     * @test
     */
    public function viewHelperRendersElseChildIfConditionIsFalse()
    {
        $context = $this->getMockBuilder(RenderingContextInterface::class)->getMockForAbstractClass();
        $actualResult = IfViewHelper::renderStatic(['condition' => false, 'then' => 'THEN', 'else' => 'ELSE'], function() {}, $context);
        $this->assertEquals('ELSE', $actualResult);
    }
}
