<?php
namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Math Expression Syntax Node - is a container for numeric values.
 */
class MathExpressionNode extends AbstractExpressionNode
{

    /**
     * Pattern which detects the mathematical expressions with either
     * object accessor expressions or numbers on left and right hand
     * side of a mathematical operator inside curly braces, e.g.:
     *
     * {variable * 10}, {100 / variable}, {variable + variable2} etc.
     */
    public static $detectionExpression = '/
		(
			{                                # Start of shorthand syntax
				(?:                          # Math expression is composed of...
					[a-zA-Z0-9\.]+(?:[\s]?[*+\^\/\%\-]{1}[\s]?[a-zA-Z0-9\.]+)+   # Various math expressions left and right sides with any spaces
					|(?R)                    # Other expressions inside
				)+
			}                                # End of shorthand syntax
		)/x';

    /**
     * @param RenderingContextInterface $renderingContext
     * @param string $expression
     * @param array $matches
     * @return integer|float
     */
    public static function evaluateExpression(RenderingContextInterface $renderingContext, $expression, array $matches)
    {
        // Split the expression on all recognized operators
        $matches = [];
        preg_match_all('/([+\-*\^\/\%]|[a-zA-Z0-9\.]+)/s', $expression, $matches);
        $matches[0] = array_map('trim', $matches[0]);
        // Like the BooleanNode, we dumb down the processing logic to not apply
        // any special precedence on the priority of operators. We simply process
        // them in order.
        $result = array_shift($matches[0]);
        $result = static::getTemplateVariableOrValueItself($result, $renderingContext);
        $operator = null;
        $operators = ['*', '^', '-', '+', '/', '%'];
        foreach ($matches[0] as $part) {
            if (in_array($part, $operators)) {
                $operator = $part;
            } else {
                $part = static::getTemplateVariableOrValueItself($part, $renderingContext);
                $result = self::evaluateOperation($result, $operator, $part);
            }
        }
        return $result;
    }

    /**
     * @param integer|float $left
     * @param string $operator
     * @param integer|float $right
     * @return integer|float
     */
    protected static function evaluateOperation($left, $operator, $right)
    {
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
