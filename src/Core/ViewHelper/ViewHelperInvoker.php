<?php
namespace TYPO3\Fluid\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\Fluid\Core\ViewHelper\Exception;

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
	 * @param ViewHelperNode $node
	 * @param RenderingContextInterface $renderingContext
	 * @return mixed
	 */
	public function invoke(ViewHelperNode $node, RenderingContextInterface $renderingContext) {
		$viewHelperNamespace = $node->getViewHelperNamespace();
		$viewHelperName = $node->getViewHelperName();
		$arguments = $node->getArguments();
		$viewHelperClassName = $node->getViewHelperClassName();
		$expectedViewHelperArguments = $node->getArgumentDefinitions();
		$childNodes = $node->getChildNodes();

		// Rendering process
		$evaluatedArguments = array();
		foreach ($expectedViewHelperArguments as $argumentName => $argumentDefinition) {
			if (isset($arguments[$argumentName])) {
				/** @var NodeInterface|mixed $argumentValue */
				$argumentValue = $arguments[$argumentName];
				if ($argumentValue instanceof NodeInterface) {
					$evaluatedArguments[$argumentName] = $argumentValue->evaluate($renderingContext);
				} else {
					$evaluatedArguments[$argumentName] = $argumentValue;
				}
			} else {
				$evaluatedArguments[$argumentName] = $argumentDefinition->getDefaultValue();
			}
		}

		$this->abortIfUnregisteredArgumentsExist($expectedViewHelperArguments, $evaluatedArguments);
		$this->abortIfRequiredArgumentsAreMissing($expectedViewHelperArguments, $evaluatedArguments);

		/** @var ViewHelperInterface $viewHelper */
		$viewHelper = $this->viewHelperResolver->createViewHelperInstance($viewHelperNamespace, $viewHelperName);
		$viewHelper->resetState();
		$viewHelper->setArguments($evaluatedArguments);
		$viewHelper->setViewHelperNode($node);
		$viewHelper->setChildNodes($childNodes);
		$viewHelper->setRenderingContext($renderingContext);

		return $viewHelper->initializeArgumentsAndRender();
	}

	/**
	 * Throw an exception if there are arguments which were not registered
	 * before.
	 *
	 * @param array $expectedArguments Array of \TYPO3\Fluid\Core\ViewHelper\ArgumentDefinition of all expected arguments
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
