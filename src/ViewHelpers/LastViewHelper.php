<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * The LastViewHelper returns the last item of an array.
 *
 * Example
 * ========

 * ::
 *
 *    <f:last value="{0: 'first', 1: 'second'}" />
 *
 * .. code-block:: text
 *
 *    second
 */
final class LastViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'array', '');
    }

    public function render(): mixed
    {
        $value = $this->arguments['value'] ?? $this->renderChildren();
        if ($value === null || !is_iterable($value)) {
            $givenType = get_debug_type($value);
            throw new \InvalidArgumentException(
                'The argument "value" was registered with type "array", but is of type "' .
                $givenType . '" in view helper "' . static::class . '".',
                1712221620,
            );
        }
        $value = iterator_to_array($value);
        return array_pop($value);
    }
}
