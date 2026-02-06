<?php
namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\BooleanParser;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\VariableExtractor;

/**
 * Null Coalescing Condition Node - allows the shorthand of a null check
 * for a default value as `{nullableVar ?? valueIfNull}`
 */
class NullcoalescingExpressionNode extends AbstractExpressionNode
{

    /**
     * Pattern which detects null coalescing conditions written in shorthand
     * syntax, e.g. {nullableVar ?? valueIfNull}.
     */
    public static $detectionExpression = '/
		(
			{                                                               # Start of shorthand syntax
				(?:                                                         # Math expression is composed of...
					[\\!_a-zA-Z0-9.\(\)\!\|\&\\\'\'\"\=\<\>\%\s\{\}\:\,]+    # Check variable side
					[\s]?\?\?[\s]?
					[_a-zA-Z0-9.\s\'\"\\.]+                                  # Fallback value side
				)
			}                                                               # End of shorthand syntax
		)/x';

    /**
     * Filter out variable names form expression
     */
    protected static $variableDetection = '/[^\'_a-zA-Z0-9\.\\\\]{0,1}([_a-zA-Z0-9\.\\\\]*)[^\']{0,1}/';

    /**
     * @param RenderingContextInterface $renderingContext
     * @param string $expression
     * @param array $matches
     * @return mixed
     */
    public static function evaluateExpression(RenderingContextInterface $renderingContext, $expression, array $matches)
    {
        $parts = preg_split('/([\?\?])/s', $expression);
        $parts = array_map([__CLASS__, 'trimPart'], $parts);

        foreach($parts as $part) {
            $value = static::getTemplateVariableOrValueItself($part, $renderingContext);
            if(!is_null($value)) {
                return $value;
            }
        }

        return null;
    }


    /**
     * @param mixed $candidate
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    protected static function getTemplateVariableOrValueItself($candidate, RenderingContextInterface $renderingContext)
    {
        $variables = $renderingContext->getVariableProvider()->getAll();
        $extractor = new VariableExtractor();
        $suspect = $extractor->getByPath($variables, $candidate);

        if (is_numeric($candidate)) {
            $suspect = $candidate;
        } elseif (mb_strpos($candidate, '\'') === 0) {
            $suspect = trim($candidate, '\'');
        } elseif (mb_strpos($candidate, '"') === 0) {
            $suspect = trim($candidate, '"');
        }

        return $suspect;
    }
}
