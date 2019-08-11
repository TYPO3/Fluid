<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper\Traits;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollection;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Class CompileWithRenderStaticTest
 */
class CompileWithRenderStaticTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testRenderCallsRenderStatic(): void
    {
        $instance = $this->getMockBuilder(CompileWithRenderStatic::class)->setMethods(['getArguments', 'buildRenderChildrenClosure', 'renderStatic'])->getMockForTrait();
        $instance->expects($this->once())->method('getArguments')->willReturn((new ArgumentCollection())->setRenderingContext(new RenderingContextFixture()));
        $instance->expects($this->once())->method('buildRenderChildrenClosure')->willReturn(function() {});
        $instance->render();
    }
}
