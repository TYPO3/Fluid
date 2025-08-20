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

        try {
            // Convert nodes to actual values (in uncached context)
            $arguments = array_map(
                fn($value) => $value instanceof NodeInterface ? $value->evaluate($renderingContext) : $value,
                $arguments,
            );

            // Determine arguments defined by the ViewHelper API
            $registeredArguments = [];
            foreach ($argumentDefinitions as $argumentName => $argumentDefinition) {
                if (isset($arguments[$argumentName])) {
                    // Perform argument processing and validation
                    $value = $renderingContext->getArgumentProcessor()->process($arguments[$argumentName], $argumentDefinition);
                    if (!$renderingContext->getArgumentProcessor()->isValid($value, $argumentDefinition)) {
                        $givenType = is_object($value) ? get_class($value) : gettype($value);
                        throw new \InvalidArgumentException(sprintf(
                            'The argument "%s" was registered with type "%s", but is of type "%s" in view helper "%s".',
                            $argumentName,
                            $argumentDefinition->getType(),
                            $givenType,
                            get_class($viewHelper),
                        ), 1256475113);
                    }
                    $registeredArguments[$argumentName] = $value;
                } else {
                    // @todo we might add a check for isRequired() here. Currently, this relies on the check
                    //       being performed by the TemplateParser
                    $registeredArguments[$argumentName] = $argumentDefinition->getDefaultValue();
                }

                // Argument has definition, so it is no additionalArgument
                unset($arguments[$argumentName]);
            }

            if ($renderChildrenClosure) {
                $viewHelper->setRenderChildrenClosure($renderChildrenClosure);
            }
            $viewHelper->setRenderingContext($renderingContext);
            $viewHelper->setArguments($registeredArguments);
            $viewHelper->handleAdditionalArguments($arguments);
            if ($viewHelper instanceof ViewHelperArgumentsValidatedEventInterface) {
                $viewHelper::argumentsValidatedEvent(
                    $registeredArguments,
                    $argumentDefinitions,
                    $viewHelper,
                );
            }
            return $viewHelper->initializeArgumentsAndRender();
        } catch (Exception $error) {
            return $renderingContext->getErrorHandler()->handleViewHelperError($error);
        }
    }
}
