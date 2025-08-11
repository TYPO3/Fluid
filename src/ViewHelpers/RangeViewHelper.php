<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * The RangeViewHelper returns an array containing a range of integers.
 *
 * This ViewHelper mimicks PHP's :php:`range()` function.
 *
 * The following examples store the result in a variable because an array cannot
 * be outputted directly in a template.
 *
 * Examples
 * ========
 *
 * Increasing range
 * -----------------
 * ::
 *
 *    <f:variable name="result"><f:range start="1" end="5" /></f:variable>
 *
 * .. code-block:: text
 *
 *    {0: 1, 1: 2, 2: 3, 3: 4, 4: 5}
 *
 *
 * Inline increasing range
 * ------------------------
 *
 * ::
 *
 *    <f:variable name="result" value="{f:range(start: 1, end: 5)}" />
 *
 * .. code-block:: text
 *
 *    {0: 1, 1: 2, 2: 3, 3: 4, 4: 5}
 *
 *
 * Decreasing range
 * -----------------
 *
 * ::
 *
 *    <f:variable name="result" value="{f:range(start: 5, end: 0)}" />
 *
 * .. code-block:: text
 *
 *    {0: 5, 1: 4, 2: 3, 3: 2, 4: 1, 5: 0}
 *
 *
 * Increasing stepped range
 * -------------------------
 *
 * ::
 *
 *    <f:variable name="result" value="{f:range(start: 1, end: 6, step: 2)" />
 *
 * .. code-block:: text
 *
 *    {0: 1, 1: 3, 2: 5}
 *
 *
 * Decreasing stepped range
 * -------------------------
 *
 * ::
 *
 *    <f:variable name="result" value="{f:range(start: 5, end: 1, step: 2)" />
 *
 * .. code-block:: text
 *
 *    {0: 5, 1: 3, 2: 1}
 */
final class RangeViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('start', 'integer', 'First value of the sequence.', true);
        $this->registerArgument('end', 'integer', 'Last possible value of the sequence.', true);
        $this->registerArgument('step', 'integer', 'indicates by how much the produced sequence is progressed between values of the sequence.', false, 1);
    }

    public function render(): mixed
    {
        $step = $this->arguments['step'];
        if ($step === 0) {
            throw new \InvalidArgumentException(
                'The argument "step" cannot be 0 in view helper "' . static::class . '".',
                1754596304,
            );
        }
        return range(
            $this->arguments['start'],
            $this->arguments['end'],
            $step,
        );

    }
}
