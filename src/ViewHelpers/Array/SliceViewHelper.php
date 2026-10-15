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
 * The SliceViewHelper extracts a part of an array, defined by an offset
 * and a length. String keys are always preserved, numeric keys only if
 * ``preserveKeys`` is set.
 *
 * This ViewHelper mimicks PHP's :php:`array_slice()` function.
 *
 * Iterators like generators can yield the same key more than once. Only the
 * last item per key is used, like with PHP's :php:`iterator_to_array()`
 * function.
 *
 * Examples
 * ========
 *
 * Given ``items = ['a', 'b', 'c', 'd', 'e']``.
 *
 * First items of an array
 * -----------------------
 *
 * ::
 *
 *    <f:array.slice value="{items}" length="3" />
 *
 * .. code-block:: php
 *
 *    ['a', 'b', 'c']
 *
 *
 * Skip the first item
 * -------------------
 *
 * ::
 *
 *    <f:array.slice value="{items}" offset="1" />
 *
 * .. code-block:: php
 *
 *    ['b', 'c', 'd', 'e']
 *
 *
 * Last items of an array
 * ----------------------
 *
 * ::
 *
 *    <f:array.slice value="{items}" offset="-2" />
 *
 * .. code-block:: php
 *
 *    ['d', 'e']
 *
 *
 * Render only the first items
 * ---------------------------
 *
 * ::
 *
 *    <f:for each="{items -> f:array.slice(length: 3)}" as="item">
 *        <span>{item}</span>
 *    </f:for>
 *
 * @api
 * @see https://docs.typo3.org/permalink/fluid:typo3fluid-fluid-array-slice
 */
final class SliceViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'array', 'The array to extract a part from');
        $this->registerArgument('offset', 'integer', 'Position of the first item. If negative, the part starts that far from the end of the array.', false, 0);
        $this->registerArgument('length', 'integer', 'Maximum number of items. If negative, the part stops that far from the end of the array. If omitted, all items from the offset on are returned.');
        $this->registerArgument('preserveKeys', 'boolean', 'If true, numeric keys are preserved. String keys are always preserved.', false, false);
    }

    /**
     * @return array The extracted part of the array
     */
    public function render(): array
    {
        $value = $this->arguments['value'] ?? $this->renderChildren();
        if ($value === null || !is_iterable($value)) {
            $givenType = get_debug_type($value);
            throw new InvalidArgumentValueException(
                'The argument "value" was registered with type "array", but is of type "'
                . $givenType . '" in view helper "' . static::class . '".',
                1792005170,
            );
        }
        return array_slice(
            iterator_to_array($value),
            $this->arguments['offset'],
            $this->arguments['length'],
            $this->arguments['preserveKeys'],
        );
    }
}
