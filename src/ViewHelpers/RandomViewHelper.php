<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * The RandomViewHelper returns a random element from an array.
 *
 * Example
 * =======
 *
 * ::
 *
 *      <f:random value="{0: '1', 1: '2', 2: '3'}" />
 *
 * Output::
 *
 *      2
 */
final class RandomViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'array', 'An array');
    }

    /**
     * @return mixed The lowest value
     */
    public function render(): mixed
    {
        $value = $this->arguments['value'] ?? $this->renderChildren();
        if ($value === null || !is_iterable($value)) {
            $givenType = get_debug_type($value);
            throw new \InvalidArgumentException(
                'The argument "value" was registered with type "array", but is of type "'
                . $givenType . '" in view helper "' . static::class . '".',
                1756181371,
            );
        }
        $value = iterator_to_array($value);
        return empty($value) ? $value : $value[array_rand($value)];
    }
}
