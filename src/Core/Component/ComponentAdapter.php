<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Component;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\Exception as ParserException;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolverDelegateInterface;
use TYPO3Fluid\Fluid\ViewHelpers\FragmentViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\SlotViewHelper;

/**
 * The ComponentAdapter is a special ViewHelper that is responsible for adapting the logic of
 * components (= rendering a separate template, e. g. a Fluid TemplateView) to Fluid's ViewHelper
 * logic. In contrast to other ViewHelpers, this implementation can be responsible for multiple
 * tags within one defined namespace by interpreting the ViewHelperNode name from within this
 * ViewHelper implementation code.
 * This ViewHelper is only called from uncached templates and delegates the relevant tasks
 * to the responsible ViewHelperResolver delegate, which must also implement the
 * ComponentDefinitionProviderInterface.
 *
 * @internal
 */
final class ComponentAdapter implements ViewHelperInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $arguments = [];

    private ViewHelperNode $viewHelperNode;

    private RenderingContextInterface $renderingContext;

    /**
     * Stores rendering contexts in a situation where ViewHelpers are called recursively from inside
     * one of their child nodes. In that case, the rendering context can change during the recursion,
     * but needs to be restored properly after each run. Thus, we store a stack of rendering contexts
     * to be able to restore the initial state of the ViewHelper.
     *
     * @var RenderingContextInterface[]
     */
    private array $renderingContextStack = [];

    public function setViewHelperNode(ViewHelperNode $node): void
    {
        $this->viewHelperNode = $node;
    }

    public function setRenderingContext(RenderingContextInterface $renderingContext): void
    {
        $this->renderingContext = $renderingContext;
    }

    /**
     * Receives defined component arguments
     *
     * @param array<string, mixed> $arguments
     */
    public function setArguments(array $arguments): void
    {
        $this->arguments = $arguments;
    }

    /**
     * Receives arguments for which no argument definition has been supplied. This is only relevant if
     * validateAdditionalArguments() didn't fail.
     *
     * @param array<string, mixed> $arguments
     */
    public function handleAdditionalArguments(array $arguments): void
    {
        $this->arguments = [...$this->arguments, ...$arguments];
    }

    /**
     * Checks if any additional arguments can be passed to the component
     *
     * @param array<string, mixed> $arguments
     */
    public function validateAdditionalArguments(array $arguments): void
    {
        if ($arguments === []) {
            return;
        }
        if (!$this->getComponentDefinitionProvider()->getComponentDefinition($this->viewHelperNode->getName())->additionalArgumentsAllowed()) {
            throw new Exception(sprintf(
                'Invalid arguments supplied to component <%s:%s>: %s',
                $this->viewHelperNode->getNamespace(),
                $this->viewHelperNode->getName(),
                implode(', ', array_keys($arguments)),
            ), 1748903732);
        }
    }

    /**
     * Obtains the argument definitions from the component's template and provides
     * them to the TemplateParser to apply correct escaping, to perform pre-validation
     * and to parse boolean expressions for boolean component arguments.
     *
     * @return ArgumentDefinition[]
     */
    public function prepareArguments(): array
    {
        return $this->getComponentDefinitionProvider()->getComponentDefinition($this->viewHelperNode->getName())->getArgumentDefinitions();
    }

    /**
     * Renders a component depending on the tag name of the original
     * ViewHelper in the template in uncached conditions. This method
     * is skipped entirely for cached templates.
     */
    public function initializeArgumentsAndRender(): mixed
    {
        return $this->getComponentDefinitionProvider()->getComponentRenderer()->renderComponent(
            $this->viewHelperNode->getName(),
            $this->arguments,
            $this->buildSlotClosures(),
            $this->renderingContext,
        );
    }

    /**
     * @return \Closure[]
     */
    private function buildSlotClosures(): array
    {
        if ($this->viewHelperNode->getChildNodes() === []) {
            return [];
        }
        $slotClosures = [];
        foreach ($this->extractFragmentViewHelperNodes($this->viewHelperNode) as $fragmentNode) {
            $fragmentName = $this->extractFragmentName($fragmentNode);
            if (isset($slotClosures[$fragmentName])) {
                throw new ParserException(sprintf(
                    'Fragment "%s" for <%s:%s> is defined multiple times.',
                    $fragmentName,
                    $this->viewHelperNode->getNamespace(),
                    $this->viewHelperNode->getName(),
                ), 1750865701);
            }
            $slotClosures[$fragmentName] = $this->buildSlotClosure($fragmentNode);
        }
        if ($slotClosures === []) {
            $slotClosures[SlotViewHelper::DEFAULT_SLOT] = $this->buildSlotClosure($this->viewHelperNode);
        }
        return $slotClosures;
    }

    private function buildSlotClosure(ViewHelperNode $viewHelperNode): \Closure
    {
        return function () use ($viewHelperNode): mixed {
            $this->renderingContextStack[] = $this->renderingContext;
            $result = $viewHelperNode->evaluateChildNodes($this->renderingContext);
            $this->setRenderingContext(array_pop($this->renderingContextStack));
            return $result;
        };
    }

    /**
     * Generates PHP code to be written into Fluid's cache files by the TemplateCompiler
     *
     * @return array{initialization: string, execution: string}
     */
    public function convert(TemplateCompiler $templateCompiler): array
    {
        $initializationPhpCode = '// Rendering Component ' . $this->viewHelperNode->getNamespace() . ':' . $this->viewHelperNode->getName() . chr(10);

        $argumentsVariableName = $templateCompiler->variableName('arguments');
        $slotClosuresVariableName = $templateCompiler->variableName('slotClosures');

        // Similarly to Fluid's ViewHelper processing, the responsible ViewHelper resolver delegate
        // is resolved, validated early and "baked in" to the cache to improve rendering times for
        // cached templates
        $resolverDelegate = $this->getComponentDefinitionProvider();
        $convertedViewHelperExecutionCode = sprintf(
            '$renderingContext->getViewHelperResolver()->createResolverDelegateInstanceFromClassName(%s)->getComponentRenderer()->renderComponent(%s, %s, %s, $renderingContext)',
            var_export($resolverDelegate->getNamespace(), true),
            var_export($this->viewHelperNode->getName(), true),
            $argumentsVariableName,
            $slotClosuresVariableName,
        );

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

        $slotClosures = [];
        if ($this->viewHelperNode->getChildNodes() !== []) {
            foreach ($this->extractFragmentViewHelperNodes($this->viewHelperNode) as $fragmentNode) {
                $fragmentName = $this->extractFragmentName($fragmentNode);
                $slotClosures[$fragmentName] = $templateCompiler->wrapChildNodesInClosure($fragmentNode);
            }
            if ($slotClosures === []) {
                $slotClosures[SlotViewHelper::DEFAULT_SLOT] = $templateCompiler->wrapChildNodesInClosure($this->viewHelperNode);
            }
        }

        // Build up closure which renders the child nodes
        foreach ($slotClosures as $name => $closureCode) {
            $slotClosures[$name] = var_export($name, true) . ' => ' . $closureCode;
        }
        $initializationPhpCode .= sprintf(
            '%s = [' . chr(10) . '%s' . chr(10) . '];' . chr(10),
            $slotClosuresVariableName,
            implode(',' . chr(10), $slotClosures),
        );

        $initializationPhpCode .= $accumulatedArgumentInitializationCode . chr(10) . $argumentInitializationCode;
        return [
            'initialization' => $initializationPhpCode,
            'execution' => $convertedViewHelperExecutionCode,
        ];
    }

    /**
     * Always escape children. The SlotViewHelper will take care during
     * output to prevent unwanted escaping.
     */
    public function isChildrenEscapingEnabled(): bool
    {
        return true;
    }

    /**
     * Never escape output of a component since the primary job of
     * components is to generate HTML which should be interpreted
     */
    public function isOutputEscapingEnabled(): bool
    {
        return false;
    }

    /**
     * Not relevant for component rendering
     *
     * @param NodeInterface[] $nodes
     * @todo remove with Fluid v5
     */
    public function setChildNodes(array $nodes): void {}

    /**
     * Not relevant for component rendering
     */
    public function getContentArgumentName(): ?string
    {
        return null;
    }

    /**
     * Not relevant for component rendering since ViewHelperInvoker
     * is never called from a cached template
     */
    public function setRenderChildrenClosure(\Closure $renderChildrenClosure): void {}

    /**
     * Helper method that ensures that the ViewHelper is called with a valid context for
     * component rendering.
     */
    private function getComponentDefinitionProvider(): ViewHelperResolverDelegateInterface&ComponentDefinitionProviderInterface
    {
        if (!$this->viewHelperNode->getResolverDelegate() instanceof ComponentDefinitionProviderInterface) {
            throw new Exception(sprintf(
                'Invalid ViewHelper resolver delegate provided to ComponentAdapter for <%s:%s>: %s',
                $this->viewHelperNode->getNamespace(),
                $this->viewHelperNode->getName(),
                get_class($this->viewHelperNode->getResolverDelegate()),
            ), 1748773601);
        }
        return $this->viewHelperNode->getResolverDelegate();
    }

    /**
     * @return ViewHelperNode[]
     */
    private function extractFragmentViewHelperNodes(ViewHelperNode $viewHelperNode): array
    {
        return array_filter(
            $viewHelperNode->getChildNodes(),
            fn(NodeInterface $node): bool => $node instanceof ViewHelperNode && $node->getUninitializedViewHelper() instanceof FragmentViewHelper,
        );
    }

    private function extractFragmentName(ViewHelperNode $fragmentNode): string
    {
        return isset($fragmentNode->getArguments()['name'])
            ? (string)$fragmentNode->getArguments()['name']->evaluate(new RenderingContext())
            : SlotViewHelper::DEFAULT_SLOT;
    }
}
