<?php
namespace TYPO3\Fluid\Core\Rendering;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3\Fluid\Core\ViewHelper\ViewHelperVariableContainer;

/**
 * Contract for the rendering context
 */
interface RenderingContextInterface {

	/**
	 * Injects the template variable container containing all variables available through ObjectAccessors
	 * in the template
	 *
	 * @param VariableProviderInterface $variableProvider The template variable container to set
	 */
	public function setVariableProvider(VariableProviderInterface $variableProvider);

	/**
	 * @param ViewHelperVariableContainer $viewHelperVariableContainer
	 */
	public function injectViewHelperVariableContainer(ViewHelperVariableContainer $viewHelperVariableContainer);

	/**
	 * Get the template variable container
	 *
	 * @return VariableProviderInterface The Template Variable Container
	 */
	public function getVariableProvider();

	/**
	 * Get the ViewHelperVariableContainer
	 *
	 * @return ViewHelperVariableContainer
	 */
	public function getViewHelperVariableContainer();

	/**
	 * @return string
	 */
	public function getControllerName();

	/**
	 * @param string $controllerName
	 * @return void
	 */
	public function setControllerName($controllerName);

	/**
	 * @return string
	 */
	public function getControllerAction();

	/**
	 * @param string $action
	 * @return void
	 */
	public function setControllerAction($action);

}
