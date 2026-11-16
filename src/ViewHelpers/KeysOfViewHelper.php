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
 * The KeysOfViewHelper returns all keys of an array.
 *
 * This ViewHelper mimicks PHP's :php:`array_keys()` function.
 *
 * For an object, it returns the names of all properties that can be accessed
 * in templates: public properties and properties with a getter method like
 * ``getName()``, ``isActive()`` or ``hasChildren()``, the same way as
 * ``{object.name}`` works. Iterable objects, for example an ``ObjectStorage``,
 * are treated like arrays.
 *
 * Examples
 * ========
 *
 * Keys of an array
 * ----------------
 *
 * Given ``config = ['title' => 'Fluid', 'version' => 5]``:
 *
 * ::
 *
 *    <f:keysOf value="{config}" />
 *
 * .. code-block:: php
 *
 *    ['title', 'version']
 *
 *
 * Output the keys as a list
 * -------------------------
 *
 * ::
 *
 *    {config -> f:keysOf() -> f:join(separator: ', ')}
 *
 * .. code-block:: text
 *
 *    title, version
 *
 *
 * Property names of an object
 * ---------------------------
 *
 * Given ``user`` is an object with a public ``$name`` property and a
 * protected ``$email`` property with a ``getEmail()`` method:
 *
 * ::
 *
 *    <f:keysOf value="{user}" />
 *
 * .. code-block:: php
 *
 *    ['name', 'email']
 *
 * @api
 * @see https://docs.typo3.org/permalink/fluid:typo3fluid-fluid-keysof
 */
final class KeysOfViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'mixed', 'The array, iterable or object to get the keys or property names from');
    }

    /**
     * @return array The keys of the array or the property names of the object
     */
    public function render(): array
    {
        $value = $this->arguments['value'] ?? $this->renderChildren();
        if (is_iterable($value)) {
            $keys = [];
            foreach ($value as $key => $item) {
                $keys[] = $key;
            }
            return $keys;
        }
        if ($value instanceof \ArrayAccess) {
            throw new InvalidArgumentValueException(
                'The keys of an object with array access can\'t be determined, but the argument "value" is of type "'
                . get_debug_type($value) . '" in view helper "' . static::class . '".',
                1794819223,
            );
        }
        if (is_object($value)) {
            return self::getAccessiblePropertyNames($value);
        }
        throw new InvalidArgumentValueException(
            'The argument "value" must be an array, an iterable or an object, but is of type "'
            . get_debug_type($value) . '" in view helper "' . static::class . '".',
            1792005150,
        );
    }

    /**
     * Returns the names of all properties that can be accessed in templates,
     * following the same rules as StandardVariableProvider::getByPath()
     *
     * @return list<string>
     */
    private static function getAccessiblePropertyNames(object $object): array
    {
        $names = [];
        for ($class = new \ReflectionObject($object); $class !== false; $class = $class->getParentClass()) {
            foreach ($class->getProperties() as $property) {
                $name = $property->getName();
                if (isset($names[$name]) || $property->isStatic()) {
                    continue;
                }
                if (self::hasGetter($object, $name) || ($property->isPublic() && $property->isInitialized($object))) {
                    $names[$name] = true;
                }
            }
        }
        return array_map(strval(...), array_keys($names));
    }

    private static function hasGetter(object $object, string $name): bool
    {
        $upperCaseName = ucfirst($name);
        return method_exists($object, 'get' . $upperCaseName)
            || method_exists($object, 'is' . $upperCaseName)
            || method_exists($object, 'has' . $upperCaseName);
    }
}
