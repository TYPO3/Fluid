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
 * The ChunkViewHelper splits an array into chunks of the given size.
 * The last chunk may contain fewer items if the number of items can't be
 * divided evenly.
 *
 * This ViewHelper mimicks PHP's :php:`array_chunk()` function.
 *
 * Iterators like generators can yield the same key more than once. All of
 * their items are used, unless ``preserveKeys`` is set: then only the last
 * item per key is used, like with PHP's :php:`iterator_to_array()` function.
 *
 * Examples
 * ========
 *
 * Split an array into chunks
 * --------------------------
 *
 * Given ``items = ['a', 'b', 'c', 'd', 'e']``:
 *
 * ::
 *
 *    <f:array.chunk value="{items}" size="2" />
 *
 * .. code-block:: php
 *
 *    [
 *        ['a', 'b'],
 *        ['c', 'd'],
 *        ['e'],
 *    ]
 *
 *
 * Preserve the original array keys
 * --------------------------------
 *
 * Given ``scores = ['a' => 1, 'b' => 2, 'c' => 3]``:
 *
 * ::
 *
 *    <f:array.chunk value="{scores}" size="2" preserveKeys="true" />
 *
 * .. code-block:: php
 *
 *    [
 *        ['a' => 1, 'b' => 2],
 *        ['c' => 3],
 *    ]
 *
 *
 * Render items in rows
 * --------------------
 *
 * ::
 *
 *    <f:for each="{items -> f:array.chunk(size: 2)}" as="row">
 *        <div class="row"><f:for each="{row}" as="item"><span>{item}</span></f:for></div>
 *    </f:for>
 *
 * .. code-block:: html
 *
 *    <div class="row"><span>a</span><span>b</span></div>
 *    <div class="row"><span>c</span><span>d</span></div>
 *    <div class="row"><span>e</span></div>
 *
 * @api
 * @see https://docs.typo3.org/permalink/fluid:typo3fluid-fluid-array-chunk
 */
final class ChunkViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'array', 'The array to split into chunks');
        $this->registerArgument('size', 'integer', 'Number of items in each chunk. The last chunk may contain fewer items.', true);
        $this->registerArgument('preserveKeys', 'boolean', 'If true, the keys of the original array are preserved in each chunk. Otherwise each chunk is reindexed numerically.', false, false);
    }

    /**
     * @return array The array of chunks
     */
    public function render(): array
    {
        $value = $this->arguments['value'] ?? $this->renderChildren();
        if ($value === null || !is_iterable($value)) {
            $givenType = get_debug_type($value);
            throw new InvalidArgumentValueException(
                'The argument "value" was registered with type "array", but is of type "'
                . $givenType . '" in view helper "' . static::class . '".',
                1791976070,
            );
        }
        $size = $this->arguments['size'];
        if ($size < 1) {
            throw new InvalidArgumentValueException(
                'The argument "size" must be greater than 0 in view helper "' . static::class . '".',
                1791976071,
            );
        }
        return array_chunk(iterator_to_array($value, $this->arguments['preserveKeys']), $size, $this->arguments['preserveKeys']);
    }
}
