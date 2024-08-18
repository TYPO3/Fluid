<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;

/**
 * Node which will call a ViewHelper associated with this node.
 */
class ViewHelperNode extends AbstractNode
{
    protected string $viewHelperClassName;

    /**
     * @var NodeInterface[]
     */
    protected array $arguments = [];

    protected ViewHelperInterface $uninitializedViewHelper;

    /**
     * @var ArgumentDefinition[]
     */
    protected array $argumentDefinitions = [];

    /**
     * Constructor.
     *
     * @param RenderingContextInterface $renderingContext a RenderingContext, provided by invoker
     * @param string $namespace the namespace identifier of the ViewHelper.
     * @param string $identifier the name of the ViewHelper to render, inside the namespace provided.
     * @param NodeInterface[] $arguments Arguments of view helper - each value is a RootNode.
     */
    public function __construct(RenderingContextInterface $renderingContext, string $namespace, string $identifier, array $arguments)
    {
        $resolver = $renderingContext->getViewHelperResolver();
        $this->arguments = $arguments;
        $this->viewHelperClassName = $resolver->resolveViewHelperClassName($namespace, $identifier);
        $this->uninitializedViewHelper = $resolver->createViewHelperInstanceFromClassName($this->viewHelperClassName);
        $this->uninitializedViewHelper->setViewHelperNode($this);
        // Note: RenderingContext required here though replaced later. See https://github.com/TYPO3Fluid/Fluid/pull/93
        $this->uninitializedViewHelper->setRenderingContext($renderingContext);
        $this->argumentDefinitions = $resolver->getArgumentDefinitionsForViewHelper($this->uninitializedViewHelper);
    }

    /**
     * @return ArgumentDefinition[]
     */
    public function getArgumentDefinitions(): array
    {
        return $this->argumentDefinitions;
    }

    /**
     * Returns the attached (but still uninitialized) ViewHelper for this ViewHelperNode.
     * We need this method because sometimes Interceptors need to ask some information from the ViewHelper.
     */
    public function getUninitializedViewHelper(): ViewHelperInterface
    {
        return $this->uninitializedViewHelper;
    }

    /**
     * Get class name of view helper
     *
     * @return string Class Name of associated view helper
     */
    public function getViewHelperClassName(): string
    {
        return $this->viewHelperClassName;
    }

    /**
     * @internal only needed for compiling templates
     * @return NodeInterface[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @internal only needed for compiling templates
     */
    public function getArgumentDefinition($argumentName): ?ArgumentDefinition
    {
        return $this->argumentDefinitions[$argumentName] ?? null;
    }

    public function addChildNode(NodeInterface $childNode): void
    {
        parent::addChildNode($childNode);
        $this->uninitializedViewHelper->setChildNodes($this->childNodes);
    }

    /**
     * Call the view helper associated with this object.
     *
     * First, it evaluates the arguments of the view helper.
     *
     * If the view helper implements \TYPO3Fluid\Fluid\Core\ViewHelper\ChildNodeAccessInterface,
     * it calls setChildNodes(array childNodes) on the view helper.
     *
     * Afterward, checks that the view helper did not leave a variable lying around.
     *
     * @param RenderingContextInterface $renderingContext
     * @return mixed evaluated node after the view helper has been called. This can be of any type,
     *               as ViewHelpers can return any type.
     */
    public function evaluate(RenderingContextInterface $renderingContext): mixed
    {
        // This is added as a safe-off, currently no evidence that we need this here like in convert().
        // See: https://github.com/TYPO3/Fluid/issues/804
        $this->updateViewHelperNodeInViewHelper();
        return $renderingContext->getViewHelperInvoker()->invoke($this->uninitializedViewHelper, $this->arguments, $renderingContext);
    }

    public function convert(TemplateCompiler $templateCompiler): array
    {
        // We need this here to avoid https://github.com/TYPO3/Fluid/issues/804.
        $this->updateViewHelperNodeInViewHelper();
        return $this->uninitializedViewHelper->convert($templateCompiler);
    }

    /**
     * Ensure correct ViewHelperNode (this) reference in the uninitialized ViewHelper instance.
     */
    protected function updateViewHelperNodeInViewHelper(): void
    {
        // Custom ViewHelperResolver can and are implemented providing the ability to instantiate ViewHelpers through
        // a DependencyInjection system like Symfony DI, for example done by TYPO3. Due to the nature, instances may be
        // set as shared, which means that changes to property reflects the latest set state. Therefore, we need to set
        // the current ViewHelperNode to a viewhelper instance to ensure correct context.
        // See https://github.com/TYPO3/Fluid/issues/804
        // @todo We should evaluate if we can get rid of this state and better pass it around.
        $this->uninitializedViewHelper->setViewHelperNode($this);
    }
}
