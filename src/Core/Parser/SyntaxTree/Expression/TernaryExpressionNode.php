<?php
namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Ternary Condition Node - allows the shorthand version
 * of a condition to be written as `{var ? thenvar : elsevar}`
 */
class TernaryExpressionNode extends AbstractExpressionNode {

	/**
	 * Pattern which detects ternary conditions written in shorthand
	 * syntax, e.g. {checkvar ? thenvar : elsevar}.
	 */
	public static $detectionExpression = '/
		(
			{                                # Start of shorthand syntax
				(?:                          # Math expression is composed of...
					[a-zA-Z0-9.]+          # Check variable side
					[\s]+\?[\s]+
					[a-zA-Z0-9.\s]+          # Then variable side
					[\s]+:[\s]+
					[a-zA-Z0-9.\s]+          # Else variable side
				)
			}                                # End of shorthand syntax
		)/x';

	/**
	 * @param RenderingContextInterface $renderingContext
	 * @param string $expression
	 * @param array $matches
	 * @return mixed
	 */
	public static function evaluateExpression(RenderingContextInterface $renderingContext, $expression, array $matches) {
		$parts = preg_split('/([\?:])/s', $expression);
		$parts = array_map(array(__CLASS__, 'trimPart'), $parts);
		list ($check, $then, $else) = $parts;
		$checkResult = Parser\SyntaxTree\BooleanNode::convertToBoolean(parent::getTemplateVariableOrValueItself($check, $renderingContext));
		if ($checkResult) {
			return parent::getTemplateVariableOrValueItself($then, $renderingContext);
		} else {
			return parent::getTemplateVariableOrValueItself($else, $renderingContext);
		}
	}

}
