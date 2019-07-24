<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper\Traits;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollection;
use TYPO3Fluid\Fluid\Component\Argument\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Class CompileWithContentArgumentAndRenderStaticTest
 */
class CompileWithContentArgumentAndRenderStaticTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testGetContentArgumentNameThrowsExceptionIfNoArgumentsAvailable(): void
    {
        $instance = $this->getMockBuilder(CompileWithContentArgumentAndRenderStatic::class)->setMethods(['getArguments'])->getMockForTrait();
        $instance->expects($this->once())->method('getArguments')->willReturn(
            new ArgumentCollection(
                [
                    'arg' => new ArgumentDefinition('arg', 'string', 'Arg', true)
                ]
            )
        );
        $this->setExpectedException(Exception::class);
        $method = new \ReflectionMethod($instance, 'resolveContentArgumentName');
        $method->setAccessible(true);
        $method->invoke($instance);
    }
}
