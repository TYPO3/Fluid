<?php
namespace TYPO3\Fluid\Core\Parser\SyntaxTree\Expression;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\Parser;
use TYPO3\Fluid\Core\Parser\SyntaxTree\AbstractNode;
use TYPO3\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Base class for nodes based on (shorthand) expressions.
 */
abstract class AbstractExpressionNode extends AbstractNode implements ExpressionNodeInterface {

	/**
	 * Contents of the text node
	 *
	 * @var string
	 */
	protected $expression;

	/**
	 * Constructor.
	 *
	 * @param string $expression The original expression that created this node.
	 * @throws Parser\Exception
	 */
	public function __construct($expression) {
		$this->expression = trim($expression, " \t\n\r\0\x0b");
	}

	/**
	 * Return the text associated to the syntax tree. Text from child nodes is
	 * appended to the text in the node's own text.
	 *
	 * @param RenderingContextInterface $renderingContext
	 * @return string the text stored in this node/subtree.
	 */
	public function evaluate(RenderingContextInterface $renderingContext) {
		return call_user_func_array(array(get_called_class(), 'evaluateExpression'), array($renderingContext, $this->expression));
	}

	/**
	 * Getter for returning the expression before parsing.
	 *
	 * @return string
	 */
	public function getExpression() {
		return $this->expression;
	}

	/**
	 * @param string $part
	 * @return string
	 */
	protected static function trimPart($part) {
		return trim($part, " \t\n\r\0\x0b{}");
	}

	/**
	 * @param mixed $candidate
	 * @param RenderingContextInterface $renderingContext
	 * @return mixed
	 */
	protected static function getTemplateVariableOrValueItself($candidate, RenderingContextInterface $renderingContext) {
		$variables = $renderingContext->getTemplateVariableContainer()->getAll();
		$suspect = Parser\SyntaxTree\ObjectAccessorNode::getPropertyPath($variables, $candidate, $renderingContext);
		if (NULL === $suspect) {
			return $candidate;
		}
		return $suspect;
	}

}
