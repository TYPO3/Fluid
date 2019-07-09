<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Tests\Functional\BaseFunctionalTestCase;

/**
 * Class CycleTest
 */
class CycleTest extends BaseFunctionalTestCase
{

    /**
     * @return array
     */
    public function getTemplateCodeFixturesAndExpectations(): array
    {
        return [
            'Cycles values in array' => [
                '<f:for each="{items}" as="item"><f:cycle values="{cycles}" as="cycled">{cycled}</f:cycle></f:for>',
                ['items' => [0, 1, 2, 3], 'cycles' => ['a', 'b']],
                ['abab'],
                ['aa', 'bb']
            ],
        ];
    }
}
