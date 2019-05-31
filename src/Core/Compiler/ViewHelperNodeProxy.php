<?php
namespace TYPO3Fluid\Fluid\Core\Compiler;


use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;

class ViewHelperNodeProxy extends ViewHelperNode
{
    public function __construct(RenderingContextInterface $renderingContext, $namespace = null, $identifier = null, array $arguments = [], ?ParsingState $state = null)
    {

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