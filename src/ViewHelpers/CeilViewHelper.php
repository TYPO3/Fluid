<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * The CeilViewHelper rounds up a float value to the next integer.
 * The ViewHelper mimics PHP's :php:`ceil()` function.
 *
 * Examples
 * ========
 *
 * Value argument
 * --------------
 * ::
 *
 *    <f:ceil value="123.456" />
 *
 * .. code-block:: text
 *
 *    124
 *
 * Tag content as value
 * --------------------
 * ::
 *
 *    <f:ceil>123.456</f:ceil>
 *
 * .. code-block:: text
 *
 *    124
 */
final class CeilViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'float', 'The number that should be rounded up');
    }

    public function render(): float
    {
        // Vermutlich erstmal noch den "main" rein mergen
        $value = $this->arguments['value'] ?? $this->renderChildren();
        return ceil((float)$value);
    }
}
