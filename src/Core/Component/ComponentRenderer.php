<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Component;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\ViewHelpers\SlotViewHelper;

final readonly class ComponentRenderer implements ComponentRendererInterface
{
    public function __construct(private ComponentTemplateResolverInterface $componentResolver) {}

    /**
     * Renders a Fluid template to be used as a component. The necessary view configuration (template paths,
     * template name and possible additional variables) are expected to be provided by the component template
     * resolver.
     *
     * @param array<string, mixed> $arguments
     * @param array<string, \Closure> $slots
     */
    public function renderComponent(string $viewHelperName, array $arguments, array $slots, RenderingContextInterface $parentRenderingContext): string
    {
        // Create new rendering context while retaining some global context (e. g. a possible request variable
        // or globally registered ViewHelper namespaces)
        $renderingContext = clone $parentRenderingContext;
        $renderingContext->getTemplateCompiler()->reset();
        $renderingContext->setTemplatePaths($this->componentResolver->getTemplatePaths());
        $renderingContext->setViewHelperResolver($renderingContext->getViewHelperResolver()->getScopedCopy());
        $renderingContext->setVariableProvider($renderingContext->getVariableProvider()->getScopeCopy($arguments));

        // Provide slots to SlotViewHelper
        $renderingContext->setViewHelperVariableContainer(new ViewHelperVariableContainer());
        $renderingContext->getViewHelperVariableContainer()->addAll(SlotViewHelper::class, $slots);

        // Create Fluid view for component
        // render() call includes validation of provided arguments
        $view = new TemplateView($renderingContext);
        $view->assignMultiple($this->componentResolver->getAdditionalVariables($viewHelperName));
        return (string)$view->render($this->componentResolver->resolveTemplateName($viewHelperName));
    }
}
