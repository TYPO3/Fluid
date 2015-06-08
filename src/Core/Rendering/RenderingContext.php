<?php
namespace NamelessCoder\Fluid\Core\Rendering;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\Core\Variables\StandardVariableProvider;
use NamelessCoder\Fluid\Core\Variables\VariableProviderInterface;
use NamelessCoder\Fluid\Core\ViewHelper\ViewHelperResolver;
use NamelessCoder\Fluid\Core\ViewHelper\ViewHelperVariableContainer;

/**
 * The rendering context that contains useful information during rendering time of a Fluid template
 */
class RenderingContext implements RenderingContextInterface {

	/**
	 * Template Variable Container. Contains all variables available through object accessors in the template
	 *
	 * @var VariableProviderInterface
	 */
	protected $variableProvider;

	/**
	 * ViewHelper Variable Container
	 *
	 * @var ViewHelperVariableContainer
	 */
	protected $viewHelperVariableContainer;

	/**
	 * @var ViewHelperResolver
	 */
	protected $viewHelperResolver;

	/**
	 * @var string
	 */
	protected $controllerName;

	/**
	 * @var string
	 */
	protected $controllerAction;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->variableProvider = new StandardVariableProvider();
		$this->viewHelperVariableContainer = new ViewHelperVariableContainer();
		$this->viewHelperResolver = new ViewHelperResolver();
	}

	/**
	 * Injects the template variable container containing all variables available through ObjectAccessors
	 * in the template
	 *
	 * @param VariableProviderInterface $variableProvider The template variable container to set
	 */
	public function setVariableProvider(VariableProviderInterface $variableProvider) {
		$this->variableProvider = $variableProvider;
	}

	/**
	 * Get the template variable container
	 *
	 * @return VariableProviderInterface The Template Variable Container
	 */
	public function getVariableProvider() {
		return $this->variableProvider;
	}

	/**
	 * @return ViewHelperResolver
	 */
	public function getViewHelperResolver() {
		return $this->viewHelperResolver;
	}

	/**
	 * @param ViewHelperResolver $viewHelperResolver
	 * @return void
	 */
	public function setViewHelperResolver(ViewHelperResolver $viewHelperResolver) {
		$this->viewHelperResolver = $viewHelperResolver;
	}

	/**
	 * Set the ViewHelperVariableContainer
	 *
	 * @param ViewHelperVariableContainer $viewHelperVariableContainer
	 * @return void
	 */
	public function injectViewHelperVariableContainer(ViewHelperVariableContainer $viewHelperVariableContainer) {
		$this->viewHelperVariableContainer = $viewHelperVariableContainer;
	}

	/**
	 * Get the ViewHelperVariableContainer
	 *
	 * @return ViewHelperVariableContainer
	 */
	public function getViewHelperVariableContainer() {
		return $this->viewHelperVariableContainer;
	}

	/**
	 * @return string
	 */
	public function getControllerName() {
		return $this->controllerName;
	}

	/**
	 * @param string $controllerName
	 * @return void
	 */
	public function setControllerName($controllerName) {
		$this->controllerName = $controllerName;
	}

	/**
	 * @return string
	 */
	public function getControllerAction() {
		return $this->controllerAction;
	}

	/**
	 * @param string $action
	 * @return void
	 */
	public function setControllerAction($action) {
		$this->controllerAction = $action;
	}

}
