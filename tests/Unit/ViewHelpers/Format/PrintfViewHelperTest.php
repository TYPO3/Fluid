<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Format;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestCase;

/**
 * Test for \TYPO3Fluid\Fluid\ViewHelpers\Format\PrintfViewHelper
 */
class PrintfViewHelperTest extends ViewHelperBaseTestCase
{
    public function getStandardTestValues(): array
    {
        return [
            'prints format string without arguments' => ['format', null, ['value' => 'format']],
            'prints format string with arguments' => ['formatted insert string 5 integer', null, ['value' => 'formatted %s string %d integer', 'arguments' => ['insert', 5]]],
        ];
    }
}
