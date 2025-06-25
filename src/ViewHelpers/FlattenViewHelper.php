<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * The FlattenViewHelper flattens a multi-dimensional array into a
 * single-dimensional array.
 *
 *
 * Example
 * ========
 *
 * ::
 *
 *    <f:flatten value="{0: {0: '1', 1: '2'}, 1: {0: '3', 1: '4'}}" />
 *
 * .. code-block:: text
 *
 *    {0: '1', 1: '2', 2: '3', 3: '4'}
 */
final class FlattenViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'array', 'An array');
    }

    /**
     * @return array The flattened array
     */
    public function render(): array
    {
        $value = $this->arguments['value'] ?? $this->renderChildren();
        if ($value === null || !is_iterable($value)) {
            $givenType = get_debug_type($value);
            throw new \InvalidArgumentException(
                'The argument "value" was registered with type "array", but is of type "' .
                $givenType . '" in view helper "' . static::class . '".',
                1750878602,
            );
        }
        $value = iterator_to_array($value);

        return $this->flatten($value);
    }

    private function flatten(array $array): array
    {
        $return = [];

        array_walk_recursive($array, function ($a) use (&$return) { $return[] = $a; });

        return $return;
    }
}
