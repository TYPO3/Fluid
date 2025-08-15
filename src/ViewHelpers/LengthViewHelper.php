<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * The LengthViewHelper returns the length of a given string.
 * Setting the character encoding is optional.
 *
 *
 * Examples
 * ========
 *
 * Simple example
 * --------------
 *
 * ::
 *
 *      <f:length value="Hello, World!" />
 *
 * Output::
 *
 *      13
 *
 * Example with encoding
 * ---------------------
 *
 * ::
 *
 *      <f:length value="Hello, World!" encoding="UTF-8" />
 *
 * Output::
 *
 *      13
 *
 * For supported character encodings see https://www.php.net/manual/en/mbstring.supported-encodings.php
 *
 *
 * Inline notation
 * ---------------
 *
 * ::
 *
 *      {f:length(value: 'Hello, World!')}
 *
 * Output::
 *
 *      13
 */
final class LengthViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'mixed', 'An string');
        $this->registerArgument('encoding', 'string', 'Character encoding', false, null);
    }

    /**
     * @return int The string length
     */
    public function render(): int
    {
        $value = $this->arguments['value'] ?? $this->renderChildren();
        $encoding = $this->arguments['encoding'];

        if (!is_scalar($value)) {
            $givenType = get_debug_type($value);
            throw new \InvalidArgumentException(
                'The argument "value" was registered with type "string", but is of type "'
                . $givenType . '" in view helper "' . static::class . '".',
                1754637887,
            );
        }

        return mb_strlen((string)$value, $encoding);
    }
}
