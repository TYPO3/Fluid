<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers\Array;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\InvalidArgumentValueException;

/**
 * The KeysViewHelper returns all keys of an array.
 *
 * This ViewHelper mimicks PHP's :php:`array_keys()` function.
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
 *    <f:array.keys value="{config}" />
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
 *    {config -> f:array.keys() -> f:join(separator: ', ')}
 *
 * .. code-block:: text
 *
 *    title, version
 *
 * @api
 * @see https://docs.typo3.org/permalink/fluid:typo3fluid-fluid-array-keys
 */
final class KeysViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'array', 'The array to get the keys from');
    }

    /**
     * @return array The keys of the array
     */
    public function render(): array
    {
        $value = $this->arguments['value'] ?? $this->renderChildren();
        if ($value === null || !is_iterable($value)) {
            $givenType = get_debug_type($value);
            throw new InvalidArgumentValueException(
                'The argument "value" was registered with type "array", but is of type "'
                . $givenType . '" in view helper "' . static::class . '".',
                1792005150,
            );
        }
        $keys = [];
        foreach ($value as $key => $item) {
            $keys[] = $key;
        }
        return $keys;
    }
}
