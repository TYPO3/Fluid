<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers\Cache;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\ViewHelpers\Cache\DisableViewHelper;

class DisableViewHelperTest extends AbstractFunctionalTestCase
{
    /**
     * @test
     */
    public function viewHelperCanBeInstantiated()
    {
        $subject = new DisableViewHelper();
        self::assertInstanceOf(AbstractViewHelper::class, $subject);
    }
}
