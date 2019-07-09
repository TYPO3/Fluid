<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Compiler;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;

/**
 * Proxy for compiling nodes that are also ViewHelper instances.
 */
class ViewHelperNodeProxy extends ViewHelperNode
{
    public function __construct(RenderingContextInterface $renderingContext, $namespace = null, $identifier = null, array $arguments = [], ?ParsingState $state = null)
    {
        unset($renderingContext, $namespace, $identifier, $arguments, $state);
    }

    /**
     * @param NodeInterface[] $childNodes
     */
    public function setChildNodes(array $childNodes): void
    {
        $this->childNodes = $childNodes;
    }

    /**
     * @param NodeInterface[] $arguments
     */
    public function setArguments(array $arguments): void
    {
        $this->arguments = $arguments;
    }

    /**
     * @param ViewHelperInterface $uninitializedViewHelper
     */
    public function setUninitializedViewHelper(ViewHelperInterface $uninitializedViewHelper): void
    {
        $this->uninitializedViewHelper = $uninitializedViewHelper;
        $this->viewHelperClassName = get_class($uninitializedViewHelper);
        $this->argumentDefinitions = $uninitializedViewHelper->prepareArguments();
    }

}