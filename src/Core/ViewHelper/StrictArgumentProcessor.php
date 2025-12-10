<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\ViewHelper;

use ArrayAccess;
use BackedEnum;
use ReflectionEnum;
use Stringable;
use Traversable;
use UnitEnum;

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
        // Scalar values can be type-casted automatically
        // Boolean expressions are evaluated at the parser level, so we just make sure
        // that the input has the correct type
        if (is_scalar($value)) {
            return match ($definition->getType()) {
                'string' => (string)$value,
                'int', 'integer' => (int)$value,
                'float', 'double' => (float)$value,
                'bool', 'boolean' => (bool)$value,
                default => enum_exists($definition->getType()) ? $this->convertValueToEnum($definition->getType(), $value) : $value,
            };
        }
        return $value;
    }

    public function isValid(mixed $value, ArgumentDefinition $definition): bool
    {
        // Allow everything for mixed type
        if ($definition->getType() === 'mixed') {
            return true;
        }

        // Always allow default value if argument is not required
        if (!$definition->isRequired() && ($value === null || $value === $definition->getDefaultValue())) {
            return true;
        }

        // Perform type validation
        foreach ($definition->getUnionTypes() as $type) {
            if ($this->isValidType($type, $value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Attempt to convert a scalar value to a valid enum case if expected type is an enum
     *
     * @param class-string<UnitEnum> $type
     */
    private function convertValueToEnum(string $type, mixed $value): mixed
    {
        // For backed enums, the scalar equivalent is preferred, but the case name can
        // be used as well
        if (is_a($type, BackedEnum::class, true)) {
            // Make sure that tryFrom() can be called without type mismatches
            $backingType = (string)(new ReflectionEnum($type))->getBackingType();
            if (
                ($backingType === 'string' && is_string($value))
                || ($backingType === 'int' && is_int($value))
            ) {
                $enum = $type::tryFrom($value);
                if ($enum !== null) {
                    return $enum;
                }
            }
        }
        // Check if enum case name exists
        return (is_string($value) && defined("$type::$value"))
            ? constant("$type::$value")
            : $value;
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
                $firstElement = $this->getFirstElement($value);
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
        if (interface_exists($type) && $value instanceof $type) {
            return true;
        }
        return false;
    }

    /**
     * Return the first element of the given array, ArrayAccess or Traversable
     */
    private function getFirstElement(mixed $value): mixed
    {
        if (is_array($value) && $value !== []) {
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
