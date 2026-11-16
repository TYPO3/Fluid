<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\InvalidArgumentValueException;

/**
 * The FilterViewHelper removes all empty items from an array. Items are
 * evaluated the same way as conditions in ``<f:if>``, so ``null``,
 * ``false``, ``0``, ``''``, ``'0'``, ``'false'``, empty arrays and empty
 * countable objects are removed.
 *
 * If a property is given, the items are kept or removed based on the value
 * of that property instead. The items can then be arrays or objects.
 * Properties are accessed the same way as variables in templates, so getter
 * methods and nested property paths like ``author.name`` are supported.
 *
 * This ViewHelper is similar to PHP's :php:`array_filter()` function.
 *
 * For an object, the values of all properties that can be accessed in
 * templates are used as items, with the property names as keys. They are
 * read the same way as ``{object.name}`` works: a getter method like
 * ``getName()``, ``isActive()`` or ``hasChildren()`` is used if available,
 * otherwise the public property. Iterable objects, for example an
 * ``ObjectStorage``, are treated like arrays.
 *
 * Iterators like generators can yield the same key more than once. All of
 * their items are used, unless ``preserveKeys`` is set: then only the last
 * item per key is used, like with PHP's :php:`iterator_to_array()` function.
 *
 * Examples
 * ========
 *
 * Remove empty items
 * ------------------
 *
 * Given ``values = ['a', '', null, 'b', 0]``:
 *
 * ::
 *
 *    <f:filter value="{values}" />
 *
 * .. code-block:: php
 *
 *    ['a', 'b']
 *
 *
 * Filter by a property
 * --------------------
 *
 * Given ``news = [['title' => 'A', 'top' => true], ['title' => 'B', 'top' => false]]``:
 *
 * ::
 *
 *    <f:filter value="{news}" property="top" />
 *
 * .. code-block:: php
 *
 *    [
 *        ['title' => 'A', 'top' => true],
 *    ]
 *
 *
 * Render only matching items
 * --------------------------
 *
 * ::
 *
 *    <f:for each="{news -> f:filter(property: 'top')}" as="item">
 *        <h2>{item.title}</h2>
 *    </f:for>
 *
 *
 * Remove empty properties of an object
 * ------------------------------------
 *
 * Given ``user`` is an object with the public properties ``$name = 'Anna'``
 * and ``$email = ''``:
 *
 * ::
 *
 *    <f:filter value="{user}" preserveKeys="true" />
 *
 * .. code-block:: php
 *
 *    ['name' => 'Anna']
 *
 * @api
 * @see https://docs.typo3.org/permalink/fluid:typo3fluid-fluid-filter
 */
final class FilterViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'mixed', 'The array, iterable or object to filter');
        $this->registerArgument('property', 'string', 'Name or path of the property that decides whether an item is kept, e.g. "visible" or "author.active". If omitted, the items themselves are evaluated.');
        $this->registerArgument('preserveKeys', 'boolean', 'If true, the keys of the original array are preserved. Otherwise the result is reindexed numerically.', false, false);
    }

    /**
     * @return array The filtered array
     */
    public function render(): array
    {
        $value = $this->arguments['value'] ?? $this->renderChildren();
        if ($value instanceof \ArrayAccess && !is_iterable($value)) {
            throw new InvalidArgumentValueException(
                'The items of an object with array access can\'t be determined, but the argument "value" is of type "'
                . get_debug_type($value) . '" in view helper "' . static::class . '".',
                1794820133,
            );
        }
        if (is_object($value) && !is_iterable($value)) {
            $value = self::getAccessiblePropertyValues($value);
        }
        if (!is_iterable($value)) {
            throw new InvalidArgumentValueException(
                'The argument "value" must be an array, an iterable or an object, but is of type "'
                . get_debug_type($value) . '" in view helper "' . static::class . '".',
                1792005210,
            );
        }
        $property = $this->arguments['property'];
        $result = [];
        foreach (iterator_to_array($value, $this->arguments['preserveKeys']) as $key => $item) {
            $condition = $item;
            if ($property !== null) {
                if (!is_array($item) && !is_object($item)) {
                    throw new InvalidArgumentValueException(
                        'All items of the argument "value" must be arrays or objects if a property is given, but one is of type "'
                        . get_debug_type($item) . '" in view helper "' . static::class . '".',
                        1792005211,
                    );
                }
                $itemProvider = new StandardVariableProvider();
                $itemProvider->setSource($item);
                $condition = $itemProvider->getByPath($property);
            }
            if (BooleanNode::convertToBoolean($condition, $this->renderingContext)) {
                $result[$key] = $item;
            }
        }
        return $this->arguments['preserveKeys'] ? $result : array_values($result);
    }

    /**
     * Returns the values of all properties that can be accessed in templates,
     * following the same rules as StandardVariableProvider::getByPath()
     *
     * @return array<string, mixed>
     */
    private static function getAccessiblePropertyValues(object $object): array
    {
        $provider = new StandardVariableProvider();
        $provider->setSource($object);
        $values = [];
        for ($class = new \ReflectionObject($object); $class !== false; $class = $class->getParentClass()) {
            foreach ($class->getProperties() as $property) {
                $name = $property->getName();
                if (array_key_exists($name, $values) || $property->isStatic()) {
                    continue;
                }
                if (self::hasGetter($object, $name) || ($property->isPublic() && $property->isInitialized($object))) {
                    $values[$name] = $provider->getByPath($name);
                }
            }
        }
        return $values;
    }

    private static function hasGetter(object $object, string $name): bool
    {
        $upperCaseName = ucfirst($name);
        return method_exists($object, 'get' . $upperCaseName)
            || method_exists($object, 'is' . $upperCaseName)
            || method_exists($object, 'has' . $upperCaseName);
    }
}
