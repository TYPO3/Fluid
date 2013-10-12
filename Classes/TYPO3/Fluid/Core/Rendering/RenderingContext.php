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
use TYPO3\Flow\Object\ObjectManagerInterface;
use TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer;
use TYPO3\Fluid\Core\ViewHelper\ViewHelperVariableContainer;

/**
 * The rendering context that contains useful information during rendering time of a Fluid template
 */
class RenderingContext implements RenderingContextInterface {

	/**
	 * Template Variable Container. Contains all variables available through object accessors in the template
	 *
	 * @var TemplateVariableContainer
	 */
	protected $templateVariableContainer;

	/**
	 * Object manager which is bubbled through. The ViewHelperNode cannot get an ObjectManager injected because
	 * the whole syntax tree should be cacheable
	 *
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * Controller context being passed to the ViewHelper
	 *
	 * @var ControllerContext
	 */
	protected $controllerContext;

	/**
	 * ViewHelper Variable Container
	 *
	 * @var ViewHelperVariableContainer
	 */
	protected $viewHelperVariableContainer;

	/**
	 * Inject the object manager
	 *
	 * @param ObjectManagerInterface $objectManager
	 */
	public function injectObjectManager(ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Returns the object manager. Only the ViewHelperNode should do this.
	 *
	 * @return ObjectManagerInterface
	 */
	public function getObjectManager() {
		return $this->objectManager;
	}

	/**
	 * Injects the template variable container containing all variables available through ObjectAccessors
	 * in the template
	 *
	 * @param TemplateVariableContainer $templateVariableContainer The template variable container to set
	 */
	public function injectTemplateVariableContainer(TemplateVariableContainer $templateVariableContainer) {
		$this->templateVariableContainer = $templateVariableContainer;
	}

	/**
	 * Get the template variable container
	 *
	 * @return TemplateVariableContainer The Template Variable Container
	 */
	public function getTemplateVariableContainer() {
		return $this->templateVariableContainer;
	}

	/**
	 * Set the controller context which will be passed to the ViewHelper
	 *
	 * @param ControllerContext $controllerContext The controller context to set
	 */
	public function setControllerContext(ControllerContext $controllerContext) {
		$this->controllerContext = $controllerContext;
	}

	/**
	 * Get the controller context which will be passed to the ViewHelper
	 *
	 * @return ControllerContext The controller context to set
	 */
	public function getControllerContext() {
		return $this->controllerContext;
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
}
