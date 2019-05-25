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

    /**
     * Returns one of the following:
     *
     * - Itself, if there is more than one child node and one or more nodes are not TextNode or NumericNode
     * - A plain value if there is a single child node of type TextNode or NumericNode
     * - The one child node if there is only a single child node not of type TextNode or NumericNode
     * - Null if there are no child nodes at all.
     *
     * @param bool $extractNode If TRUE, will extract the value of a single node if the node type contains a scalar value
     * @return RootNode|string|int|float|null
     */
    public function flatten(bool $extractNode = true)
    {
        if (empty($this->childNodes)) {
            return null;
        }
        $nodesCounted = 0;
        $containsNonTextNonNumericNodes = false;
        foreach ($this->childNodes as $childNode) {
            ++$nodesCounted;
            if (!($childNode instanceof TextNode || $childNode instanceof NumericNode)) {
                $containsNonTextNonNumericNodes = true;
                break;
            }
        }
        if ($nodesCounted === 1) {
            if ($containsNonTextNonNumericNodes) {
                return $this->childNodes[0];
            }
        }
        if (!$containsNonTextNonNumericNodes) {
            $value = array_reduce($this->childNodes, function($initial, NodeInterface $node) {
                if ($node instanceof TextNode) {
                    return $initial . $node->getText();
                }
                if ($node instanceof NumericNode) {
                    return $initial . (string) $node->getValue();
                }
            }, '');
            if ($extractNode) {
                return $value;
            }
            return new TextNode($value);
        }
        return $this;
    }
}
