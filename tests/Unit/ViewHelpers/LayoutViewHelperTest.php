<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Testcase for LayoutViewHelper
 */
class LayoutViewHelperTest extends ViewHelperBaseTestCase
{
    public function getStandardTestValues(): array
    {
        return [
            'returns null on execution' => [null, null, ['name' => 'layout']],
        ];
    }
}
