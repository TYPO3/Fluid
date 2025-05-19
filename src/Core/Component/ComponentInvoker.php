<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Component;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolverDelegateInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\ViewHelpers\SlotViewHelper;

final readonly class ComponentInvoker
{
    public function invoke(ViewHelperResolverDelegateInterface|string $resolverDelegate, string $path, array $arguments, RenderingContextInterface $parentRenderingContext, array $slots = []): string
    {
        if (!$resolverDelegate instanceof ViewHelperResolverDelegateInterface) {
            $resolverDelegate = $parentRenderingContext->getViewHelperResolver()->createResolverDelegateInstanceFromClassName($resolverDelegate);
        }
        if (!$resolverDelegate instanceof ComponentResolverInterface) {
            throw new Exception('ComponentInvoker can only handle resolver delegates that implement ComponentResolverInterface.', 1748512595);
        }

        // Create new rendering context while retaining some global context (e. g. a possible request variable
        // or globally registered ViewHelper namespaces)
        $renderingContext = clone $parentRenderingContext;
        $renderingContext->getTemplateCompiler()->reset();
        $renderingContext->setTemplatePaths($resolverDelegate->getTemplatePaths());
        $renderingContext->setViewHelperResolver($renderingContext->getViewHelperResolver()->getScopedCopy());
        $renderingContext->setVariableProvider($renderingContext->getVariableProvider()->getScopeCopy($arguments));

        // Provide slots to SlotViewHelper
        $renderingContext->setViewHelperVariableContainer(new ViewHelperVariableContainer());
        $renderingContext->getViewHelperVariableContainer()->addAll(SlotViewHelper::class, $slots);

        // Create Fluid view for component
        // render() call includes validation of provided arguments
        $view = new TemplateView($renderingContext);
        $view->assignMultiple($resolverDelegate->getAdditionalVariables());
        return (string)$view->render($path);
    }
}
