<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\Rendering\RenderingContext;
use TYPO3\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Fluid\Core\ViewHelper\TagBuilder;
use TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer;
use TYPO3\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3\Fluid\Tests\UnitTestCase;

/**
 * Base test class for testing view helpers
 */
abstract class ViewHelperBaseTestcase extends UnitTestCase {

	/**
	 * @var ViewHelperVariableContainer
	 */
	protected $viewHelperVariableContainer;

	/**
	 * Mock contents of the $viewHelperVariableContainer in the format:
	 * array(
	 *  'Some\ViewHelper\Class' => array('key1' => 'value1', 'key2' => 'value2')
	 * )
	 *
	 * @var array
	 */
	protected $viewHelperVariableContainerData = array();

	/**
	 * @var TemplateVariableContainer
	 */
	protected $templateVariableContainer;

	/**
	 * @var TagBuilder
	 */
	protected $tagBuilder;

	/**
	 * @var array
	 */
	protected $arguments = array();

	/**
	 * @var RenderingContext
	 */
	protected $renderingContext;

	/**
	 * @return void
	 */
	public function setUp() {
		$this->viewHelperVariableContainer = new ViewHelperVariableContainer();
		$this->templateVariableContainer = new TemplateVariableContainer();
		$this->renderingContext = new RenderingContext();
		$this->renderingContext->injectTemplateVariableContainer($this->templateVariableContainer);
		$this->renderingContext->injectViewHelperVariableContainer($this->viewHelperVariableContainer);
	}

	/**
	 * @param string $viewHelperName
	 * @param string $key
	 * @return boolean
	 */
	public function viewHelperVariableContainerExistsCallback($viewHelperName, $key) {
		return isset($this->viewHelperVariableContainerData[$viewHelperName][$key]);
	}

	/**
	 * @param string $viewHelperName
	 * @param string $key
	 * @return boolean
	 */
	public function viewHelperVariableContainerGetCallback($viewHelperName, $key) {
		return $this->viewHelperVariableContainerData[$viewHelperName][$key];
	}

	/**
	 * @param AbstractViewHelper $viewHelper
	 * @return void
	 */
	protected function injectDependenciesIntoViewHelper(AbstractViewHelper $viewHelper) {
		$viewHelper->setRenderingContext($this->renderingContext);
		$viewHelper->setArguments($this->arguments);
		if ($viewHelper instanceof AbstractTagBasedViewHelper) {
			$viewHelper->injectTagBuilder($this->tagBuilder);
		}
	}
}
