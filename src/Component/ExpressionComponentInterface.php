<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Component;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Expression-capable Component Interface
 *
 * Allows a Component to carry a matching method which receives an array
 * of exploded expression parts and must return true or false based on
 * whether or not the component is capable of rendering the expression.
 */
interface ExpressionComponentInterface extends ComponentInterface
{
    /**
     * Must return TRUE if the parts (split to array by inline
     * tokens and spaces) matches the type of expression.
     *
     * @param array $parts
     * @return bool
     */
    public static function matches(array $parts): bool;
}
