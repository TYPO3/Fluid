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
 * The ReverseViewHelper returns an array with its items in reverse order.
 * String keys are always preserved, numeric keys only if ``preserveKeys``
 * is set.
 *
 * This ViewHelper mimicks PHP's :php:`array_reverse()` function.
 *
 * Iterators like generators can yield the same key more than once. Only the
 * last item per key is used, like with PHP's :php:`iterator_to_array()`
 * function.
 *
 * Examples
 * ========
 *
 * Reverse an array
 * ----------------
 *
 * Given ``items = ['a', 'b', 'c']``:
 *
 * ::
 *
 *    <f:array.reverse value="{items}" />
 *
 * .. code-block:: php
 *
 *    ['c', 'b', 'a']
 *
 *
 * Preserve numeric keys
 * ---------------------
 *
 * ::
 *
 *    <f:array.reverse value="{items}" preserveKeys="true" />
 *
 * .. code-block:: php
 *
 *    [2 => 'c', 1 => 'b', 0 => 'a']
 *
 *
 * Output the items in reverse order
 * ---------------------------------
 *
 * ::
 *
 *    {items -> f:array.reverse() -> f:join(separator: ', ')}
 *
 * .. code-block:: text
 *
 *    c, b, a
 *
 * @api
 * @see https://docs.typo3.org/permalink/fluid:typo3fluid-fluid-array-reverse
 */
final class ReverseViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'array', 'The array to reverse');
        $this->registerArgument('preserveKeys', 'boolean', 'If true, numeric keys are preserved. String keys are always preserved.', false, false);
    }

    /**
     * @return array The reversed array
     */
    public function render(): array
    {
        $value = $this->arguments['value'] ?? $this->renderChildren();
        if ($value === null || !is_iterable($value)) {
            $givenType = get_debug_type($value);
            throw new InvalidArgumentValueException(
                'The argument "value" was registered with type "array", but is of type "'
                . $givenType . '" in view helper "' . static::class . '".',
                1792005160,
            );
        }
        return array_reverse(iterator_to_array($value), $this->arguments['preserveKeys']);
    }
}
