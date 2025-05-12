<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\ViewHelper;

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class ViewHelperInvoker
 *
 * Class which is responsible for calling the render methods
 * on ViewHelpers, and this alone.
 *
 * Can be replaced via the ViewHelperResolver if the system
 * that implements Fluid requires special handling of classes.
 * This includes for example when you want to validate arguments
 * differently, wish to use another ViewHelper initialization
 * process, or wish to store instances of ViewHelpers to reuse
 * as if they were Singletons.
 *
 * To override the instantiation process and class name resolving,
 * see ViewHelperResolver. This particular class should only be
 * responsible for invoking the render method of a ViewHelper
 * using the properties available in the node.
 */
class ViewHelperInvoker
{
    /**
     * Invoke the ViewHelper described by the ViewHelperNode, the properties
     * of which will already have been filled by the ViewHelperResolver.
     */
    public function invoke(string|ViewHelperInterface $viewHelperClassNameOrInstance, array $arguments, RenderingContextInterface $renderingContext, ?\Closure $renderChildrenClosure = null): mixed
    {
        $viewHelperResolver = $renderingContext->getViewHelperResolver();
        if ($viewHelperClassNameOrInstance instanceof ViewHelperInterface) {
            $viewHelper = $viewHelperClassNameOrInstance;
        } else {
            $viewHelper = $viewHelperResolver->createViewHelperInstanceFromClassName($viewHelperClassNameOrInstance);
        }
        $argumentDefinitions = $viewHelperResolver->getArgumentDefinitionsForViewHelper($viewHelper);

        // @todo make configurable with Fluid v5
        $argumentProcessor = new LenientArgumentProcessor();
        try {
            // Convert nodes to actual values (in uncached context)
            $arguments = array_map(
                fn($value) => $value instanceof NodeInterface ? $value->evaluate($renderingContext) : $value,
                $arguments,
            );

            // Determine arguments defined by the ViewHelper API
            $registeredArguments = [];
            foreach ($argumentDefinitions as $argumentName => $argumentDefinition) {
                // @todo also perform argument validation here with Fluid v5, including check for required arguments
                $registeredArguments[$argumentName] = isset($arguments[$argumentName])
                    ? $argumentProcessor->process($arguments[$argumentName], $argumentDefinition)
                    : $argumentDefinition->getDefaultValue();
                unset($arguments[$argumentName]);
            }

            if ($renderChildrenClosure) {
                $viewHelper->setRenderChildrenClosure($renderChildrenClosure);
            }
            $viewHelper->setRenderingContext($renderingContext);
            $viewHelper->setArguments($registeredArguments);
            $viewHelper->handleAdditionalArguments($arguments);
            return $viewHelper->initializeArgumentsAndRender();
        } catch (Exception $error) {
            return $renderingContext->getErrorHandler()->handleViewHelperError($error);
        }
    }
}
