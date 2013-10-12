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

use TYPO3\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Root node of every syntax tree.
 */
class RootNode extends AbstractNode {

	/**
	 * Evaluate the root node, by evaluating the subtree.
	 *
	 * @param RenderingContextInterface $renderingContext
	 * @return mixed Evaluated subtree
	 */
	public function evaluate(RenderingContextInterface $renderingContext) {
		return $this->evaluateChildNodes($renderingContext);
	}
}
