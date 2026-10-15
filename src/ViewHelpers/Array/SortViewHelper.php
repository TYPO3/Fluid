<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers\Array;

use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\InvalidArgumentValueException;

/**
 * The SortViewHelper sorts the items of an array in ascending or
 * descending order. Items with equal values keep their original order.
 *
 * Items are sorted in human order: numbers and numeric strings are compared
 * by their value, so ``11`` comes before ``100``. Other strings are compared
 * in natural, case-insensitive order, so ``'img2.png'`` comes before
 * ``'img10.png'``, like PHP's :php:`strnatcasecmp()` function. All other
 * values, for example dates, are compared with PHP's comparison rules.
 *
 * If a property is given, the items are sorted by the value of that
 * property instead. The items can then be arrays or objects. Properties
 * are accessed the same way as variables in templates, so getter methods
 * and nested property paths like ``author.name`` are supported.
 *
 * Iterators like generators can yield the same key more than once. All of
 * their items are used, unless ``preserveKeys`` is set: then only the last
 * item per key is used, like with PHP's :php:`iterator_to_array()` function.
 *
 * Examples
 * ========
 *
 * Sort an array
 * -------------
 *
 * Given ``numbers = [100, 9, 11]``:
 *
 * ::
 *
 *    <f:array.sort value="{numbers}" />
 *
 * .. code-block:: php
 *
 *    [9, 11, 100]
 *
 *
 * Sort strings in human order
 * ---------------------------
 *
 * Given ``files = ['img12.png', 'IMG10.png', 'img2.png', 'img1.png']``:
 *
 * ::
 *
 *    <f:array.sort value="{files}" />
 *
 * .. code-block:: php
 *
 *    ['img1.png', 'img2.png', 'IMG10.png', 'img12.png']
 *
 *
 * Sort in descending order
 * ------------------------
 *
 * ::
 *
 *    <f:array.sort value="{numbers}" descending="true" />
 *
 * .. code-block:: php
 *
 *    [100, 11, 9]
 *
 *
 * Sort by a property
 * ------------------
 *
 * Given ``products = [['title' => 'Book', 'price' => 12], ['title' => 'Pen', 'price' => 2.5]]``:
 *
 * ::
 *
 *    <f:for each="{products -> f:array.sort(property: 'price')}" as="product">
 *        {product.title}
 *    </f:for>
 *
 * .. code-block:: text
 *
 *    Pen Book
 *
 * @api
 * @see https://docs.typo3.org/permalink/fluid:typo3fluid-fluid-array-sort
 */
final class SortViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'array', 'The array to sort');
        $this->registerArgument('property', 'string', 'Name or path of the property to sort by, e.g. "title" or "author.name". If omitted, the items themselves are compared.');
        $this->registerArgument('descending', 'boolean', 'If true, the items are sorted in descending order', false, false);
        $this->registerArgument('preserveKeys', 'boolean', 'If true, the keys of the original array are preserved. Otherwise the result is reindexed numerically.', false, false);
    }

    /**
     * @return array The sorted array
     */
    public function render(): array
    {
        $value = $this->arguments['value'] ?? $this->renderChildren();
        if ($value === null || !is_iterable($value)) {
            $givenType = get_debug_type($value);
            throw new InvalidArgumentValueException(
                'The argument "value" was registered with type "array", but is of type "'
                . $givenType . '" in view helper "' . static::class . '".',
                1792005220,
            );
        }
        $items = iterator_to_array($value, $this->arguments['preserveKeys']);
        $sortValues = $this->getSortValues($items, $this->arguments['property']);
        $direction = $this->arguments['descending'] ? -1 : 1;
        uksort($items, static fn(int|string $a, int|string $b): int => $direction * self::compare($sortValues[$a], $sortValues[$b]));
        return $this->arguments['preserveKeys'] ? $items : array_values($items);
    }

    /**
     * Returns the values to compare, with the same keys as the items
     */
    private function getSortValues(array $items, ?string $property): array
    {
        if ($property === null) {
            return $items;
        }
        $sortValues = [];
        foreach ($items as $key => $item) {
            if (!is_array($item) && !is_object($item)) {
                throw new InvalidArgumentValueException(
                    'All items of the argument "value" must be arrays or objects if a property is given, but one is of type "'
                    . get_debug_type($item) . '" in view helper "' . static::class . '".',
                    1792005221,
                );
            }
            $itemProvider = new StandardVariableProvider();
            $itemProvider->setSource($item);
            $sortValues[$key] = $itemProvider->getByPath($property);
        }
        return $sortValues;
    }

    /**
     * Compares two values in human order: numbers by value, strings in
     * natural, case-insensitive order and everything else with PHP's
     * comparison rules
     */
    private static function compare(mixed $a, mixed $b): int
    {
        if (is_numeric($a) && is_numeric($b)) {
            return $a <=> $b;
        }
        if ((is_string($a) || $a instanceof \Stringable) && (is_string($b) || $b instanceof \Stringable)) {
            return strnatcasecmp((string)$a, (string)$b);
        }
        return $a <=> $b;
    }
}
