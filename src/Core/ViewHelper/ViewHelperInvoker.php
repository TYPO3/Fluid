<?php
namespace TYPO3Fluid\Fluid\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
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
	 * @var ViewHelperResolver
	 */
	protected $viewHelperResolver;

	/**
	 * @param ViewHelperResolver $viewHelperResolver
	 */
	public function __construct(ViewHelperResolver $viewHelperResolver) {
		$this->viewHelperResolver = $viewHelperResolver;
	}

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
		if ($viewHelperClassNameOrInstance instanceof ViewHelperInterface) {
			$viewHelper = $viewHelperClassNameOrInstance;
		} else {
			$viewHelper = $this->viewHelperResolver->createViewHelperInstanceFromClassName($viewHelperClassNameOrInstance);
		}
		$expectedViewHelperArguments = $renderingContext->getViewHelperResolver()->getArgumentDefinitionsForViewHelper($viewHelper);

		// Rendering process
		$evaluatedArguments = array();
		foreach ($expectedViewHelperArguments as $argumentName => $argumentDefinition) {
			if (isset($arguments[$argumentName])) {
				/** @var NodeInterface|mixed $argumentValue */
				$argumentValue = $arguments[$argumentName];
				$evaluatedArguments[$argumentName] = $argumentValue instanceof NodeInterface ? $argumentValue->evaluate($renderingContext) : $argumentValue;
			} else {
				$evaluatedArguments[$argumentName] = $argumentDefinition->getDefaultValue();
			}
		}

		$this->abortIfUnregisteredArgumentsExist($expectedViewHelperArguments, $evaluatedArguments);
		$this->abortIfRequiredArgumentsAreMissing($expectedViewHelperArguments, $evaluatedArguments);

		$viewHelper->setArguments($evaluatedArguments);
		$viewHelper->setRenderingContext($renderingContext);
		if ($renderChildrenClosure) {
			$viewHelper->setRenderChildrenClosure($renderChildrenClosure);
		}
		return $viewHelper->initializeArgumentsAndRender();
	}

	/**
	 * Throw an exception if there are arguments which were not registered
	 * before.
	 *
	 * @param array $expectedArguments Array of \TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition of all expected arguments
	 * @param array $actualArguments Actual arguments
	 * @throws Exception
	 */
	protected function abortIfUnregisteredArgumentsExist($expectedArguments, $actualArguments) {
		$expectedArgumentNames = array();
		/** @var ArgumentDefinition $expectedArgument */
		foreach ($expectedArguments as $expectedArgument) {
			$expectedArgumentNames[] = $expectedArgument->getName();
		}

		foreach (array_keys($actualArguments) as $argumentName) {
			if (!in_array($argumentName, $expectedArgumentNames)) {
				throw new Exception('Argument "' . $argumentName . '" was not registered.', 1237823695);
			}
		}
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
