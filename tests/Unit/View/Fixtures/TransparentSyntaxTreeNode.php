<?php
namespace TYPO3Fluid\Fluid\View\Fixture;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\AbstractNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * [Enter description here]
 *
 */
class TransparentSyntaxTreeNode extends AbstractNode
{
    public $variableContainer;

    public function evaluate(RenderingContextInterface $renderingContext)
    {
    }
}
