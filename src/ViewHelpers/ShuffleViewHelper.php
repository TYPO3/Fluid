<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * The ShuffleViewHelper shuffles elements from an array.
 * This ViewHelper uses PHP's :php:`shuffle()` function.
 *
 * Example
 * ========
 *
 * ::
 *
 *    <f:shuffle value="{0: '1', 1: '2', 2: '3'}" />
 *
 * .. code-block:: text
 *
 *    {0: '2', 1: '3', 2: '1'}
 */
final class ShuffleViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'array', 'An array');
    }

    /**
     * @return array The shuffled array
     */
    public function render(): array
    {
        $value = $this->arguments['value'] ?? $this->renderChildren();
        if ($value === null || !is_iterable($value)) {
            $givenType = get_debug_type($value);
            throw new \InvalidArgumentException(
                'The argument "value" was registered with type "array", but is of type "' .
                $givenType . '" in view helper "' . static::class . '".',
                1750881571,
            );
        }
        $value = iterator_to_array($value);

        shuffle($value);

        return $value;
    }
}
