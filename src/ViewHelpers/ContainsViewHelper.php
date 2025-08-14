<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * The ContainsViewHelper checks if a given value exists in the provided array
 * or is contained within the provided string. You cannot use both the `array`
 * and `string` arguments at the same time.
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
 *      <f:contains value="Hello" array="{myArray}" />
 *      It Works!
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
 *      <f:contains value="Wo" string="{myString}" />
 *      It Works!
 *      </f:contains>
 *
 * Output::
 *
 *      It Works!
 *
 * A more complex example with inline notation
 * -----------------------------------------
 *
 * ::
 *
 *      <f:variable name="condition" value="false" />
 *      <f:variable name="myString" value="Hello, World!" />
 *
 *      <f:if condition="{condition || {f:contains(search: 'Wo', subject: myString)}}">
 *      It Works!
 *      </f:if>
 *
 * Output::
 *
 *       It Works!
 */
class ContainsViewHelper extends AbstractConditionViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('value', 'mixed', 'The value to check for in either the given string or array', true);
        $this->registerArgument('string', 'string', 'The string to check for');
        $this->registerArgument('array', 'array', 'The array to check for');
    }

    /**
     * @param array $arguments
     * @param RenderingContextInterface $renderingContext
     * @return bool
     */
    public static function verdict(array $arguments, RenderingContextInterface $renderingContext): bool
    {
        $hasString = !empty($arguments['string']);
        $hasArray  = !empty($arguments['array']);

        if ($hasString === $hasArray) {
            throw new \InvalidArgumentException(
                'Exactly one of the arguments "string" and "array" must be provided.',
                1754978400,
            );
        }

        if ($arguments['string']) {
            return static::handleString($arguments);
        }

        if ($arguments['array']) {
            return static::handleArray($arguments);
        }

        return false;
    }

    /**
     * @param array $arguments
     * @return bool
     */
    protected static function handleString(array $arguments): bool
    {
        if (!is_string($arguments['string'])) {
            throw new \InvalidArgumentException('The argument "string" must be a string: ' . $arguments['string'], 1754978404);
        }
        if (!is_scalar($arguments['value'])) {
            $givenType = get_debug_type($arguments['value']);
            throw new \InvalidArgumentException(
                'The argument "value" was registered with type "string", but is of type "' .
                $givenType . '" in view helper "' . static::class . '".',
                1754978401,
            );
        }
        return str_contains($arguments['string'], (string)$arguments['value']);
    }

    /**
     * @param array $arguments
     * @return bool
     */
    protected static function handleArray(array $arguments): bool
    {
        $haystack = $arguments['array'];
        if (!is_iterable($haystack)) {
            $givenType = get_debug_type($haystack);
            throw new \InvalidArgumentException(
                'The argument "array" was registered with type "array", but is of type "' .
                $givenType . '" in view helper "' . static::class . '".',
                1754978402,
            );
        }
        $haystack = iterator_to_array($haystack);

        return in_array($arguments['value'], $haystack);
    }
}
