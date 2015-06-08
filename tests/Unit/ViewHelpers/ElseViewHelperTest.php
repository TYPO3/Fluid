<?php
namespace NamelessCoder\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Testcase for ElseViewHelper
 */
class ElseViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @test
	 */
	public function testInitializeArgumentsRegistersExpectedArguments() {
		$instance = $this->getMock('NamelessCoder\\Fluid\\ViewHelpers\\ElseViewHelper', array('registerArgument'));
		$instance->expects($this->at(0))->method('registerArgument')->with('if', 'boolean', $this->anything(), FALSE, NULL);
		$instance->initializeArguments();
	}

	/**
	 * @test
	 */
	public function renderRendersChildren() {
		$viewHelper = $this->getMock('NamelessCoder\Fluid\ViewHelpers\ElseViewHelper', array('renderChildren'));

		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue('foo'));
		$actualResult = $viewHelper->render();
		$this->assertEquals('foo', $actualResult);
	}
}
