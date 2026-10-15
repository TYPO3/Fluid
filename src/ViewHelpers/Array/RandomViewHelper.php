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
 * The RandomViewHelper returns a random item of an array. For an empty
 * array, ``null`` is returned.
 *
 * This ViewHelper uses PHP's :php:`array_rand()` function. A new item is
 * selected each time the template is rendered. Note that if the rendered
 * output is cached, for example by a page cache, the selected item is
 * cached as well.
 *
 * Examples
 * ========
 *
 * Random item of an array
 * -----------------------
 *
 * Given ``teasers = ['Teaser A', 'Teaser B', 'Teaser C']``:
 *
 * ::
 *
 *    <f:array.random value="{teasers}" />
 *
 * .. code-block:: text
 *
 *    Teaser B
 *
 *
 * Inline usage
 * ------------
 *
 * ::
 *
 *    <f:variable name="teaser" value="{teasers -> f:array.random()}" />
 *
 * @api
 * @see https://docs.typo3.org/permalink/fluid:typo3fluid-fluid-array-random
 */
final class RandomViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'array', 'The array to select a random item from');
    }

    /**
     * @return mixed A random item of the array, or null if the array is empty
     */
    public function render(): mixed
    {
        $value = $this->arguments['value'] ?? $this->renderChildren();
        if ($value === null || !is_iterable($value)) {
            $givenType = get_debug_type($value);
            throw new InvalidArgumentValueException(
                'The argument "value" was registered with type "array", but is of type "'
                . $givenType . '" in view helper "' . static::class . '".',
                1792005190,
            );
        }
        $value = iterator_to_array($value, false);
        return $value === [] ? null : $value[array_rand($value)];
    }
}
