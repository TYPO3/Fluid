<?php
namespace TYPO3\Fluid\Core\Rendering;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer;
use TYPO3\Fluid\Core\ViewHelper\ViewHelperVariableContainer;

/**
 * Contract for the rendering context
 */
interface RenderingContextInterface {

	/**
	 * Injects the template variable container containing all variables available through ObjectAccessors
	 * in the template
	 *
	 * @param TemplateVariableContainer $templateVariableContainer The template variable container to set
	 */
	public function injectTemplateVariableContainer(TemplateVariableContainer $templateVariableContainer);

	/**
	 * @param ViewHelperVariableContainer $viewHelperVariableContainer
	 */
	public function injectViewHelperVariableContainer(ViewHelperVariableContainer $viewHelperVariableContainer);

	/**
	 * Get the template variable container
	 *
	 * @return TemplateVariableContainer The Template Variable Container
	 */
	public function getTemplateVariableContainer();

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

	/**
	 * @return string
	 */
	public function getFormat();

	/**
	 * @param string $format
	 * @return void
	 */
	public function setFormat($format);
}
