<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * The FirstViewHelper returns the first item of an array.
 *
 * Example
 * ========

 * ::
 *
 *    <f:first value="{0: 'first', 1: 'second'}" />
 *
 * .. code-block:: text
 *
 *    first
 */
final class FirstViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'array', '');
    }

    public function render(): mixed
    {
        $value = $this->arguments['value'] ?? $this->renderChildren();
        if ($value === null || !is_iterable($value)) {
            $givenType = get_debug_type($value);
            throw new \InvalidArgumentException(
                'The argument "value" was registered with type "array", but is of type "' .
                $givenType . '" in view helper "' . static::class . '".',
                1712220569,
            );
        }
        $value = iterator_to_array($value);
        return array_shift($value);
    }
}
