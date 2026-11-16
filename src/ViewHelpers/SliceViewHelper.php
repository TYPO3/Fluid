<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\InvalidArgumentValueException;

/**
 * The SliceViewHelper extracts a part of an array or a string, defined by
 * an offset and a length.
 *
 * For arrays, this ViewHelper mimicks PHP's :php:`array_slice()` function.
 * String keys are always preserved, numeric keys only if ``preserveKeys``
 * is set.
 *
 * For strings, it mimicks PHP's :php:`mb_substr()` function, so offset and
 * length count characters, not bytes.
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
 *    <f:slice value="{items}" length="3" />
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
 *    <f:slice value="{items}" offset="1" />
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
 *    <f:slice value="{items}" offset="-2" />
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
 *    <f:for each="{items -> f:slice(length: 3)}" as="item">
 *        <span>{item}</span>
 *    </f:for>
 *
 *
 * Part of a string
 * ----------------
 *
 * ::
 *
 *    <f:slice value="Hello World" offset="6" />
 *
 * .. code-block:: text
 *
 *    World
 *
 * @api
 * @see https://docs.typo3.org/permalink/fluid:typo3fluid-fluid-slice
 */
final class SliceViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'mixed', 'The array or string to extract a part from');
        $this->registerArgument('offset', 'integer', 'Position of the first item or character. If negative, the part starts that far from the end.', false, 0);
        $this->registerArgument('length', 'integer', 'Maximum number of items or characters. If negative, the part stops that far from the end. If omitted, everything from the offset on is returned.');
        $this->registerArgument('preserveKeys', 'boolean', 'If true, numeric keys of an array are preserved. String keys are always preserved. Has no effect on strings.', false, false);
    }

    /**
     * @return array|string The extracted part of the array or string
     */
    public function render(): array|string
    {
        $value = $this->arguments['value'] ?? $this->renderChildren();
        if (is_string($value)) {
            return mb_substr($value, $this->arguments['offset'], $this->arguments['length']);
        }
        if ($value === null || !is_iterable($value)) {
            $givenType = get_debug_type($value);
            throw new InvalidArgumentValueException(
                'The argument "value" must be an array or a string, but is of type "'
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
