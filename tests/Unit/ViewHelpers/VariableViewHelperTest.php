<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\ViewHelpers\VariableViewHelper;

/**
 * Testcase for VariableViewHelper
 */
class VariableViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @test
     */
    public function registersArguments()
    {
        $subject = new VariableViewHelper();
        $subject->initializeArguments();
        $this->assertAttributeNotEmpty('argumentDefinitions', $subject);
    }

    /**
     * @test
     */
    public function assignsVariableInVariableProvider()
    {
        $variableProvider = $this->getMockBuilder(StandardVariableProvider::class)->setMethods(['add'])->getMock();
        $variableProvider->expects($this->once())->method('add')->with('name', 'value');
        $renderingContext = new RenderingContextFixture();
        $renderingContext->setVariableProvider($variableProvider);
        VariableViewHelper::renderStatic(['name' => 'name', 'value' => null], function() { return 'value'; }, $renderingContext);
    }

}
