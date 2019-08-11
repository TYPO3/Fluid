<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\ViewHelpers\Expression;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\ExpressionComponentInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Math Expression ViewHelper, seconds as expression type
 */
class MathViewHelper extends AbstractViewHelper implements ExpressionComponentInterface
{
    protected $parts = [];

    /**
     * Possible operators, sorted by likely frequency of use to make
     * the strpos() check work as fast as possible for the most common
     * use cases.
     *
     * @var string
     */
    protected static $operators = '+-*/%^';

    public function __construct(iterable $parts = [])
    {
        $this->parts = $parts;
    }

    protected function initializeArguments()
    {
        $this->registerArgument('a', 'mixed', 'Numeric first value to calculate', true);
        $this->registerArgument('b', 'mixed', 'Numeric first value to calculate', true);
        $this->registerArgument('operator', 'string', 'Operator to use, e.g. +, -, %', true);
    }

    public static function matches(array $parts): bool
    {
        return isset($parts[2]) && strpos(static::$operators, $parts[1]) !== false;
    }

    public function evaluate(RenderingContextInterface $renderingContext)
    {
        $arguments = $this->getArguments()->setRenderingContext($renderingContext)->getArrayCopy();
        $parts = empty($this->parts) ? [$arguments['a'], $arguments['operator'], $arguments['b']] : $this->parts;
        $variable = array_shift($parts);
        $result = $this->resolveToNumericValue($variable, $renderingContext);
        $operator = null;
        $operators = str_split(static::$operators);
        foreach ($parts as $part) {
            if (in_array($part, $operators, true)) {
                $operator = $part;
            } else {
                $part = $this->resolveToNumericValue($part, $renderingContext);
                $result = $this->evaluateOperation($result, $operator, $part);
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
    protected function evaluateOperation($left, string $operator, $right)
    {
        switch ($operator) {
            case '%':
                return $left % $right;
            case '-':
                return $left - $right;
            case '*':
                return $left * $right;
            case '^':
                return pow($left, $right);
            case '/':
                return (integer) $right !== 0 ? $left / $right : 0;
            case '+':
            default:
                return $left + $right;
        }
    }

    protected function resolveToNumericValue($value, RenderingContextInterface $renderingContext)
    {
        if (is_object($value)) {
            if (!method_exists($value, '__toString')) {
                return 0;
            }
            $value = (string) $value;
        }
        if (!is_numeric($value)) {
            $value = $renderingContext->getVariableProvider()->get((string) $value);
        }
        return $value + 0;
    }
}
