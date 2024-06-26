<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\ViewHelper\Traits;

use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\Exception;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\Tests\Functional\Core\ViewHelper\Traits\Fixtures\CompileWithContentArgumentAndRenderStaticTestTraitViewHelper;

final class CompileWithContentArgumentAndRenderStaticTest extends AbstractFunctionalTestCase
{
    #[Test]
    public function resolveContentArgumentNameThrowsExceptionIfNoArgumentsAvailable(): void
    {
        $this->expectException(Exception::class);
        $instance = new CompileWithContentArgumentAndRenderStaticTestTraitViewHelper();
        $instance->resolveContentArgumentName();
    }
}
