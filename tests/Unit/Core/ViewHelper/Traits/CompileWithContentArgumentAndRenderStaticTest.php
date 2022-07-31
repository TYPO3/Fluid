<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper\Traits;

use TYPO3Fluid\Fluid\Core\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
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
    public function testGetContentArgumentNameThrowsExceptionIfNoArgumentsAvailable()
    {
        $instance = $this->getMockBuilder(CompileWithContentArgumentAndRenderStatic::class)->setMethods(['prepareArguments'])->getMockForTrait();
        $instance->expects(self::once())->method('prepareArguments')->willReturn([
            'arg' => new ArgumentDefinition('arg', 'string', 'Arg', true)
        ]);
        $this->setExpectedException(Exception::class);
        $method = new \ReflectionMethod($instance, 'resolveContentArgumentName');
        $method->setAccessible(true);
        $method->invoke($instance);
    }
}
