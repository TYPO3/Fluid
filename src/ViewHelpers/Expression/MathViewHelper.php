<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\ViewHelpers\Expression;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollection;
use TYPO3Fluid\Fluid\Component\ExpressionComponentInterface;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Math Expression ViewHelper, seconds as expression type
 */
class MathViewHelper extends AbstractViewHelper implements ExpressionComponentInterface
{
    protected $parts = [];

    public function __construct(iterable $parts = [])
    {
        $this->parts = $parts;
    }

    /**
     * Possible operators, sorted by likely frequency of use to make
     * the strpos() check work as fast as possible for the most common
     * use cases.
     *
     * @var string
     */
    protected static $operators = '+-*/%^';

    protected function initializeArguments()
    {
        $this->registerArgument('a', 'mixed', 'Numeric first value to calculate', true);
        $this->registerArgument('b', 'mixed', 'Numeric first value to calculate', true);
        $this->registerArgument('operator', 'string', 'Operator to use, e.g. +, -, %', true);
    }

    /**
     * @param array $parts
     * @return bool
     */
    public static function matches(array $parts): bool
    {
        return isset($parts[2]) && strpos(static::$operators, $parts[1]) !== false;
    }

    public function execute(RenderingContextInterface $renderingContext, ?ArgumentCollection $arguments = null)
    {
        $parts = empty($this->parts) ? [$arguments['a'], $arguments['operator'], $arguments['b']] : $this->parts;
        $variable = array_shift($parts);
        $result = $renderingContext->getVariableProvider()->get($variable) ?? $variable;
        $operator = null;
        $operators = str_split(static::$operators);
        foreach ($parts as $part) {
            if (in_array($part, $operators)) {
                $operator = $part;
            } else {
                $part = $renderingContext->getVariableProvider()->get($part) ?? $part;

                if (!is_string($operator)) {
                    throw new Exception(
                        sprintf(
                            'Invalid operator type (%s) given, it must be a valid string, e.g. "+" or "-"!',
                            gettype($operator)
                        ),
                        1561121432
                    );
                }

                $result = self::evaluateOperation($result, $operator, $part);
            }
        }
        return $result + 0;
    }

    /**
     * @param integer|float $left
     * @param string $operator
     * @param integer|float $right
     * @return integer|float
     */
    protected static function evaluateOperation($left, string $operator, $right)
    {
        if (!is_numeric($left)) {
            $left = 0;
        }
        if (!is_numeric($right)) {
            $right = 0;
        }
        if ($operator === '%') {
            return $left % $right;
        } elseif ($operator === '-') {
            return $left - $right;
        } elseif ($operator === '+') {
            return $left + $right;
        } elseif ($operator === '*') {
            return $left * $right;
        } elseif ($operator === '/') {
            return (integer) $right !== 0 ? $left / $right : 0;
        } elseif ($operator === '^') {
            return pow($left, $right);
        }
        return 0;
    }
}
