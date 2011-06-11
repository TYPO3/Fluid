<?php
namespace F3\Fluid\Core\Rendering;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 *
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
interface RenderingContextInterface {

	/**
	 * Returns the object manager. Only the ViewHelperNode should do this.
	 *
	 * @param \F3\FLOW3\Object\ObjectManagerInterface $objectManager
	 */
	public function getObjectManager();

	/**
	 * Injects the template variable container containing all variables available through ObjectAccessors
	 * in the template
	 *
	 * @param \F3\Fluid\Core\ViewHelper\TemplateVariableContainer $templateVariableContainer The template variable container to set
	 */
	public function injectTemplateVariableContainer(\F3\Fluid\Core\ViewHelper\TemplateVariableContainer $templateVariableContainer);

	/**
	 * Get the template variable container
	 *
	 * @return \F3\Fluid\Core\ViewHelper\TemplateVariableContainer The Template Variable Container
	 */
	public function getTemplateVariableContainer();

	/**
	 * Set the controller context which will be passed to the ViewHelper
	 *
	 * @param \F3\FLOW3\MVC\Controller\ControllerContext $controllerContext The controller context to set
	 */
	public function setControllerContext(\F3\FLOW3\MVC\Controller\ControllerContext $controllerContext);

	/**
	 * Get the controller context which will be passed to the ViewHelper
	 *
	 * @return \F3\FLOW3\MVC\Controller\ControllerContext The controller context to set
	 */
	public function getControllerContext();

	/**
	 * Set the ViewHelperVariableContainer
	 *
	 * @param \F3\Fluid\Core\ViewHelper\ViewHelperVariableContainer $viewHelperVariableContainer
	 * @return void
	 */
	public function injectViewHelperVariableContainer(\F3\Fluid\Core\ViewHelper\ViewHelperVariableContainer $viewHelperVariableContainer);

	/**
	 * Get the ViewHelperVariableContainer
	 *
	 * @return \F3\Fluid\Core\ViewHelper\ViewHelperVariableContainer
	 */
	public function getViewHelperVariableContainer();
}
?>