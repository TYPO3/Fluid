<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\ViewHelper\Traits;

use TYPO3Fluid\Fluid\Core\Exception;
use TYPO3Fluid\Fluid\Tests\Functional\Core\ViewHelper\Traits\Fixtures\CompileWithContentArgumentAndRenderStaticTestTraitViewHelper;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

class CompileWithContentArgumentAndRenderStaticTest extends UnitTestCase
{
    /**
     * @test
     */
    public function resolveContentArgumentNameThrowsExceptionIfNoArgumentsAvailable()
    {
        $this->setExpectedException(Exception::class);
        $instance = new CompileWithContentArgumentAndRenderStaticTestTraitViewHelper();
        $instance->resolveContentArgumentName();
    }
}
