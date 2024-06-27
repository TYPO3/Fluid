<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Wrapper for PHPs :php:`constant` function.
 * See https://www.php.net/manual/function.constant.php.
 *
 * Examples
 * ========
 *
 * Get built-in PHP constant
 * -------------------------
 *
 * ::
 *
 *    {f:constant(name: 'PHP_INT_MAX')}
 *
 * Output::
 *
 *    9223372036854775807
 *    (Depending on CPU architecture).
 *
 * Get class constant
 * ------------------
 *
 * ::
 *
 *    {f:constant(name: '\Vendor\Package\Class::CONSTANT')}
 *
 * Get enum value
 * --------------
 *
 * ::
 *
 *    {f:constant(name: '\Vendor\Package\Enum::CASE')}
 */
class ConstantViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('name', 'string', 'String representation of a PHP constant or enum');
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): mixed
    {
        $name = $arguments['name'] ?? $renderChildrenClosure();
        return constant($name);
    }
}
