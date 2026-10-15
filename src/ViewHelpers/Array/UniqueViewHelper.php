<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers\Array;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\InvalidArgumentValueException;

/**
 * The UniqueViewHelper removes duplicate values from an array. The first
 * occurrence of each value is kept.
 *
 * Values are compared loosely (``==``), so ``1`` and ``'1'`` are considered
 * duplicates, and objects are considered duplicates if they are of the same
 * class and have equal properties. Strings are compared case-sensitively.
 *
 * This ViewHelper is similar to PHP's :php:`array_unique()` function, but
 * also works with arrays of arrays or objects.
 *
 * Iterators like generators can yield the same key more than once. All of
 * their items are used, unless ``preserveKeys`` is set: then only the last
 * item per key is used, like with PHP's :php:`iterator_to_array()` function.
 *
 * Examples
 * ========
 *
 * Given ``tags = ['php', 'fluid', 'php', 'typo3', 'fluid']``.
 *
 * Remove duplicate values
 * -----------------------
 *
 * ::
 *
 *    <f:array.unique value="{tags}" />
 *
 * .. code-block:: php
 *
 *    ['php', 'fluid', 'typo3']
 *
 *
 * Preserve the original array keys
 * --------------------------------
 *
 * ::
 *
 *    <f:array.unique value="{tags}" preserveKeys="true" />
 *
 * .. code-block:: php
 *
 *    [0 => 'php', 1 => 'fluid', 3 => 'typo3']
 *
 *
 * Output the unique values as a list
 * ----------------------------------
 *
 * ::
 *
 *    {tags -> f:array.unique() -> f:join(separator: ', ')}
 *
 * .. code-block:: text
 *
 *    php, fluid, typo3
 *
 * @api
 * @see https://docs.typo3.org/permalink/fluid:typo3fluid-fluid-array-unique
 */
final class UniqueViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'array', 'The array to remove duplicate values from');
        $this->registerArgument('preserveKeys', 'boolean', 'If true, the keys of the original array are preserved. Otherwise the result is reindexed numerically.', false, false);
    }

    /**
     * @return array The array without duplicate values
     */
    public function render(): array
    {
        $value = $this->arguments['value'] ?? $this->renderChildren();
        if ($value === null || !is_iterable($value)) {
            $givenType = get_debug_type($value);
            throw new InvalidArgumentValueException(
                'The argument "value" was registered with type "array", but is of type "'
                . $givenType . '" in view helper "' . static::class . '".',
                1792005180,
            );
        }
        $unique = [];
        foreach (iterator_to_array($value, $this->arguments['preserveKeys']) as $key => $item) {
            if (!in_array($item, $unique)) {
                $unique[$key] = $item;
            }
        }
        return $this->arguments['preserveKeys'] ? $unique : array_values($unique);
    }
}
