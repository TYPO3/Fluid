<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Dummy object to test Viewhelper behavior on objects with a toArray() method
 */
class UserWithToArray extends UserWithToString
{

    /**
     * @return array
     */
    function toArray()
    {
        return ['name' => $this->name];
    }
}
