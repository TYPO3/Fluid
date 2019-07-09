<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Node in the syntax tree.
 */
interface NodeInterface
{

    /**
     * Evaluate all child nodes and return the evaluated results.
     *
     * @param RenderingContextInterface $renderingContext
     * @return mixed Normally, an object is returned - in case it is concatenated with a string, a string is returned.
     */
    public function evaluateChildNodes(RenderingContextInterface $renderingContext);

    /**
     * Returns all child nodes for a given node.
     *
     * @return array<\TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface> A list of nodes
     */
    public function getChildNodes(): array;

    /**
     * @param NodeInterface[] $childNodes
     * @return NodeInterface
     */
    public function setChildNodes(array $childNodes);

    /**
     * Appends a sub node to this node. Is used inside the parser to append children
     *
     * @param NodeInterface $childNode The sub node to add
     * @return self
     */
    public function addChildNode(NodeInterface $childNode): self;

    /**
     * Evaluates the node - can return not only strings, but arbitary objects.
     *
     * @param RenderingContextInterface $renderingContext
     * @return mixed Evaluated node
     */
    public function evaluate(RenderingContextInterface $renderingContext);

    /**
     * Returns one of the following:
     *
     * - Itself, if there is more than one child node and one or more nodes are not TextNode or NumericNode
     * - A plain value if there is a single child node of type TextNode or NumericNode
     * - The one child node if there is only a single child node not of type TextNode or NumericNode
     * - Null if there are no child nodes at all.
     *
     * @param bool $extractNode If TRUE, will extract the value of a single node if the node type contains a scalar value
     * @return NodeInterface|string|int|float|null
     */
    public function flatten(bool $extractNode = false);
}
