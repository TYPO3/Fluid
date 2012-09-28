<?php
namespace TYPO3\Fluid\Core\Parser\SyntaxTree;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


/**
 * Numeric Syntax Tree Node - is a container for numerics.
 *
 */
class NumericNode extends \TYPO3\Fluid\Core\Parser\SyntaxTree\AbstractNode {

	/**
	 * Contents of the numeric node
	 * @var number
	 */
	protected $value;

	/**
	 * Constructor.
	 *
	 * @param string|number $value value to store in this numericNode
	 * @throws \TYPO3\Fluid\Core\Parser\Exception
	 */
	public function __construct($value) {
		if (!is_numeric($value)) {
			throw new \TYPO3\Fluid\Core\Parser\Exception('Numeric node requires an argument of type number, "' . gettype($value) . '" given.');
		}
		$this->value = $value + 0;
	}

	/**
	 * Return the value associated to the syntax tree.
	 *
	 * @param \TYPO3\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
	 * @return number the value stored in this node/subtree.
	 */
	public function evaluate(\TYPO3\Fluid\Core\Rendering\RenderingContextInterface $renderingContext) {
		return $this->value;
	}

	/**
	 * Getter for value
	 *
	 * @return number The value of this node
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * NumericNode does not allow adding child nodes, so this will always throw an exception.
	 *
	 * @param \TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface $childNode The subnode to add
	 * @throws \TYPO3\Fluid\Core\Parser\Exception
	 * @return void
	 */
	public function addChildNode(\TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface $childNode) {
		throw new \TYPO3\Fluid\Core\Parser\Exception('Numeric nodes may not contain child nodes, tried to add "' . get_class($childNode) . '".');
	}
}

?>