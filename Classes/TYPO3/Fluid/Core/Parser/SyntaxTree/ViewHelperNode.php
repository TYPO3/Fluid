<?php
namespace TYPO3\Fluid\Core\Parser\SyntaxTree;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Object\DependencyInjection\DependencyProxy;
use TYPO3\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3\Fluid\Core\ViewHelper\Facets\ChildNodeAccessInterface;

/**
 * Node which will call a ViewHelper associated with this node.
 */
class ViewHelperNode extends AbstractNode {

	/**
	 * Class name of view helper
	 *
	 * @var string
	 */
	protected $viewHelperClassName;

	/**
	 * Arguments of view helper - References to RootNodes.
	 *
	 * @var array<NodeInterface>
	 */
	protected $arguments = array();

	/**
	 * The ViewHelper associated with this node
	 *
	 * @var AbstractViewHelper
	 */
	protected $uninitializedViewHelper = NULL;

	/**
	 * A mapping RenderingContext -> ViewHelper to only re-initialize ViewHelpers
	 * when a context change occurs.
	 *
	 * @var \SplObjectStorage
	 */
	protected $viewHelpersByContext = NULL;

	/**
	 * Constructor.
	 *
	 * @param AbstractViewHelper $viewHelper The view helper
	 * @param array $arguments<NodeInterface> Arguments of view helper - each value is a RootNode.
	 */
	public function __construct(AbstractViewHelper $viewHelper, array $arguments) {
		$this->uninitializedViewHelper = $viewHelper;
		$this->viewHelpersByContext = new \SplObjectStorage();
		$this->arguments = $arguments;
		$this->viewHelperClassName = ($this->uninitializedViewHelper instanceof DependencyProxy) ? $this->uninitializedViewHelper->_getClassName() : get_class($this->uninitializedViewHelper);
	}

	/**
	 * Returns the attached (but still uninitialized) ViewHelper for this ViewHelperNode.
	 * We need this method because sometimes Interceptors need to ask some information from the ViewHelper.
	 *
	 * @return AbstractViewHelper the attached ViewHelper, if it is initialized
	 */
	public function getUninitializedViewHelper() {
		return $this->uninitializedViewHelper;
	}

	/**
	 * Get class name of view helper
	 *
	 * @return string Class Name of associated view helper
	 */
	public function getViewHelperClassName() {
		return $this->viewHelperClassName;
	}

	/**
	 * INTERNAL - only needed for compiling templates
	 *
	 * @return array
	 * @Flow\Internal
	 */
	public function getArguments() {
		return $this->arguments;
	}

	/**
	 * Call the view helper associated with this object.
	 *
	 * First, it evaluates the arguments of the view helper.
	 *
	 * If the view helper implements \TYPO3\Fluid\Core\ViewHelper\Facets\ChildNodeAccessInterface,
	 * it calls setChildNodes(array childNodes) on the view helper.
	 *
	 * Afterwards, checks that the view helper did not leave a variable lying around.
	 *
	 * @param RenderingContextInterface $renderingContext
	 * @return object evaluated node after the view helper has been called.
	 */
	public function evaluate(RenderingContextInterface $renderingContext) {
		if ($this->viewHelpersByContext->contains($renderingContext)) {
			$viewHelper = $this->viewHelpersByContext->offsetGet($renderingContext);
			$viewHelper->resetState();
		} else {
			$viewHelper = clone $this->uninitializedViewHelper;
			$this->viewHelpersByContext->attach($renderingContext, $viewHelper);
		}

		$evaluatedArguments = array();
		if (count($viewHelper->prepareArguments())) {
			/** @var $argumentDefinition ArgumentDefinition */
			foreach ($viewHelper->prepareArguments() as $argumentName => $argumentDefinition) {
				if (isset($this->arguments[$argumentName])) {
					/** @var $argumentValue NodeInterface */
					$argumentValue = $this->arguments[$argumentName];
					$evaluatedArguments[$argumentName] = $argumentValue->evaluate($renderingContext);
				} else {
					$evaluatedArguments[$argumentName] = $argumentDefinition->getDefaultValue();
				}
			}
		}

		$viewHelper->setArguments($evaluatedArguments);
		$viewHelper->setViewHelperNode($this);
		$viewHelper->setRenderingContext($renderingContext);

		if ($viewHelper instanceof ChildNodeAccessInterface) {
			$viewHelper->setChildNodes($this->childNodes);
		}

		$output = $viewHelper->initializeArgumentsAndRender();

		return $output;
	}

	/**
	 * Clean up for serializing.
	 *
	 * @return array
	 */
	public function __sleep() {
		return array('viewHelperClassName', 'arguments', 'childNodes');
	}
}
