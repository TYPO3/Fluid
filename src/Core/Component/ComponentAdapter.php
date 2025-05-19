<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Component;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;
use TYPO3Fluid\Fluid\ViewHelpers\SlotViewHelper;

/**
 * The ComponentAdapter is a special ViewHelper that is responsible for adapting the logic of
 * components (= rendering a separate Fluid TemplateView) to Fluid's ViewHelper logic. In contrast
 * to other ViewHelpers, this implementation can be responsible for multiple tags within one
 * defined namespace by interpreting the ViewHelperNode name from within the ViewHelper code.
 * This ViewHelper is only called from uncached templates, the ComponentInvoker is responsible
 * for the actual rendering of a component.
 *
 * @internal
 */
final class ComponentAdapter implements ViewHelperInterface
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
     * This will usually only reset the arguments since this ViewHelper
     * doesn't declare any arguments. The component arguments are merged
     * in handleAdditionalArguments().
     *
     * @param array<string, mixed> $arguments
     */
    public function setArguments(array $arguments): void
    {
        $this->arguments = $arguments;
    }

    /**
     * Component arguments are passed to the ViewHelper using this method
     * since the ViewHelper itself doesn't declare any additional arguments.
     *
     * @param array<string, mixed> $arguments
     */
    public function handleAdditionalArguments(array $arguments): void
    {
        $this->arguments = [...$this->arguments, ...$arguments];
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
        if (!isset($this->viewHelperNode)) {
            throw new Exception('The ComponentAdapter can only be used in uncached templates.', 1748773811);
        }
        if (!$this->viewHelperNode->getResolverDelegate() instanceof ComponentResolverInterface) {
            throw new Exception(sprintf(
                'Invalid ViewHelper resolver delegate provided to ComponentAdapter for <%s:%s>: %s',
                $this->viewHelperNode->getNamespace(),
                $this->viewHelperNode->getName(),
                get_class($this->viewHelperNode->getResolverDelegate()),
            ), 1748773812);
        }

        $resolverDelegate = $this->viewHelperNode->getResolverDelegate();
        return $resolverDelegate->getArgumentDefinitions($this->viewHelperNode->getName(), $this->renderingContext->getViewHelperResolver());
    }

    /**
     * Renders a component depending on the tag name of the original
     * ViewHelper in the template in uncached conditions. This method
     * is skipped entirely for cached templates.
     */
    public function initializeArgumentsAndRender(): mixed
    {
        if (!isset($this->viewHelperNode)) {
            throw new Exception('The ComponentAdapter can only be used in uncached templates.', 1748773493);
        }
        if (!$this->viewHelperNode->getResolverDelegate() instanceof ComponentResolverInterface) {
            throw new Exception(sprintf(
                'Invalid ViewHelper resolver delegate provided to ComponentAdapter for <%s:%s>: %s',
                $this->viewHelperNode->getNamespace(),
                $this->viewHelperNode->getName(),
                get_class($this->viewHelperNode->getResolverDelegate()),
            ), 1748773601);
        }

        $resolverDelegate = $this->viewHelperNode->getResolverDelegate();
        return (new ComponentInvoker())->invoke(
            $resolverDelegate,
            $resolverDelegate->resolveTemplateName($this->viewHelperNode->getName()),
            $this->arguments,
            $this->renderingContext,
            [
                SlotViewHelper::DEFAULT_SLOT => $this->buildRenderChildrenClosure(),
            ],
        );
    }

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
        if (!$this->viewHelperNode->getResolverDelegate() instanceof ComponentResolverInterface) {
            throw new Exception(sprintf(
                'Invalid ViewHelper resolver delegate provided to ComponentAdapter for <%s:%s>: %s',
                $this->viewHelperNode->getNamespace(),
                $this->viewHelperNode->getName(),
                get_class($this->viewHelperNode->getResolverDelegate()),
            ), 1748773602);
        }

        $initializationPhpCode = '// Rendering Component ' . $this->viewHelperNode->getNamespace() . ':' . $this->viewHelperNode->getName() . chr(10);

        $argumentsVariableName = $templateCompiler->variableName('arguments');
        $renderChildrenClosureVariableName = $templateCompiler->variableName('renderChildrenClosure');

        // Similarly to Fluid's ViewHelper processing, the responsible ViewHelper resolver delegate and
        // the component's template are resolved early and "baked in" to the cache to improve rendering
        // times for cached templates
        $resolverDelegate = $this->viewHelperNode->getResolverDelegate();
        $convertedViewHelperExecutionCode = sprintf(
            '(new %s)->invoke(%s, %s, %s, $renderingContext, %s)',
            ComponentInvoker::class,
            var_export($resolverDelegate->getNamespace(), true),
            var_export($resolverDelegate->resolveTemplateName($this->viewHelperNode->getName()), true),
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
     * Not relevant for component rendering since argument validation
     * happens on the template level
     *
     * @param array<string, mixed> $arguments
     */
    public function validateAdditionalArguments(array $arguments): void {}

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
}
