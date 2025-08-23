<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * The RoundViewHelper rounds a float value with the specified precision and rounding mode.
 * The ViewHelper mimics PHP's :php:`round()` function.
 *
 * +------------------+-----------+-------------+
 * |  rounding modes  | PHP < 8.4 | PHP >= 8.4  |
 * +==================+===========+=============+
 * | HalfAwayFromZero |     Yes   |      Yes    |
 * +------------------+-----------+-------------+
 * | HalfTowardsZero  |     Yes   |      Yes    |
 * +------------------+-----------+-------------+
 * | HalfEven         |     Yes   |      Yes    |
 * +------------------+-----------+-------------+
 * | HalfOdd          |     Yes   |      Yes    |
 * +------------------+-----------+-------------+
 * | TowardsZero      |     No    |      Yes    |
 * +------------------+-----------+-------------+
 * | AwayFromZero     |     No    |      Yes    |
 * +------------------+-----------+-------------+
 * | NegativeInfinity |     No    |      Yes    |
 * +------------------+-----------+-------------+
 * | PositiveInfinity |     No    |      Yes    |
 * +------------------+-----------+-------------+
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
 * Round with specific precision and rounding mode
 * -----------------------------------------------
 * ::
 *
 *    <f:round value="123.456" precision="1" roundingMode="HalfAwayFromZero" />
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
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'float', 'The number that should be rounded');
        $this->registerArgument('precision', 'int', 'Rounding precision', false, 2);
        $this->registerArgument('roundingMode', 'string', 'Rounding mode', false, 'HalfAwayFromZero');
    }

    public function render(): float
    {
        $value = $this->arguments['value'] ?? $this->renderChildren();

        if (class_exists(\RoundingMode::class)) {
            $roundingMode = match ($this->arguments['roundingMode']) {
                'HalfAwayFromZero'  => \RoundingMode::HalfAwayFromZero,
                'HalfTowardsZero'   => \RoundingMode::HalfTowardsZero,
                'HalfEven'          => \RoundingMode::HalfEven,
                'HalfOdd'           => \RoundingMode::HalfOdd,
                'TowardsZero'       => \RoundingMode::TowardsZero,
                'AwayFromZero'      => \RoundingMode::AwayFromZero,
                'NegativeInfinity'  => \RoundingMode::NegativeInfinity,
                'PositiveInfinity'  => \RoundingMode::PositiveInfinity,
                default             => \RoundingMode::HalfAwayFromZero,
            };
        } else {
            $roundingMode = match ($this->arguments['roundingMode']) {
                'HalfAwayFromZero'  => \PHP_ROUND_HALF_UP,
                'HalfTowardsZero'   => \PHP_ROUND_HALF_DOWN,
                'HalfEven'          => \PHP_ROUND_HALF_EVEN,
                'HalfOdd'           => \PHP_ROUND_HALF_ODD,
                default             => \PHP_ROUND_HALF_UP,
            };
        }

        return round((float)$value, $this->arguments['precision'], $roundingMode);
    }
}
