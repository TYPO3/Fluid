<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Ternary Condition Node - allows the shorthand version
 * of a condition to be written as `{var ? thenvar : elsevar}`
 */
class TernaryExpressionNode extends AbstractExpressionNode
{
    /**
     * Matches possibilities:
     *
     * - {foo ? bar : baz}
     * - {foo ?: baz}
     *
     * But not:
     *
     * - {?bar:baz}
     * - {foo?bar:baz}
     * - {foo ?? bar}
     * - {foo ? bar : baz : more}
     *
     * And so on.
     *
     * @param array $parts
     * @return bool
     */
    public static function matches(array $parts): bool
    {
        return isset($parts[2]) && ($parts[1] === '?' && (($parts[2] ?? null) === ':' && !isset($parts[4])) || ($parts[3] ?? null) === ':' && !isset($parts[5]));
    }

    public function evaluateParts(RenderingContextInterface $renderingContext, iterable $parts)
    {
        $check = null;
        $then = null;
        $else = null;
        $expression = '';
        foreach ($parts as $part) {
            $expression .= $part . ' ';
            if ($part === ':' && $then === null) {
                $then = $check;
                continue;
            }

            if ($check === null) {
                $check = $part;
                continue;
            }

            if ($part === '?' || $part === ':') {
                continue;
            }

            if ($then === null) {
                $then = $part;
                continue;
            }

            if ($else === null) {
                $else = $part;
                break;
            }
        }

        $negated = false;
        if (!is_numeric($check)) {
            if ($check[0] === '!') {
                $check = substr($check, 1);
                $negated = true;
            }
            $check = static::getTemplateVariableOrValueItself($check, $renderingContext);
        }

        if (!is_numeric($then)) {
            $then = static::getTemplateVariableOrValueItself($then, $renderingContext);
        }

        if (!is_numeric($else)) {
            $else = static::getTemplateVariableOrValueItself($else, $renderingContext);
        }

        return $negated ? (!$check ? $then : $else) : ($check ? $then : $else);
    }

    /**
     * @param mixed $candidate
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    public static function getTemplateVariableOrValueItself($candidate, RenderingContextInterface $renderingContext)
    {
        $suspect = parent::getTemplateVariableOrValueItself($candidate, $renderingContext);
        if ($suspect === $candidate) {
            return $renderingContext->getTemplateParser()->unquoteString($suspect);
        }
        return $suspect;
    }
}
