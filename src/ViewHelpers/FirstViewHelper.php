<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * The FirstViewHelper returns the first item of an array.
 *
 * Example
 * ========

 * ::
 *
 *    <f:first value="{0: 'first', 1: 'second'}" />
 *
 * .. code-block:: text
 *
 *    first
 */
final class FirstViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'array', '');
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): mixed
    {
        $value = $arguments['value'] ?? $renderChildrenClosure();

        if ($value === null || !is_iterable($value)) {
            $givenType = get_debug_type($value);
            throw new \InvalidArgumentException(
                'The argument "value" was registered with type "array", but is of type "' .
                $givenType . '" in view helper "' . static::class . '".',
                1712220569,
            );
        }

        $value = self::iteratorToArray($value);

        return array_shift($value);
    }

    /**
     * This ensures compatibility with PHP 8.1
     */
    private static function iteratorToArray(\Traversable|array $iterator): array
    {
        return is_array($iterator) ? $iterator : iterator_to_array($iterator);
    }
}
