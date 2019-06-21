<?php
namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Type Casting Node - allows the shorthand version
 * of a condition to be written as `{var ? thenvar : elsevar}`
 */
class CastingExpressionNode extends AbstractExpressionNode
{

    /**
     * @var array
     */
    protected static $validTypes = [
        'integer', 'boolean', 'string', 'float', 'array', 'DateTime'
    ];

    /**
     * Pattern which detects ternary conditions written in shorthand
     * syntax, e.g. {some.variable as integer}. The right-hand side
     * of the expression can also be a variable containing the type
     * of the variable.
     */
    public static $detectionExpression = '/
		(
			{                                # Start of shorthand syntax
				(?:                          # Math expression is composed of...
					[_a-zA-Z0-9.]+            # Template variable object access path
					[\s]+as[\s]+             # A single space, then "as", then a single space
					[_a-zA-Z0-9.\s]+          # Casting-to-type side
				)
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
        $expression = trim($expression, '{}');
        list ($variable, $type) = explode(' as ', $expression);
        $variable = static::getTemplateVariableOrValueItself($variable, $renderingContext);
        if (!in_array($type, self::$validTypes)) {
            $type = static::getTemplateVariableOrValueItself($type, $renderingContext);
        }
        if (!in_array($type, self::$validTypes)) {
            throw new ExpressionException(
                sprintf(
                    'Invalid target conversion type "%s" specified in casting expression "{%s}".',
                    $type,
                    $expression
                )
            );
        }
        return self::convert($variable, $type);
    }

    /**
     * @param mixed $variable
     * @param string $type
     * @return mixed
     */
    protected static function convert($variable, $type)
    {
        $value = null;
        if ($type === 'integer') {
            $value = (integer) $variable;
        } elseif ($type === 'boolean') {
            $value = (boolean) $variable;
        } elseif ($type === 'string') {
            $value = (string) $variable;
        } elseif ($type === 'float') {
            $value = (float) $variable;
        } elseif ($type === 'DateTime') {
            $value = self::convertToDateTime($variable);
        } elseif ($type === 'array') {
            $value = (array) self::convertToArray($variable);
        }
        return $value;
    }

    /**
     * @param mixed $variable
     * @return \DateTime|false
     */
    protected static function convertToDateTime($variable)
    {
        if (preg_match_all('/[a-z]+/i', $variable)) {
            return new \DateTime($variable);
        }
        return \DateTime::createFromFormat('U', (integer) $variable);
    }

    /**
     * @param mixed $variable
     * @return array
     */
    protected static function convertToArray($variable)
    {
        if (is_array($variable)) {
            return $variable;
        } elseif (is_string($variable) && strpos($variable, ',')) {
            return array_map('trim', explode(',', $variable));
        } elseif (is_object($variable) && $variable instanceof \Iterator) {
            $array = [];
            foreach ($variable as $key => $value) {
                $array[$key] = $value;
            }
            return $array;
        } elseif (is_object($variable) && method_exists($variable, 'toArray')) {
            return $variable->toArray();
        } elseif (is_bool($variable)) {
            return [];
        } else {
            return [$variable];
        }
    }
}
