<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use Stringable;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * The ContainsViewHelper checks if a provided string or array contains
 * the specified value. Depending on the input, this mimicks PHP's
 * :php:`in_array()` or :php:`str_contains()`.
 *
 *
 * Examples
 * ========
 *
 * Check value in array
 * --------------------
 *
 * ::
 *
 *      <f:variable name="myArray" value="{0: 'Hello', 1: 'World'}" />
 *
 *      <f:contains value="Hello" subject="{myArray}">
 *          It Works!
 *      </f:contains>
 *
 * Output::
 *
 *      It Works!
 *
 * Check value in string
 * ---------------------
 *
 * ::
 *
 *      <f:variable name="myString" value="Hello, World!" />
 *
 *      <f:contains value="Wo" subject="{myString}">
 *          It Works!
 *      </f:contains>
 *
 * Output::
 *
 *      It Works!
 *
 * A more complex example with inline notation
 * -------------------------------------------
 *
 * ::
 *
 *      <f:variable name="myString" value="Hello, World!" />
 *
 *      <f:if condition="{someCondition} || {f:contains(value: 'Wo', subject: myString)}">
 *          It Works!
 *      </f:if>
 *
 * Output::
 *
 *       It Works!
 */
final class ContainsViewHelper extends AbstractConditionViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('value', 'mixed', 'The value to check for (needle)', true);
        $this->registerArgument('subject', 'mixed', 'The string or array that might contain the value (haystack)', true);
    }

    public static function verdict(array $arguments, RenderingContextInterface $renderingContext): bool
    {
        if (is_scalar($arguments['subject'])) {
            return static::stringContains((string)$arguments['subject'], $arguments['value']);
        }
        return static::arrayContains($arguments['subject'], $arguments['value']);
    }

    private static function stringContains(string $subject, mixed $value): bool
    {
        if (!is_scalar($value) && !$value instanceof Stringable) {
            $givenType = get_debug_type($value);
            throw new \InvalidArgumentException(
                'If the argument "subject" is a string, then "value" must be scalar, but it is of type "'
                . $givenType . '" in view helper "' . static::class . '".',
                1754978401,
            );
        }
        return str_contains($subject, (string)$value);
    }

    private static function arrayContains(mixed $subject, mixed $value): bool
    {
        if (!is_iterable($subject)) {
            $givenType = get_debug_type($subject);
            throw new \InvalidArgumentException(
                'The argument "subject" must be either a scalar value or an array/iterator, but is of type "'
                . $givenType . '" in view helper "' . static::class . '".',
                1754978402,
            );
        }
        return in_array($value, iterator_to_array($subject));
    }
}
