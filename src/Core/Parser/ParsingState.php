<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Parser;

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\View;

/**
 * Stores all information relevant for one parsing pass - that is, the root node,
 * and the current stack of open nodes (nodeStack) and a variable container used
 * for PostParseFacets.
 */
class ParsingState implements ParsedTemplateInterface
{
    protected string $identifier;

    /**
     * Root node reference
     */
    protected RootNode $rootNode;

    /**
     * Array of node references currently open.
     *
     * @var NodeInterface[]
     */
    protected array $nodeStack = [];

    /**
     * Variable container where ViewHelpers implementing the PostParseFacet can
     * store things in.
     */
    protected VariableProviderInterface $variableContainer;

    protected bool $compilable = true;

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Injects a variable container to be used during parsing.
     */
    public function setVariableProvider(VariableProviderInterface $variableContainer): void
    {
        $this->variableContainer = $variableContainer;
    }

    /**
     * Set root node of this parsing state.
     */
    public function setRootNode(RootNode $rootNode): void
    {
        $this->rootNode = $rootNode;
    }

    /**
     * Get root node of this parsing state.
     */
    public function getRootNode(): RootNode
    {
        return $this->rootNode;
    }

    /**
     * Render the parsed template with rendering context
     *
     * @param RenderingContextInterface $renderingContext The rendering context to use
     */
    public function render(RenderingContextInterface $renderingContext): mixed
    {
        return $this->getRootNode()->evaluate($renderingContext);
    }

    /**
     * Push a node to the node stack. The node stack holds all currently open
     * templating tags.
     *
     * @param NodeInterface $node Node to push to node stack
     */
    public function pushNodeToStack(NodeInterface $node): void
    {
        $this->nodeStack[] = $node;
    }

    /**
     * Get the top stack element, without removing it.
     *
     * @return NodeInterface the top stack element.
     */
    public function getNodeFromStack(): NodeInterface
    {
        return $this->nodeStack[count($this->nodeStack) - 1];
    }

    /**
     * Pop the top stack element (=remove it) and return it back.
     *
     * @return NodeInterface the top stack element, which was removed.
     */
    public function popNodeFromStack(): NodeInterface
    {
        return array_pop($this->nodeStack);
    }

    /**
     * Count the size of the node stack
     *
     * @return int Number of elements on the node stack (i.e. number of currently open Fluid tags)
     */
    public function countNodeStack(): int
    {
        return count($this->nodeStack);
    }

    /**
     * Returns a variable container which will be then passed to the postParseFacet.
     *
     * @return VariableProviderInterface The variable container or null if none has been set yet
     */
    public function getVariableContainer(): VariableProviderInterface
    {
        return $this->variableContainer;
    }

    /**
     * Returns true if the current template has a template defined via <f:layout name="..." />
     */
    public function hasLayout(): bool
    {
        return $this->variableContainer->exists('layoutName');
    }

    /**
     * Returns the name of the layout that is defined within the current template via <f:layout name="..." />
     * If no layout is defined, this returns null.
     * This requires the current rendering context in order to be able to evaluate the layout name
     *
     * @throws View\Exception
     */
    public function getLayoutName(RenderingContextInterface $renderingContext): string|null|NodeInterface
    {
        $layoutName = $this->variableContainer->get('layoutName');
        return $layoutName instanceof RootNode ? $layoutName->evaluate($renderingContext) : $layoutName;
    }

    public function addCompiledNamespaces(RenderingContextInterface $renderingContext): void {}

    public function isCompilable(): bool
    {
        return $this->compilable;
    }

    public function setCompilable(bool $compilable): void
    {
        $this->compilable = $compilable;
    }

    public function isCompiled(): bool
    {
        return false;
    }
}
