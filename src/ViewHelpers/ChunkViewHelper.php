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
 * The ChunkViewHelper splits an array or a string into chunks of the given
 * size. The last chunk may be shorter if the items or characters can't be
 * divided evenly.
 *
 * For arrays, this ViewHelper mimicks PHP's :php:`array_chunk()` function.
 * For strings, it mimicks PHP's :php:`mb_str_split()` function, so the size
 * counts characters, not bytes.
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
 *    <f:chunk value="{items}" size="2" />
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
 *    <f:chunk value="{scores}" size="2" preserveKeys="{true}" />
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
 *    <f:for each="{items -> f:chunk(size: 2)}" as="row">
 *        <div class="row"><f:for each="{row}" as="item"><span>{item}</span></f:for></div>
 *    </f:for>
 *
 * .. code-block:: html
 *
 *    <div class="row"><span>a</span><span>b</span></div>
 *    <div class="row"><span>c</span><span>d</span></div>
 *    <div class="row"><span>e</span></div>
 *
 *
 * Split a string into groups
 * --------------------------
 *
 * Given ``iban = 'DE89370400440532013000'``:
 *
 * ::
 *
 *    {iban -> f:chunk(size: 4) -> f:join(separator: ' ')}
 *
 * .. code-block:: text
 *
 *    DE89 3704 0044 0532 0130 00
 *
 * @api
 * @see https://docs.typo3.org/permalink/fluid:typo3fluid-fluid-chunk
 */
final class ChunkViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'mixed', 'The array or string to split into chunks');
        $this->registerArgument('size', 'integer', 'Number of items or characters in each chunk. The last chunk may be shorter.', true);
        $this->registerArgument('preserveKeys', 'boolean', 'If true, the keys of the original array are preserved in each chunk. Otherwise each chunk is reindexed numerically. Has no effect on strings.', false, false);
    }

    /**
     * @return array The array of chunks
     */
    public function render(): array
    {
        $value = $this->arguments['value'] ?? $this->renderChildren();
        if (!is_string($value) && !is_iterable($value)) {
            $givenType = get_debug_type($value);
            throw new InvalidArgumentValueException(
                'The argument "value" must be an array or a string, but is of type "'
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
        if (is_string($value)) {
            return mb_str_split($value, $size);
        }
        return array_chunk(iterator_to_array($value, $this->arguments['preserveKeys']), $size, $this->arguments['preserveKeys']);
    }
}
