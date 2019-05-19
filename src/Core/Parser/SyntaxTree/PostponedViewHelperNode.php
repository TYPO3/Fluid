<?php
namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;

/**
 * Postponed Validation ViewHelperNode
 *
 * Postpones the validation and boolean argument rewrites
 * until a specific method is called (allowing this to be
 * skipped if one wishes to create a syntax tree that does
 * not depend on ViewHelper classes being present).
 */
class PostponedViewHelperNode extends ViewHelperNode
{

    /**
     * @var string
     */
    protected $viewHelperClassName;

    /**
     * @var NodeInterface[]
     */
    protected $arguments = [];

    /**
     * @var ViewHelperInterface
     */
    protected $uninitializedViewHelper = null;

    /**
     * @var ArgumentDefinition[]
     */
    protected $argumentDefinitions = [];

    /**
     * @var string
     */
    protected $pointerTemplateCode = null;

    /**
     * @var string
     */
    protected $namespace = '';

    /**
     * @var string
     */
    protected $identifier = '';

    /**
     * Constructor.
     *
     * @param RenderingContextInterface $renderingContext a RenderingContext, provided by invoker
     * @param string $namespace the namespace identifier of the ViewHelper.
     * @param string $identifier the name of the ViewHelper to render, inside the namespace provided.
     * @param NodeInterface[] $arguments Arguments of view helper - each value is a RootNode.
     * @param ParsingState|null $state
     */
    public function __construct(RenderingContextInterface $renderingContext, $namespace, $identifier, array $arguments = [], ParsingState $state = null)
    {
        $this->renderingContext = $renderingContext;
        $this->namespace = $namespace;
        $this->identifier = $identifier;
        $this->arguments = $arguments;
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @return ViewHelperInterface
     */
    public function getUninitializedViewHelper()
    {
        $resolver = $this->renderingContext->getViewHelperResolver();
        $this->viewHelperClassName = $resolver->resolveViewHelperClassName($this->namespace, $this->identifier);
        $this->uninitializedViewHelper = $resolver->createViewHelperInstanceFromClassName($this->viewHelperClassName);
        $this->uninitializedViewHelper->setRenderingContext($this->renderingContext);
        $this->uninitializedViewHelper->setViewHelperNode($this);
        $this->argumentDefinitions = $resolver->getArgumentDefinitionsForViewHelper($this->uninitializedViewHelper);
        return $this->uninitializedViewHelper;
    }

    /**
     * @param array $arguments
     * @param ParsingState $state
     */
    public function finalizeNode(array $arguments, ParsingState $state): void
    {
        $this->arguments = $arguments;
        $this->rewriteBooleanNodesInArgumentsObjectTree($this->argumentDefinitions, $this->arguments);
        $this->validateArguments($this->argumentDefinitions, $this->arguments);
    }
}
