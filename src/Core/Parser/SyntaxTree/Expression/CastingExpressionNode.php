<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Type Casting Expression
 * Allows casting variables to specific types, for example `{myVariable as boolean}`
 *
 * @internal
 * @todo Make class final.
 */
class CastingExpressionNode extends AbstractExpressionNode
{
    protected static array $validTypes = [
        'integer', 'boolean', 'string', 'float', 'array', 'DateTime',
    ];

    /**
     * Pattern which detects ternary conditions written in shorthand
     * syntax, e.g. {some.variable as integer}. The right-hand side
     * of the expression can also be a variable containing the type
     * of the variable.
     */
    public static string $detectionExpression = '/
		(
			{                                # Start of shorthand syntax
				(?:                          # Math expression is composed of...
					[_a-zA-Z0-9.]+            # Template variable object access path
					[\s]+as[\s]+             # A single space, then "as", then a single space
					[_a-zA-Z0-9.\s]+          # Casting-to-type side
				)
			}                                # End of shorthand syntax
		)/x';

    public static function evaluateExpression(RenderingContextInterface $renderingContext, string $expression, array $matches): mixed
    {
        $expression = trim($expression, '{}');
        list($variable, $type) = explode(' as ', $expression);
        $variable = static::getTemplateVariableOrValueItself($variable, $renderingContext);
        if (!in_array($type, self::$validTypes)) {
            $type = static::getTemplateVariableOrValueItself($type, $renderingContext);
        }
        if (!in_array($type, self::$validTypes)) {
            throw new ExpressionException(
                sprintf(
                    'Invalid target conversion type "%s" specified in casting expression "{%s}".',
                    $type,
                    $expression,
                ),
            );
        }
        return self::convertStatic($variable, $type);
    }

    protected static function convertStatic(mixed $variable, string $type): mixed
    {
        $value = null;
        if ($type === 'integer') {
            $value = (int)$variable;
        } elseif ($type === 'boolean') {
            $value = (bool)$variable;
        } elseif ($type === 'string') {
            $value = (string)$variable;
        } elseif ($type === 'float') {
            $value = (float)$variable;
        } elseif ($type === 'DateTime') {
            $value = self::convertToDateTime($variable);
        } elseif ($type === 'array') {
            $value = (array)self::convertToArray($variable);
        }
        return $value;
    }

    protected static function convertToDateTime(mixed $variable): \DateTime|false
    {
        if (is_string($variable) || $variable instanceof \Stringable && preg_match_all('/[a-z]+/i', (string)$variable)) {
            return new \DateTime($variable);
        }
        return \DateTime::createFromFormat('U', (string)(int)$variable);
    }

    protected static function convertToArray(mixed $variable): array
    {
        if (is_array($variable)) {
            return $variable;
        }
        if (is_string($variable) && strpos($variable, ',')) {
            return array_map('trim', explode(',', $variable));
        }
        if ($variable instanceof \Iterator) {
            $array = [];
            foreach ($variable as $key => $value) {
                $array[$key] = $value;
            }
            return $array;
        }
        if (is_object($variable) && method_exists($variable, 'toArray')) {
            return $variable->toArray();
        }
        if (is_bool($variable)) {
            return [];
        }
        return [$variable];
    }
}
