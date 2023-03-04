<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various;

/**
 * Dummy object to test Viewhelper behavior on objects with a __toString method
 */
class UserWithToString extends UserWithoutToString
{
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
