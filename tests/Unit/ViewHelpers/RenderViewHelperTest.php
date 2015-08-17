<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\ViewHelpers\RenderViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;

/**
 * Testcase for RenderViewHelper
 */
class RenderViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @test
	 */
	public function testInitializeArgumentsRegistersExpectedArguments() {
		$instance = $this->getMock('TYPO3Fluid\\Fluid\\ViewHelpers\\RenderViewHelper', array('registerArgument'));
		$instance->expects($this->at(0))->method('registerArgument')->with('section', 'string', $this->anything(), FALSE, NULL);
		$instance->expects($this->at(1))->method('registerArgument')->with('partial', 'string', $this->anything(), FALSE, NULL);
		$instance->expects($this->at(2))->method('registerArgument')->with('arguments', 'array', $this->anything(), FALSE, array());
		$instance->expects($this->at(3))->method('registerArgument')->with('optional', 'boolean', $this->anything(), FALSE, FALSE);
		$instance->expects($this->at(4))->method('registerArgument')->with('default', 'mixed', $this->anything(), FALSE, FALSE);
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
		$instance = $this->getMock('TYPO3Fluid\\Fluid\\ViewHelpers\\RenderViewHelper', array('renderChildren'));
		$instance->expects($this->any())->method('renderChildren')->willReturn(NULL);
		$renderingContext = new RenderingContext();
		$paths = $this->getMock('TYPO3Fluid\\Fluid\\View\\TemplatePaths', array('sanitizePath'));
		$paths->expects($this->any())->method('sanitizePath')->willReturnArgument(0);
		$viewHelperVariableContainer = new ViewHelperVariableContainer();
		$view = $this->getMock('TYPO3Fluid\\Fluid\\View\\TemplateView', $methods, array($paths, $renderingContext));
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
				array('partial' => NULL, 'section' => NULL, 'arguments' => array(), 'optional' => FALSE, 'default' => NULL),
				NULL
			),
			array(
				array('partial' => 'foo-partial', 'section' => NULL, 'arguments' => array(), 'optional' => FALSE, 'default' => NULL),
				'renderPartial'
			),
			array(
				array('partial' => 'foo-partial', 'section' => 'foo-section', 'arguments' => array(), 'optional' => FALSE, 'default' => NULL),
				'renderPartial'
			),
			array(
				array('partial' => NULL, 'section' => 'foo-section', 'arguments' => array(), 'optional' => FALSE, 'default' => NULL),
				'renderSection'
			),
		);
	}

}
