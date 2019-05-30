<?php
namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Root node of every syntax tree.
 */
class RootNode extends AbstractNode
{
    public function addChildNode(NodeInterface $childNode)
    {
        if ($childNode instanceof RootNode) {
            // Assimilate child nodes instead of allowing a root node inside a root node.
            foreach ($childNode->getChildNodes() as $node) {
                parent::addChildNode($node);
            }
            return $this;
        }
        return parent::addChildNode($childNode);
    }

    /**
     * Evaluate the root node, by evaluating the subtree.
     *
     * @param RenderingContextInterface $renderingContext
     * @return mixed Evaluated subtree
     */
    public function evaluate(RenderingContextInterface $renderingContext)
    {
        return $this->evaluateChildNodes($renderingContext);
    }

}
