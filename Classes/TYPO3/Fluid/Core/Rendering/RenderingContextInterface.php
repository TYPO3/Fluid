<?php
namespace TYPO3\Fluid\Core\Rendering;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Mvc\Controller\ControllerContext;
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
	 * Get the template variable container
	 *
	 * @return TemplateVariableContainer The Template Variable Container
	 */
	public function getTemplateVariableContainer();

	/**
	 * Set the controller context which will be passed to the ViewHelper
	 *
	 * @param ControllerContext $controllerContext The controller context to set
	 */
	public function setControllerContext(ControllerContext $controllerContext);

	/**
	 * Get the controller context which will be passed to the ViewHelper
	 *
	 * @return ControllerContext The controller context to set
	 */
	public function getControllerContext();

	/**
	 * Get the ViewHelperVariableContainer
	 *
	 * @return ViewHelperVariableContainer
	 */
	public function getViewHelperVariableContainer();
}
