<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * The JoinViewHelper combines elements from an array into a single string.
 * You can specify both a general separator and a special one for the last
 * element, which serves as the delimiter between the elements.
 *
 *
 * Examples
 * ========
 *
 * Simple join
 * -----------
 * ::
 *
 *    <f:join value="{0: '1', 1: '2', 2: '3'}" />
 *
 * .. code-block:: text
 *
 *    123
 *
 *
 * Join with separator
 * -------------------
 *
 * ::
 *
 *    <f:join value="{0: '1', 1: '2', 2: '3'}" separator=", " />
 *
 * .. code-block:: text
 *
 *    1, 2, 3
 *
 *
 * Join with separator, and special one for the last
 * -------------------------------------------------
 *
 * ::
 *
 *    <f:join value="{0: '1', 1: '2', 2: '3'}" separator=", " separatorLast=" and " />
 *
 * .. code-block:: text
 *
 *    1, 2 and 3
 */
final class JoinViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'array', 'An array');
        $this->registerArgument('separator', 'string', 'The separator', false, '');
        $this->registerArgument('separatorLast', 'string', 'The separator for the last pair.');
    }

    /**
     * @return string The concatenated string
     */
    public function render(): string
    {
        $value = $this->arguments['value'] ?? $this->renderChildren();
        $separator = $this->arguments['separator'] ?? '';
        $separatorLast = $this->arguments['separatorLast'] ?? null;
        if ($value === null || !is_iterable($value)) {
            $givenType = get_debug_type($value);
            throw new \InvalidArgumentException(
                'The argument "value" was registered with type "array", but is of type "' .
                $givenType . '" in view helper "' . static::class . '".',
                1256475113,
            );
        }
        $value = iterator_to_array($value);
        if (\count($value) < 2) {
            return (string)array_pop($value);
        }
        if ($separatorLast === null || $separatorLast === $separator) {
            return implode($separator, $value);
        }
        return implode($separator, \array_slice($value, 0, -1)) . $separatorLast . $value[\count($value) - 1];
    }
}
