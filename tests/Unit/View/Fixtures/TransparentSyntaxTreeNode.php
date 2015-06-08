<?php
namespace NamelessCoder\Fluid\View\Fixture;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\Core\Parser\SyntaxTree\AbstractNode;
use NamelessCoder\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * [Enter description here]
 *
 */
class TransparentSyntaxTreeNode extends AbstractNode {
	public $variableContainer;

	public function evaluate(RenderingContextInterface $renderingContext) {
	}
}
