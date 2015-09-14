<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Testcase for ElseViewHelper
 */
class ThenViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @test
	 */
	public function renderRendersChildren() {
		$viewHelper = $this->getMock('TYPO3Fluid\Fluid\ViewHelpers\ThenViewHelper', array('renderChildren'));

		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue('foo'));
		$actualResult = $viewHelper->render();
		$this->assertEquals('foo', $actualResult);
	}
}
