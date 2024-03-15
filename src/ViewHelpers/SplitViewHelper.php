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
 * The SplitViewHelper splits a string by the specified separator, which
 * results in an array. The number of values in the resulting array can
 * be limited with the limit parameter, which results in an array where
 * the last item contains the remaining unsplit string.
 *
 * This ViewHelper mimicks PHP's :php:`explode()` function.
 *
 *
 * Examples
 * ========
 *
 * Split with a separator
 * -----------------------
 * ::
 *
 *    <f:split value="1,5,8" separator="," />
 *
 * .. code-block:: text
 *
 *    {0: '1', 1: '5', 2: '8'}
 *
 *
 * Split using tag content as value
 * --------------------------------
 *
 * ::
 *
 *    <f:split separator="-">1-5-8</f:split>
 *
 * .. code-block:: text
 *
 *    {0: '1', 1: '5', 2: '8'}
 *
 *
 * Split with a limit
 * -------------------
 *
 * ::
 *
 *    <f:split value="1,5,8" separator="," limit="2" />
 *
 * .. code-block:: text
 *
 *    {0: '1', 1: '5,8'}
 */
final class SplitViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var bool
     */
    protected $escapeChildren = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'string', 'The string to explode');
        $this->registerArgument('separator', 'string', 'Separator string to explode with', true);
        $this->registerArgument('limit', 'int', 'If limit is positive, a maximum of $limit items will be returned. If limit is negative, all items except for the last $limit items will be returned. 0 will be treated as 1.', false, PHP_INT_MAX);
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): array
    {
        $value = $arguments['value'] ?? $renderChildrenClosure();
        if (!is_string($value)) {
            throw new \InvalidArgumentException('Value to be split must be a string: ' . $value, 1705250408);
        }

        return explode($arguments['separator'], $value, $arguments['limit']);
    }
}
