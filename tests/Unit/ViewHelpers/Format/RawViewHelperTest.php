<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers\Format;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Tests\UnitTestCase;
use TYPO3\Fluid\ViewHelpers\Format\RawViewHelper;

/**
 * Test for \TYPO3\Fluid\ViewHelpers\Format\RawViewHelper
 */
class RawViewHelperTest extends UnitTestCase {

	/**
	 * @var RawViewHelper
	 */
	protected $viewHelper;

	public function setUp() {
		$this->viewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Format\RawViewHelper', array('renderChildren'));
	}

	/**
	 * @test
	 */
	public function viewHelperDeactivatesEscapingInterceptor() {
		$this->assertFalse($this->viewHelper->isOutputEscapingEnabled());
	}

	/**
	 * @test
	 */
	public function renderReturnsUnmodifiedValueIfSpecified() {
		$value = 'input value " & äöüß@';
		$this->viewHelper->expects($this->never())->method('renderChildren');
		$actualResult = $this->viewHelper->render($value);
		$this->assertEquals($value, $actualResult);
	}

	/**
	 * @test
	 */
	public function renderReturnsUnmodifiedChildNodesIfNoValueIsSpecified() {
		$childNodes = 'input value " & äöüß@';
		$this->viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue($childNodes));
		$actualResult = $this->viewHelper->render();
		$this->assertEquals($childNodes, $actualResult);
	}
}
