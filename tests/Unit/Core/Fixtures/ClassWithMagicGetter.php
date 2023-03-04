<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Fixtures;

class ClassWithMagicGetter
{
    public function __call($name, $arguments)
    {
        if ($name === 'getTest') {
            return 'test result';
        }
        return null;
    }
}
