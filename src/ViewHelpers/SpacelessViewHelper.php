<?php
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Space Removal ViewHelper
 *
 * Removes redundant spaces between HTML tags while
 * preserving the whitespace that may be inside HTML
 * tags. Trims the final result before output.
 *
 * Heavily inspired by Twig's corresponding node type.
 *
 * <code title="Usage of f:spaceless">
 * <f:spaceless>
 * <div>
 *     <div>
 *         <div>text
 *
 * text</div>
 *     </div>
 * </div>
 * </code>
 * <output>
 * <div><div><div>text
 *
 * text</div></div></div>
 * </output>
 */
class SpacelessViewHelper extends AbstractViewHelper {

	/**
	 * @return string
	 */
	public function render() {
		return static::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
	}

	/**
	 * @param array $arguments
	 * @param \Closure $childClosure
	 * @param RenderingContextInterface $renderingContext
	 */
	public static function renderStatic(array $arguments, \Closure $childClosure, RenderingContextInterface $renderingContext) {
		return trim(preg_replace('/\\>\\s+\\</', '><', $childClosure()));
	}

}
