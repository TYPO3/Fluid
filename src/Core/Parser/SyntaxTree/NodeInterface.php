<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
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
     * @return mixed Normally, any type can be returned - in case it is concatenated with a string, a string is returned.
     */
    public function evaluateChildNodes(RenderingContextInterface $renderingContext): mixed;

    /**
     * Returns all child nodes for a given node.
     *
     * @return \TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface[] A list of nodes
     */
    public function getChildNodes(): array;

    /**
     * Appends a sub node to this node. Is used inside the parser to append children
     *
     * @param NodeInterface $childNode The sub node to add
     */
    public function addChildNode(NodeInterface $childNode): void;

    /**
     * Evaluates the node - can return not only strings, but arbitary objects.
     *
     * @param RenderingContextInterface $renderingContext
     * @return mixed Evaluated node
     * @todo add missing return type with Fluid v5 together with ExpressionNodeInterface
     */
    public function evaluate(RenderingContextInterface $renderingContext);

    /**
     * Compile the Node to a PHP representation, returning an array with
     * exactly two keys which contain strings:
     *
     * - "initialization" contains PHP code which is inserted *before* the actual
     *                    rendering call. Must be valid, i.e. end with semicolon.
     * - "execution" contains *a single PHP instruction* which needs to return the
     *               rendered output of the given element. Should NOT end with semicolon.
     *
     * @return array{initialization: string, execution: string|number}
     * @internal There is a rather "hard" list of nodes within Fluid that are
     *           only hard to override by changing TemplateParser. As such,
     *           it's usually not needed to add new nodes that need different
     *           convert() processing at compile time.
     */
    public function convert(TemplateCompiler $templateCompiler): array;
}
