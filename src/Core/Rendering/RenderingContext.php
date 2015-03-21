<?php
namespace TYPO3\Fluid\Core\Rendering;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer;
use TYPO3\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3\Fluid\View\TemplatePaths;

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
	 * ViewHelper Variable Container
	 *
	 * @var ViewHelperVariableContainer
	 */
	protected $viewHelperVariableContainer;

	/**
	 * @var string
	 */
	protected $controllerName;

	/**
	 * @var string
	 */
	protected $controllerAction;

	/**
	 * @var string
	 */
	protected $format = TemplatePaths::DEFAULT_FORMAT;

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

	/**
	 * @return string
	 */
	public function getFormat() {
		return $this->format;
	}

	/**
	 * @param string $format
	 * @return void
	 */
	public function setFormat($format) {
		$this->format = $format;
	}

}
