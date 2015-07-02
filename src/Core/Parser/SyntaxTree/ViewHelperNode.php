<?php
namespace NamelessCoder\Fluid\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\Core\Parser\ParsingState;
use NamelessCoder\Fluid\Core\Rendering\RenderingContextInterface;
use NamelessCoder\Fluid\Core\ViewHelper\ArgumentDefinition;
use NamelessCoder\Fluid\Core\ViewHelper\ViewHelperInterface;
use NamelessCoder\Fluid\Core\ViewHelper\ViewHelperResolver;

/**
 * Node which will call a ViewHelper associated with this node.
 */
class ViewHelperNode extends AbstractNode {

	/**
	 * @var string
	 */
	protected $viewHelperClassName;

	/**
	 * @var NodeInterface[]
	 */
	protected $arguments = array();

	/**
	 * @var ViewHelperInterface
	 */
	protected $uninitializedViewHelper = NULL;

	/**
	 * @var ArgumentDefinition[]
	 */
	protected $argumentDefinitions = array();

	/**
	 * @var ViewHelperResolver
	 */
	protected $viewHelperResolver;

	/**
	 * Constructor.
	 *
	 * @param ViewHelperResolver an instance or subclass of ViewHelperResolver
	 * @param string $namespace the namespace identifier of the ViewHelper.
	 * @param string $identifier the name of the ViewHelper to render, inside the namespace provided.
	 * @param NodeInterface[] $arguments Arguments of view helper - each value is a RootNode.
	 * @param ParsingState $state
	 */
	public function __construct(ViewHelperResolver $resolver, $namespace, $identifier, array $arguments, ParsingState $state) {
		$this->viewHelperResolver = $resolver;
		$this->viewHelperClassName = $resolver->resolveViewHelperClassName($namespace, $identifier);
		$this->uninitializedViewHelper = $resolver->createViewHelperInstance($namespace, $identifier);
		$this->arguments = $arguments;
		$this->argumentDefinitions = $resolver->getArgumentDefinitionsForViewHelper($this->uninitializedViewHelper);
		$this->rewriteBooleanNodesInArgumentsObjectTree($this->argumentDefinitions, $this->arguments);
	}

	/**
	 * @return ArgumentDefinition[]
	 */
	public function getArgumentDefinitions() {
		return $this->uninitializedViewHelper->prepareArguments();
	}

	/**
	 * Returns the attached (but still uninitialized) ViewHelper for this ViewHelperNode.
	 * We need this method because sometimes Interceptors need to ask some information from the ViewHelper.
	 *
	 * @return ViewHelperInterface
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
	 * @return NodeInterface[]
	 */
	public function getArguments() {
		return $this->arguments;
	}

	/**
	 * INTERNAL - only needed for compiling templates
	 *
	 * @param string $argumentName
	 * @return ArgumentDefinition|NULL
	 */
	public function getArgumentDefinition($argumentName) {
		return $this->argumentDefinitions[$argumentName];
	}

	/**
	 * Call the view helper associated with this object.
	 *
	 * First, it evaluates the arguments of the view helper.
	 *
	 * If the view helper implements \NamelessCoder\Fluid\Core\ViewHelper\ChildNodeAccessInterface,
	 * it calls setChildNodes(array childNodes) on the view helper.
	 *
	 * Afterwards, checks that the view helper did not leave a variable lying around.
	 *
	 * @param RenderingContextInterface $renderingContext
	 * @return object evaluated node after the view helper has been called.
	 */
	public function evaluate(RenderingContextInterface $renderingContext) {
		$viewHelper = clone $this->getUninitializedViewHelper();
		// Note about the following three method calls: some ViewHelpers
		// require a specific order of attribute setting. The logical
		// order is to first provide a ViewHelperNode, second to provide
		// the rendering context and finally to provide child nodes.
		// DO NOT CHANGE THIS ORDER. You *will* cause damage.
		$viewHelper->setViewHelperNode($this);
		$viewHelper->setRenderingContext($renderingContext);
		$viewHelper->setChildNodes($this->getChildNodes());
		return $renderingContext->getViewHelperResolver()->resolveViewHelperInvoker($this->getViewHelperClassName())
			->invoke($viewHelper, $this->getArguments(), $renderingContext);
	}

	/**
	 * Wraps the argument tree, if a node is boolean, into a Boolean syntax tree node
	 *
	 * @param array $argumentDefinitions the argument definitions, key is the argument name, value is the ArgumentDefinition object
	 * @param array $argumentsObjectTree the arguments syntax tree, key is the argument name, value is an AbstractNode
	 * @return void
	 */
	protected function rewriteBooleanNodesInArgumentsObjectTree($argumentDefinitions, &$argumentsObjectTree) {
		/** @var $argumentDefinition ArgumentDefinition */
		foreach ($argumentDefinitions as $argumentName => $argumentDefinition) {
			if ($argumentDefinition->getType() === 'boolean' && isset($argumentsObjectTree[$argumentName])) {
				$argumentsObjectTree[$argumentName] = new BooleanNode($argumentsObjectTree[$argumentName]);
			}
		}
	}

}
