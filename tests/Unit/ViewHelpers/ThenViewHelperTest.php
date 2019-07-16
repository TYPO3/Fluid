<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\ViewHelpers\ThenViewHelper;

/**
 * Testcase for ElseViewHelper
 */
class ThenViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @test
     */
    public function renderRendersChildren(): void
    {
        $viewHelper = $this->getMock(ThenViewHelper::class, ['renderChildren']);

        $viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue('foo'));
        $actualResult = $viewHelper->render();
        $this->assertEquals('foo', $actualResult);
    }
}
