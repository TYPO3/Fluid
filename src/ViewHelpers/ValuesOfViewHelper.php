<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\InvalidArgumentValueException;

/**
 * The ValuesOfViewHelper returns the values of an array as a list, without
 * its keys.
 *
 * If a property is given, it returns the values of that property from all
 * items instead. The items can then be arrays or objects. Properties are
 * accessed the same way as variables in templates, so getter methods and
 * nested property paths like ``author.name`` are supported. If an item
 * doesn't have the property, ``null`` is used as value.
 *
 * Optionally, another property can be used as keys of the resulting array.
 * If several items have the same key, the last one wins.
 *
 * This ViewHelper mimicks PHP's :php:`array_values()` and
 * :php:`array_column()` functions.
 *
 * Examples
 * ========
 *
 * Values of an array
 * ------------------
 *
 * Given ``config = ['title' => 'Fluid', 'version' => 5]``:
 *
 * ::
 *
 *    <f:valuesOf value="{config}" />
 *
 * .. code-block:: php
 *
 *    ['Fluid', 5]
 *
 *
 * Values of a property
 * --------------------
 *
 * Given ``users = [['id' => 3, 'name' => 'Anna'], ['id' => 7, 'name' => 'Ben']]``:
 *
 * ::
 *
 *    <f:valuesOf value="{users}" property="name" />
 *
 * .. code-block:: php
 *
 *    ['Anna', 'Ben']
 *
 *
 * Use another property as keys
 * ----------------------------
 *
 * ::
 *
 *    <f:valuesOf value="{users}" property="name" indexProperty="id" />
 *
 * .. code-block:: php
 *
 *    [3 => 'Anna', 7 => 'Ben']
 *
 *
 * Output the values as a list
 * ---------------------------
 *
 * ::
 *
 *    {users -> f:valuesOf(property: 'name') -> f:join(separator: ', ')}
 *
 * .. code-block:: text
 *
 *    Anna, Ben
 *
 * @api
 * @see https://docs.typo3.org/permalink/fluid:typo3fluid-fluid-valuesof
 */
final class ValuesOfViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'array', 'The array to get the values from. If a property is given, an array of arrays or objects.');
        $this->registerArgument('property', 'string', 'Name or path of the property whose values should be returned, e.g. "title" or "author.name". If omitted, the items themselves are returned.');
        $this->registerArgument('indexProperty', 'string', 'Name or path of the property whose values should be used as keys of the resulting array');
    }

    /**
     * @return array The values of the array or of the property
     */
    public function render(): array
    {
        $value = $this->arguments['value'] ?? $this->renderChildren();
        if ($value === null || !is_iterable($value)) {
            $givenType = get_debug_type($value);
            throw new InvalidArgumentValueException(
                'The argument "value" was registered with type "array", but is of type "'
                . $givenType . '" in view helper "' . static::class . '".',
                1792005200,
            );
        }
        $property = $this->arguments['property'];
        $indexProperty = $this->arguments['indexProperty'];
        $result = [];
        foreach ($value as $item) {
            if ($property === null && $indexProperty === null) {
                $result[] = $item;
                continue;
            }
            if (!is_array($item) && !is_object($item)) {
                throw new InvalidArgumentValueException(
                    'All items of the argument "value" must be arrays or objects if a property is given, but one is of type "'
                    . get_debug_type($item) . '" in view helper "' . static::class . '".',
                    1792005201,
                );
            }
            $itemProvider = new StandardVariableProvider();
            $itemProvider->setSource($item);
            $column = $property === null ? $item : $itemProvider->getByPath($property);
            $index = $indexProperty === null ? null : $itemProvider->getByPath($indexProperty);
            if ($index === null) {
                $result[] = $column;
                continue;
            }
            if ($index instanceof \Stringable) {
                $index = (string)$index;
            }
            if (!is_int($index) && !is_string($index)) {
                throw new InvalidArgumentValueException(
                    'The values of the property "' . $indexProperty . '" must be integers or strings to be used as keys, but one is of type "'
                    . get_debug_type($index) . '" in view helper "' . static::class . '".',
                    1792005202,
                );
            }
            $result[$index] = $column;
        }
        return $result;
    }
}
