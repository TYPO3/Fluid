<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests;

/**
 * This interface defines the methods provided by BaseTestCase::getAccessibleMock().
 * Do not implement this interface in own classes.
 *
 * @internal
 */
interface AccessibleObjectInterface
{
    /**
     * Calls $methodName with further $methodArguments and returns its return value.
     *
     * @param string $methodName name of method to call, must not be empty
     * @param mixed ...$methodArguments additional arguments for method
     * @return mixed the return value from the method $methodName
     */
    public function _call(string $methodName, ...$methodArguments);

    /**
     * Sets the value of a property.
     *
     * @param string $propertyName name of property to set value for, must not be empty
     * @param mixed $value the new value for the property defined in $propertyName
     */
    public function _set(string $propertyName, $value): void;

    /**
     * Gets the value of the given property.
     *
     * @param string $propertyName name of property to return value of, must not be empty
     * @return mixed the value of the property $propertyName
     */
    public function _get(string $propertyName);
}
