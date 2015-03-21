<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\ViewHelpers\RenderViewHelper;
use TYPO3\Fluid\Core\Rendering\RenderingContext;
use TYPO3\Fluid\Core\ViewHelper\ViewHelperVariableContainer;

/**
 * Testcase for RenderViewHelper
 */
class RenderViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @test
	 */
	public function testInitializeArgumentsRegistersExpectedArguments() {
		$instance = $this->getMock('TYPO3\\Fluid\\ViewHelpers\\RenderViewHelper', array('registerArgument'));
		$instance->expects($this->at(0))->method('registerArgument')->with('section', 'string', $this->anything(), FALSE, NULL);
		$instance->expects($this->at(1))->method('registerArgument')->with('partial', 'string', $this->anything(), FALSE, NULL);
		$instance->expects($this->at(2))->method('registerArgument')->with('arguments', 'array', $this->anything(), FALSE, array());
		$instance->expects($this->at(3))->method('registerArgument')->with('optional', 'boolean', $this->anything(), FALSE, FALSE);
		$instance->initializeArguments();
	}

	/**
	 * @test
	 * @dataProvider getRenderTestValues
	 * @param array $arguments
	 * @param string|NULL $expectedViewMethod
	 */
	public function testRender(array $arguments, $expectedViewMethod) {
		if ($expectedViewMethod) {
			$methods = array($expectedViewMethod);
		} else {
			$methods = array('renderPartial', 'renderSection');
		}
		$instance = new RenderViewHelper();
		$renderingContext = new RenderingContext();
		$viewHelperVariableContainer = new ViewHelperVariableContainer();
		$view = $this->getMock('TYPO3\\Fluid\\View\\TemplateView', $methods, array(), '', FALSE);
		$viewHelperVariableContainer->setView($view);
		$renderingContext->injectViewHelperVariableContainer($viewHelperVariableContainer);
		$instance->setArguments($arguments);
		$instance->setRenderingContext($renderingContext);
		$instance->render();

	}

	/**
	 * @return array
	 */
	public function getRenderTestValues() {
		return array(
			array(
				array('partial' => NULL, 'section' => NULL, 'arguments' => array(), 'optional' => FALSE),
				NULL
			),
			array(
				array('partial' => 'foo-partial', 'section' => NULL, 'arguments' => array(), 'optional' => FALSE),
				'renderPartial'
			),
			array(
				array('partial' => 'foo-partial', 'section' => 'foo-section', 'arguments' => array(), 'optional' => FALSE),
				'renderPartial'
			),
			array(
				array('partial' => NULL, 'section' => 'foo-section', 'arguments' => array(), 'optional' => FALSE),
				'renderSection'
			),
		);
	}

}
