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
 * The RoundViewHelper rounds a float value with the specified precision.
 * The ViewHelper mimicks PHP's :php:`round()` function.
 *
 * Examples
 * ========
 *
 * Round with default precision
 * ----------------------------
 * ::
 *
 *    <f:round value="123.456" />
 *
 * .. code-block:: text
 *
 *    123.46
 *
 * Round with specific precision
 * -----------------------------
 * ::
 *
 *    <f:round value="123.456" precision="1" />
 *
 * .. code-block:: text
 *
 *    123.5
 *
 * Tag content as value
 * --------------------
 * ::
 *
 *    <f:round precision="1">123.456</f:round>
 *
 * .. code-block:: text
 *
 *    123.5
 */
final class RoundViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'float', 'The number that should be rounded');
        $this->registerArgument('precision', 'int', 'Rounding precision', false, 2);
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): float
    {
        $value = $arguments['value'] ?? (float) $renderChildrenClosure();
        return round($value, $arguments['precision']);
    }
}
