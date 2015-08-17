<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\ViewHelpers\DebugViewHelper;

/**
 * Testcase for DebugViewHelper
 */
class DebugViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @test
	 */
	public function testInitializeArgumentsRegistersExpectedArguments() {
		$instance = $this->getMock('TYPO3Fluid\\Fluid\\ViewHelpers\\DebugViewHelper', array('registerArgument'));
		$instance->expects($this->at(0))->method('registerArgument')->with('typeOnly', 'boolean', $this->anything(), FALSE, FALSE);
		$instance->initializeArguments();
	}

	/**
	 * @dataProvider getRenderTestValues
	 * @param mixed $value
	 * @param array $arguments
	 * @param string $expected
	 */
	public function testRender($value, array $arguments, $expected) {
		$instance = $this->getMock('TYPO3Fluid\\Fluid\\ViewHelpers\\DebugViewHelper', array('renderChildren'));
		$instance->expects($this->once())->method('renderChildren')->willReturn($value);
		$instance->setArguments($arguments);
		$result = $instance->render();
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return array
	 */
	public function getRenderTestValues() {
		return array(
			array('test', array('typeOnly' => FALSE), 'string(4) "test"' . PHP_EOL),
			array('test', array('typeOnly' => TRUE), 'string'),
		);
	}

}
