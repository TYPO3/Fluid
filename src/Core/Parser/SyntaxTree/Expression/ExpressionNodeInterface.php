<?php
namespace NamelessCoder\Fluid\Core\Parser\SyntaxTree\Expression;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use NamelessCoder\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Interface for shorthand expression node types
 */
interface ExpressionNodeInterface extends NodeInterface {

	/**
	 * Evaluates the expression by delegating it to the
	 * resolved ExpressionNode type.
	 *
	 * @param RenderingContextInterface $renderingContext
	 * @return mixed
	 */
	public function evaluate(RenderingContextInterface $renderingContext);

	/**
	 * Evaluate expression, static version. Should return
	 * the exact same value as evaluate() but should be
	 * able to do so in a statically called context.
	 *
	 * @param RenderingContextInterface $renderingContext
	 * @param string $expression
	 * @return mixed
	 */
	public static function evaluateExpression(RenderingContextInterface $renderingContext, $expression);

	/**
	 * Getter for returning the expression before parsing.
	 *
	 * @return string
	 */
	public function getExpression();

}
