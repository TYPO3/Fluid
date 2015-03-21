<?php
namespace TYPO3\Fluid\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\Parser\ParsingState;
use TYPO3\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3\Fluid\Core\ViewHelper\ViewHelperInterface;
use TYPO3\Fluid\Core\ViewHelper\ViewHelperResolver;

/**
 * Node which will call a ViewHelper associated with this node.
 */
class ViewHelperNode extends AbstractNode {

	/**
	 * @var string
	 */
	protected $viewHelperNamespace;

	/**
	 * @var string
	 */
	protected $viewHelperName;

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
		$this->viewHelperNamespace = $namespace;
		$this->viewHelperName = $identifier;
		$this->viewHelperClassName = $resolver->resolveViewHelperClassName($namespace, $identifier);
		$this->uninitializedViewHelper = $resolver->createViewHelperInstance($namespace, $identifier);
		$this->argumentDefinitions = $resolver->getArgumentDefinitionsForViewHelper($this->uninitializedViewHelper);
		$this->arguments = $arguments;
		$this->rewriteBooleanNodesInArgumentsObjectTree($this->argumentDefinitions, $this->arguments, $state);
	}

	/**
	 * @return string
	 */
	public function getViewHelperNamespace() {
		return $this->viewHelperNamespace;
	}

	/**
	 * @return string
	 */
	public function getViewHelperName() {
		return $this->viewHelperName;
	}

	/**
	 * @return ArgumentDefinition[]
	 */
	public function getArgumentDefinitions() {
		return $this->argumentDefinitions;
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
	 * Call the view helper associated with this object.
	 *
	 * First, it evaluates the arguments of the view helper.
	 *
	 * If the view helper implements \TYPO3\Fluid\Core\ViewHelper\ChildNodeAccessInterface,
	 * it calls setChildNodes(array childNodes) on the view helper.
	 *
	 * Afterwards, checks that the view helper did not leave a variable lying around.
	 *
	 * @param RenderingContextInterface $renderingContext
	 * @return object evaluated node after the view helper has been called.
	 */
	public function evaluate(RenderingContextInterface $renderingContext) {
		$invoker = $this->viewHelperResolver->resolveViewHelperInvoker($this->viewHelperClassName);
		return $invoker->invoke($this, $renderingContext);
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

	/**
	 * Clean up for serializing.
	 *
	 * @return array
	 */
	public function __sleep() {
		return array(
			'viewHelperClassName',
			'viewHelperNamespace',
			'viewHelperName',
			'argumentDefinitions',
			'viewHelperResolver',
			'arguments',
			'childNodes'
		);
	}
}
