<?php
namespace TYPO3Fluid\Fluid\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

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
class ViewHelperInvoker {

	/**
	 * Invoke the ViewHelper described by the ViewHelperNode, the properties
	 * of which will already have been filled by the ViewHelperResolver.
	 *
	 * @param string|ViewHelperInterface $viewHelperClassName
	 * @param array $arguments
	 * @param RenderingContextInterface $renderingContext
	 * @param \Closure $renderChildrenClosure
	 * @return mixed
	 */
	public function invoke($viewHelperClassNameOrInstance, array $arguments, RenderingContextInterface $renderingContext, \Closure $renderChildrenClosure = NULL) {
		$viewHelperResolver = $renderingContext->getViewHelperResolver();
		if ($viewHelperClassNameOrInstance instanceof ViewHelperInterface) {
			$viewHelper = $viewHelperClassNameOrInstance;
		} else {
			$viewHelper = $viewHelperResolver->createViewHelperInstanceFromClassName($viewHelperClassNameOrInstance);
		}
		$expectedViewHelperArguments = $viewHelperResolver->getArgumentDefinitionsForViewHelper($viewHelper);

		// Rendering process
		$evaluatedArguments = array();
		$undeclaredArguments = array();
		foreach ($expectedViewHelperArguments as $argumentName => $argumentDefinition) {
			if (isset($arguments[$argumentName])) {
				/** @var NodeInterface|mixed $argumentValue */
				$argumentValue = $arguments[$argumentName];
				$evaluatedArguments[$argumentName] = $argumentValue instanceof NodeInterface ? $argumentValue->evaluate($renderingContext) : $argumentValue;
			} else {
				$evaluatedArguments[$argumentName] = $argumentDefinition->getDefaultValue();
			}
		}
		foreach ($arguments as $argumentName => $argumentValue) {
			if (!array_key_exists($argumentName, $evaluatedArguments)) {
				$undeclaredArguments[$argumentName] = $argumentValue instanceof NodeInterface ? $argumentValue->evaluate($renderingContext) : $argumentValue;
			}
		}

		$this->abortIfRequiredArgumentsAreMissing($expectedViewHelperArguments, $evaluatedArguments);

		$viewHelper->setRenderingContext($renderingContext);
		$viewHelper->setArguments($evaluatedArguments);
		$viewHelper->handleAdditionalArguments($undeclaredArguments);
		if ($renderChildrenClosure) {
			$viewHelper->setRenderChildrenClosure($renderChildrenClosure);
		}
		return $viewHelper->initializeArgumentsAndRender();
	}

	/**
	 * Throw an exception if required arguments are missing
	 *
	 * @param ArgumentDefinition[] $expectedArguments Array of all expected arguments
	 * @param NodeInterface[] $actualArguments Actual arguments
	 * @throws Exception
	 */
	protected function abortIfRequiredArgumentsAreMissing($expectedArguments, $actualArguments) {
		$actualArgumentNames = array_keys($actualArguments);
		foreach ($expectedArguments as $expectedArgument) {
			if ($expectedArgument->isRequired() && !in_array($expectedArgument->getName(), $actualArgumentNames)) {
				throw new Exception('Required argument "' . $expectedArgument->getName() . '" was not supplied.', 1237823699);
			}
		}
	}

}
