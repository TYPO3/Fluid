<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Cache;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\StopCompilingChildrenException;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase;
use TYPO3Fluid\Fluid\ViewHelpers\Cache\StaticViewHelper;

/**
 * Testcase for StaticViewHelper
 */
class StaticViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @test
     */
    public function testRenderCallsRenderChildren(): void
    {
        $subject = $this->getMockBuilder(StaticViewHelper::class)->setMethods(['renderChildren'])->getMock();
        $subject->expects($this->once())->method('renderChildren')->willReturn('test');
        $this->assertEquals('test', $subject->render());
    }
}
