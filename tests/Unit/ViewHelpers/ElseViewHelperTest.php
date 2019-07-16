<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\ViewHelpers\ElseViewHelper;

/**
 * Testcase for ElseViewHelper
 */
class ElseViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @test
     */
    public function testInitializeArgumentsRegistersExpectedArguments(): void
    {
        $instance = $this->getMock(ElseViewHelper::class, ['registerArgument']);
        $instance->expects($this->at(0))->method('registerArgument')->with('if', 'boolean', $this->anything());
        $instance->initializeArguments();
    }

    /**
     * @test
     */
    public function renderRendersChildren(): void
    {
        $viewHelper = $this->getMock(ElseViewHelper::class, ['renderChildren']);

        $viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue('foo'));
        $actualResult = $viewHelper->render();
        $this->assertEquals('foo', $actualResult);
    }
}
