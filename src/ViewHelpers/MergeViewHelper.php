<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * The MergeViewHelper merges two arrays into one, optionally recursively.
 *
 * It works similar to the PHP functions array_merge() and array_merge_recursive(),
 * depending on the value of the "recursive" argument.
 *
 *
 * Example
 * ========
 *
 * Simple merge
 * -------------
 *
 * ::
 *
 *    <f:merge array="{0: 'a', 1: 'b'}" with="{1: 'x', 2: 'c'}" />
 *
 * Result::
 *
 *    {0: 'a', 1: 'x', 2: 'c'}
 *
 * Recursive merge
 * ---------------
 *
 * ::
 *
 *    <f:merge array="{foo: {bar: 'baz'}}" with="{foo: {qux: 'value'}}" recursive="true" />
 *
 * Result::
 *
 *    {foo: {bar: 'baz', qux: 'value'}}
 *
 * Inline notation
 * ---------------
 *
 * ::
 *
 *      {f:variable(name: 'myArray', value: {foo: 1})}
 *      {myArray -> f:merge(with: {bar: 2})}
 *
 * Result::
 *
 *    {foo: 1, bar: 2}
 */
final class MergeViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('array', 'array', 'The array to merge into');
        $this->registerArgument('with', 'array', 'Array to be merged');
        $this->registerArgument('recursive', 'boolean', 'Whether to merge arrays recursively', false, false);
    }

    /**
     * @return array
     */
    public function render(): array
    {
        $array = $this->arguments['array'] ?? $this->renderChildren();
        if (!is_iterable($array)) {
            $givenType = get_debug_type($array);
            throw new \InvalidArgumentException(
                'The argument "array" was registered with type "array", but is of type "'
                . $givenType . '" in view helper "' . static::class . '".',
                1755316529,
            );
        }
        $array = iterator_to_array($array);

        $with = $this->arguments['with'] ?? [];
        if (!is_iterable($with)) {
            $givenType = get_debug_type($with);
            throw new \InvalidArgumentException(
                'The argument "with" was registered with type "array", but is of type "'
                . $givenType . '" in view helper "' . static::class . '".',
                1755316530,
            );
        }
        $with = iterator_to_array($with);

        if ($this->arguments['recursive']) {
            return array_merge_recursive($array, $with);
        }

        return array_merge($array, $with);
    }
}
