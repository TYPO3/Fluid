<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * The FloorViewHelper rounds down a float value to the next integer.
 * The ViewHelper mimics PHP's :php:`floor()` function.
 *
 * Examples
 * ========
 *
 * Value argument
 * --------------
 * ::
 *
 *    <f:floor value="123.456" />
 *
 * .. code-block:: text
 *
 *    123
 *
 * Tag content as value
 * --------------------
 * ::
 *
 *    <f:floor>123.456</f:floor>
 *
 * .. code-block:: text
 *
 *    123
 */
final class FloorViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'float', 'The number that should be rounded down');
    }

    public function render(): float
    {
        $value = $this->arguments['value'] ?? $this->renderChildren();
        return floor((float)$value);
    }
}
