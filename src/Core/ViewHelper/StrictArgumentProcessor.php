<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\ViewHelper;

use ArrayAccess;
use Stringable;
use Traversable;

/**
 * The StrictArgumentProcessor offers an alternative, stricter implementation
 * for Fluid's argument validation. Noteworthy differences:
 *
 * 1. The validation either ends with a negative result or with a valid
 *    type of the variable. In the previous implementation, there could be
 *    situations where the type wasn't ensured properly.
 * 2. Scalar values are implicitly converted to the correct type.
 * 3. Common type aliases are considered (e. g. int and integer)
 *
 * @internal
 */
final readonly class StrictArgumentProcessor implements ArgumentProcessorInterface
{
    public function process(mixed $value, ArgumentDefinition $definition): mixed
    {
        if (!$definition->isRequired() && $value === $definition->getDefaultValue()) {
            return $value;
        }
        // bool/boolean is not handled separately here because Fluid's BooleanParser
        // already ensures valid boolean values
        return match ($definition->getType()) {
            'string' => is_scalar($value) ? (string)$value : $value,
            'int', 'integer' => is_scalar($value) ? (int)$value : $value,
            'float', 'double' => is_scalar($value) ? (float)$value : $value,
            default => $value,
        };
    }

    public function isValid(mixed $value, ArgumentDefinition $definition): bool
    {
        // Allow everything for mixed type
        if ($definition->getType() === 'mixed') {
            return true;
        }

        // Always allow default value if argument is not required
        if (!$definition->isRequired() && $value === $definition->getDefaultValue()) {
            return true;
        }

        // Perform type validation
        return $this->isValidType($definition->getType(), $value);
    }

    /**
     * Check whether the defined type matches the value type
     */
    private function isValidType(string $type, mixed $value): bool
    {
        if ($type === 'object') {
            return is_object($value);
        }
        if ($type === 'string') {
            return is_string($value) || $value instanceof Stringable;
        }
        if ($type === 'int' || $type === 'integer') {
            return is_int($value);
        }
        if ($type === 'float' || $type === 'double') {
            return is_float($value);
        }
        if ($type === 'bool' || $type === 'boolean') {
            return is_bool($value);
        }
        if ($type === 'iterable') {
            return is_iterable($value);
        }
        if ($type === 'countable') {
            return is_countable($value);
        }
        if ($type === 'callable') {
            return is_callable($value);
        }
        if ($type === 'array' || str_ends_with($type, '[]')) {
            if (!is_array($value) && !$value instanceof ArrayAccess && !$value instanceof Traversable) {
                return false;
            }
            if (str_ends_with($type, '[]')) {
                $firstElement = $this->getFirstElementOfNonEmpty($value);
                if ($firstElement === null) {
                    return true;
                }
                return $this->isValidType(substr($type, 0, -2), $firstElement);
            }
            return true;
        }
        if (class_exists($type) && $value instanceof $type) {
            return true;
        }
        return false;
    }

    /**
     * Return the first element of the given array, ArrayAccess or Traversable
     * that is not empty
     */
    private function getFirstElementOfNonEmpty(mixed $value): mixed
    {
        if (is_array($value)) {
            return reset($value);
        }
        if ($value instanceof Traversable) {
            foreach ($value as $element) {
                return $element;
            }
        }
        return null;
    }
}
