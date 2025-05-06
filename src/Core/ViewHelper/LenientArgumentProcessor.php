<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\ViewHelper;

use ArrayAccess;
use Traversable;

/**
 * The LenientArgumentProcessor mimicks the ViewHelper argument validation
 * logic used by Fluid up to (and including) 4.x:
 *
 * 1. Argument types are validated against the isValidType() implementation
 *    which previously was part of AbstractViewHelper. For clear violations
 *    (e. g. a string instead of an array), validation fails.
 * 2. Valid types are NOT guaranteed to ViewHelpers in all cases, since scalar
 *    types are not converted implicitly (meaning: You might receive an integer,
 *    even if the ViewHelper specifies "string" as argument type)
 * 3. ArrayAccess and Traversible are valid array arguments
 * 4. Objects implementing Stringable are valid string arguments
 * 5. In addition to PHP's internal types, class names can be specified as well
 * 6. Collection types (e. g. "string[]") are possible. Only the first item
 *    of a collection is validated.
 *
 * @internal
 */
final readonly class LenientArgumentProcessor implements ArgumentProcessorInterface
{
    public function process(mixed $value, ArgumentDefinition $definition): mixed
    {
        // No processing, argument values are passed on unmodified
        return $value;
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
            if (!is_object($value)) {
                return false;
            }
        } elseif ($type === 'array' || substr($type, -2) === '[]') {
            if (!is_array($value) && !$value instanceof ArrayAccess && !$value instanceof Traversable && !empty($value)) {
                return false;
            }
            if (substr($type, -2) === '[]') {
                $firstElement = $this->getFirstElementOfNonEmpty($value);
                if ($firstElement === null) {
                    return true;
                }
                return $this->isValidType(substr($type, 0, -2), $firstElement);
            }
        } elseif ($type === 'string') {
            if (is_object($value) && !method_exists($value, '__toString')) {
                return false;
            }
        } elseif ($type === 'boolean' && !is_bool($value)) {
            return false;
        } elseif (class_exists($type) && $value !== null && !$value instanceof $type) {
            return false;
        } elseif (is_object($value) && !is_a($value, $type, true)) {
            return false;
        }
        return true;
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
