<?php
namespace NamelessCoder\Fluid\Tests\Unit\ViewHelpers\Format;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\Tests\UnitTestCase;
use NamelessCoder\Fluid\ViewHelpers\Format\RawViewHelper;

/**
 * Test for \NamelessCoder\Fluid\ViewHelpers\Format\RawViewHelper
 */
class RawViewHelperTest extends UnitTestCase {

	/**
	 * @var RawViewHelper
	 */
	protected $viewHelper;

	public function setUp() {
		$this->viewHelper = $this->getMock('NamelessCoder\Fluid\ViewHelpers\Format\RawViewHelper', array('renderChildren'));
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
		$this->viewHelper->setArguments(array('value' => $value));
		$actualResult = $this->viewHelper->render();
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
