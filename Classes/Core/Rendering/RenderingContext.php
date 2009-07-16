<?php
declare(ENCODING = 'utf-8');
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
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @internal
 * @scope prototype
 */
class RenderingContext {

	/**
	 * Template Variable Container. Contains all variables available through object accessors in the template
	 * @var F3\Fluid\Core\ViewHelper\TemplateVariableContainer
	 */
	protected $templateVariableContainer;

	/**
	 * Object factory which is bubbled through. The ViewHelperNode cannot get an ObjectFactory injected because
	 * the whole syntax tree should be cacheable
	 * @var F3\FLOW3\Object\FactoryInterface
	 */
	protected $objectFactory;

	/**
	 * Controller context being passed to the ViewHelper
	 * @var F3\FLOW3\MVC\Controller\ControllerContext
	 */
	protected $controllerContext;

	/**
	 * Rendering Context
	 * @var F3\Fluid\Core\Rendering\RenderingConfiguration
	 */
	protected $renderingConfiguration;

	/**
	 * ViewHelper Variable Container
	 * @var F3\Fluid\Core\ViewHelpers\ViewHelperVariableContainer
	 */
	protected $viewHelperVariableContainer;

	/**
	 * TRUE if the arguments of a ViewHelper are currently evaluated
	 * @var boolean
	 */
	protected $objectAccessorPostProcessorEnabled = TRUE;

	/**
	 * Inject the object factory
	 *
	 * @param F3\FLOW3\Object\FactoryInterface $objectFactory
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function injectObjectFactory(\F3\FLOW3\Object\FactoryInterface $objectFactory) {
		$this->objectFactory = $objectFactory;
	}

	/**
	 * Returns the object factory. Only the ViewHelperNode should do this.
	 *
	 * @param F3\FLOW3\Object\FactoryInterface $objectFactory
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getObjectFactory() {
		return $this->objectFactory;
	}

	/**
	 * Sets the template variable container containing all variables available through ObjectAccessors
	 * in the template
	 *
	 * @param F3\Fluid\Core\ViewHelper\TemplateVariableContainer $templateVariableContainer The template variable container to set
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setTemplateVariableContainer(\F3\Fluid\Core\ViewHelper\TemplateVariableContainer $templateVariableContainer) {
		$this->templateVariableContainer = $templateVariableContainer;
	}

	/**
	 * Get the template variable container
	 *
	 * @return F3\Fluid\Core\ViewHelper\TemplateVariableContainer The Template Variable Container
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getTemplateVariableContainer() {
		return $this->templateVariableContainer;
	}

	/**
	 * Set the controller context which will be passed to the ViewHelper
	 *
	 * @param F3\FLOW3\MVC\Controller\ControllerContext $controllerContext The controller context to set
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setControllerContext(\F3\FLOW3\MVC\Controller\ControllerContext $controllerContext) {
		$this->controllerContext = $controllerContext;
	}

	/**
	 * Get the controller context which will be passed to the ViewHelper
	 *
	 * @return F3\FLOW3\MVC\Controller\ControllerContext The controller context to set
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getControllerContext() {
		return $this->controllerContext;
	}

	/**
	 * Set the rendering configuration for the current rendering process
	 *
	 * @param F3\Fluid\Core\Rendering\RenderingConfiguration The Rendering Configuration to be set
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setRenderingConfiguration(\F3\Fluid\Core\Rendering\RenderingConfiguration $renderingConfiguration) {
		$this->renderingConfiguration = $renderingConfiguration;
	}

	/**
	 * Get the current rendering configuration
	 *
	 * @return F3\Fluid\Core\Rendering\RenderingConfiguration The rendering configuration currently active
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getRenderingConfiguration() {
		return $this->renderingConfiguration;
	}

	/**
	 * Set the argument evaluation mode. Should be set to TRUE if the arguments are currently being parsed.
	 * FALSE if we do not parse arguments currently
	 *
	 * @param boolean $objectAccessorPostProcessorEnabled Argument evaluation mode to be set
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setObjectAccessorPostProcessorEnabled($objectAccessorPostProcessorEnabled) {
		$this->objectAccessorPostProcessorEnabled = (boolean)$objectAccessorPostProcessorEnabled;
	}

	/**
	 * if TRUE, then we are currently in Argument Evaluation mode. False otherwise.
	 * Is used to make sure that ObjectAccessors are PostProcessed if we are NOT in Argument Evaluation Mode
	 *
	 * @return boolean TRUE if we are currently evaluating arguments, FALSE otherwise
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function isObjectAccessorPostProcessorEnabled() {
		return $this->objectAccessorPostProcessorEnabled;
	}

	/**
	 * Set the ViewHelperVariableContainer
	 *
	 * @param F3\Fluid\Core\ViewHelpers\ViewHelperVariableContainer $viewHelperVariableContainer
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setViewHelperVariableContainer(\F3\Fluid\Core\ViewHelper\ViewHelperVariableContainer $viewHelperVariableContainer) {
		$this->viewHelperVariableContainer = $viewHelperVariableContainer;
	}

	/**
	 * Get the ViewHelperVariableContainer
	 *
	 * @return F3\Fluid\Core\ViewHelpers\ViewHelperVariableContainer
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getViewHelperVariableContainer() {
		return $this->viewHelperVariableContainer;
	}
}
?>