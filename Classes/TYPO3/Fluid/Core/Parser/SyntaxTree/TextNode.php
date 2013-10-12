<?php
namespace TYPO3\Fluid\Core\Parser\SyntaxTree;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Fluid\Core\Parser;
use TYPO3\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Text Syntax Tree Node - is a container for strings.
 */
class TextNode extends AbstractNode {

	/**
	 * Contents of the text node
	 *
	 * @var string
	 */
	protected $text;

	/**
	 * Constructor.
	 *
	 * @param string $text text to store in this textNode
	 * @throws Parser\Exception
	 */
	public function __construct($text) {
		if (!is_string($text)) {
			throw new Parser\Exception('Text node requires an argument of type string, "' . gettype($text) . '" given.');
		}
		$this->text = $text;
	}

	/**
	 * Return the text associated to the syntax tree. Text from child nodes is
	 * appended to the text in the node's own text.
	 *
	 * @param RenderingContextInterface $renderingContext
	 * @return string the text stored in this node/subtree.
	 */
	public function evaluate(RenderingContextInterface $renderingContext) {
		return $this->text . $this->evaluateChildNodes($renderingContext);
	}

	/**
	 * Getter for text
	 *
	 * @return string The text of this node
	 */
	public function getText() {
		return $this->text;
	}
}
