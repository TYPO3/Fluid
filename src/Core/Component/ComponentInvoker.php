<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Component;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolverDelegateInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\View\TemplatePaths;
use TYPO3Fluid\Fluid\ViewHelpers\SlotViewHelper;

class ComponentInvoker
{
    public function invoke(ViewHelperResolverDelegateInterface|string $resolverDelegate, string $path, array $arguments, RenderingContextInterface $parentRenderingContext, array $slots = []): mixed
    {
        if (!$resolverDelegate instanceof ViewHelperResolverDelegateInterface) {
            $resolverDelegate = $parentRenderingContext->getViewHelperResolver()->createResolverDelegateInstanceFromClassName($resolverDelegate);
        }

        if (!$resolverDelegate instanceof ComponentViewFactoryInterface) {
            throw new \Exception('Invalid component collection');
        }

        // @todo provide better API to clone rendering context (within template context vs. outside template context).
        //       currently, objects need to be reset manually to avoid side effects while retaining desired state
        //       (e. g. a request object)
        $renderingContext = clone $parentRenderingContext;
        $renderingContext->setTemplateParser(new TemplateParser());
        $renderingContext->setTemplateCompiler(new TemplateCompiler());
        $renderingContext->setTemplatePaths(new TemplatePaths());
        $renderingContext->setViewHelperResolver($renderingContext->getViewHelperResolver()->getScopedCopy());
        $renderingContext->setVariableProvider($renderingContext->getVariableProvider()->getScopeCopy($arguments));
        $renderingContext->setViewHelperVariableContainer(new ViewHelperVariableContainer());
        $renderingContext->getViewHelperVariableContainer()->addAll(SlotViewHelper::class, $slots);
        return $resolverDelegate->createView($renderingContext)->render($path);
    }
}
