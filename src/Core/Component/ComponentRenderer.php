<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Component;

use Exception;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;
use TYPO3Fluid\Fluid\ViewHelpers\SlotViewHelper;

final class ComponentRenderer implements ViewHelperInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $arguments = [];

    private ?ViewHelperNode $viewHelperNode = null;

    private RenderingContextInterface $renderingContext;

    /**
     * Stores rendering contexts in a situation where ViewHelpers are called recursively from inside
     * one of their child nodes. In that case, the rendering context can change during the recursion,
     * but needs to be restored properly after each run. Thus, we store a stack of rendering contexts
     * to be able to restore the initial state of the ViewHelper.
     *
     * @var RenderingContextInterface[]
     */
    protected array $renderingContextStack = [];

    /**
     * @return ArgumentDefinition[]
     */
    public function prepareArguments(): array
    {
        return [];
    }

    /**
     * @param array<string, mixed> $arguments
     */
    public function setArguments(array $arguments): void
    {
        $this->arguments = $arguments;
    }

    public function getContentArgumentName(): ?string
    {
        return null;
    }

    /**
     * @param NodeInterface[] $nodes
     */
    public function setChildNodes(array $nodes): void {}

    public function setViewHelperNode(ViewHelperNode $node): void
    {
        $this->viewHelperNode = $node;
    }

    /**
     * @param RenderingContextInterface $renderingContext
     */
    public function setRenderingContext(RenderingContextInterface $renderingContext): void
    {
        $this->renderingContext = $renderingContext;
    }

    /**
     * Initialize the arguments of the ViewHelper, and call the render() method of the ViewHelper.
     *
     * @return mixed the rendered ViewHelper.
     */
    public function initializeArgumentsAndRender(): mixed
    {
        if (!isset($this->viewHelperNode)) {
            throw new Exception('ComponentRenderer can only be used in uncached templates.');
        }

        if (!$this->viewHelperNode->getResolverDelegate() instanceof ComponentViewFactoryInterface) {
            throw new Exception('Invalid component delegate.');
        }

        // @todo make ComponentInvoker configurable
        $resolverDelegate = $this->viewHelperNode->getResolverDelegate();
        $componentInvoker = new ComponentInvoker();
        return $componentInvoker->invoke(
            $resolverDelegate,
            $resolverDelegate->prepareTemplateName($this->viewHelperNode->getName()),
            $this->arguments,
            $this->renderingContext,
            [
                SlotViewHelper::DEFAULT_SLOT => $this->buildRenderChildrenClosure(),
            ],
        );
    }

    /**
     * Method which can be implemented in any ViewHelper if that ViewHelper desires
     * the ability to allow additional, undeclared, dynamic etc. arguments for the
     * node in the template. Do not implement this unless you need it!
     *
     * @param array<string, mixed> $arguments
     */
    public function handleAdditionalArguments(array $arguments): void
    {
        $this->arguments = [...$this->arguments, ...$arguments];
    }

    /**
     * Method which can be implemented in any ViewHelper if that ViewHelper desires
     * the ability to allow additional, undeclared, dynamic etc. arguments for the
     * node in the template. Do not implement this unless you need it!
     *
     * @param array<string, mixed> $arguments
     */
    public function validateAdditionalArguments(array $arguments): void {}

    /**
     * Called when being inside a cached template.
     *
     * @param \Closure $renderChildrenClosure
     */
    public function setRenderChildrenClosure(\Closure $renderChildrenClosure): void {}

    protected function buildRenderChildrenClosure(): callable
    {
        return function (): mixed {
            $this->renderingContextStack[] = $this->renderingContext;
            $result = $this->viewHelperNode->evaluateChildNodes($this->renderingContext);
            $this->setRenderingContext(array_pop($this->renderingContextStack));
            return $result;
        };
    }

    /**
     * Main method called at compile time to turn this ViewHelper
     * into a PHP representation written to compiled templates cache.
     *
     * This method is a layer above / earlier than compile() and returns
     * an array with identical structure as NodeInterface::convert().
     *
     * This method is considered Fluid internal, own view helpers should
     * refrain from overriding this. Overriding this method is typically
     * only needed when the compiled template code needs to be optimized
     * in a way compile() does not allow.
     *
     * There are some caveats when overriding this method: First, this
     * is not supported territory. Second, this may give additional
     * headaches when a VH with this method "overrides" an existing
     * VH via namespace declaration, since this adds a runtime dependency
     * to compile time. Don't do it.
     *
     * @internal Do not override except you know exactly what you are doing.
     *           Be prepared to maintain this in the future, it may break any time.
     *           Also, both method signature and return array structure may change any time.
     * @return array{initialization: string, execution: string}
     */
    public function convert(TemplateCompiler $templateCompiler): array
    {
        $initializationPhpCode = '// Rendering Component ' . $this->viewHelperNode->getNamespace() . ':' . $this->viewHelperNode->getName() . chr(10);

        $argumentsVariableName = $templateCompiler->variableName('arguments');
        $renderChildrenClosureVariableName = $templateCompiler->variableName('renderChildrenClosure');

        if (!$this->viewHelperNode->getResolverDelegate() instanceof ComponentViewFactoryInterface) {
            throw new Exception('Invalid component delegate.');
        }

        // @todo make ComponentInvoker configurable
        $resolverDelegate = $this->viewHelperNode->getResolverDelegate();
        $convertedViewHelperExecutionCode = sprintf(
            '(new %s)->invoke(%s, %s, %s, $renderingContext, %s)',
            ComponentInvoker::class,
            var_export($resolverDelegate->getNamespace(), true),
            var_export($resolverDelegate->prepareTemplateName($this->viewHelperNode->getName()), true),
            $argumentsVariableName,
            '[\'' . SlotViewHelper::DEFAULT_SLOT . '\' => ' . $renderChildrenClosureVariableName . ']',
        );

        // @todo make sure that arguments are escaped
        $accumulatedArgumentInitializationCode = '';
        $argumentInitializationCode = sprintf('%s = [' . chr(10), $argumentsVariableName);

        $arguments = $this->viewHelperNode->getArguments();
        foreach ($arguments as $argumentName => $argumentValue) {
            if ($argumentValue instanceof NodeInterface) {
                $converted = $argumentValue->convert($templateCompiler);
                if (!empty($converted['initialization'])) {
                    $accumulatedArgumentInitializationCode .= $converted['initialization'];
                }
                $argumentInitializationCode .= sprintf(
                    '\'%s\' => %s,' . chr(10),
                    $argumentName,
                    $converted['execution'],
                );
            } else {
                $argumentInitializationCode .= sprintf(
                    '\'%s\' => %s,' . chr(10),
                    $argumentName,
                    $argumentValue,
                );
            }
        }

        $argumentInitializationCode .= '];' . chr(10);

        // Build up closure which renders the child nodes
        $initializationPhpCode .= sprintf(
            '%s = %s;' . chr(10),
            $renderChildrenClosureVariableName,
            $templateCompiler->wrapChildNodesInClosure($this->viewHelperNode),
        );

        $initializationPhpCode .= $accumulatedArgumentInitializationCode . chr(10) . $argumentInitializationCode;
        return [
            'initialization' => $initializationPhpCode,
            'execution' => $convertedViewHelperExecutionCode,
        ];
    }

    public function isChildrenEscapingEnabled(): bool
    {
        return true;
    }

    public function isOutputEscapingEnabled(): bool
    {
        return false;
    }
}
