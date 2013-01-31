<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers\Format;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Http\Uri;

/**
 *
 */
class UrlencodeViewHelperTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @var \TYPO3\Fluid\ViewHelpers\Format\UrlEncodeViewHelper
	 */
	protected $viewHelper;

	public function setUp() {
		$this->viewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Format\UrlencodeViewHelper', array('renderChildren'));
	}

	/**
	 * @test
	 */
	public function viewHelperDeactivatesEscapingInterceptor() {
		$this->assertFalse($this->viewHelper->isEscapingInterceptorEnabled());
	}

	/**
	 * @test
	 */
	public function renderUsesValueAsSourceIfSpecified() {
		$this->viewHelper->expects($this->never())->method('renderChildren');
		$actualResult = $this->viewHelper->render('Source');
		$this->assertEquals('Source', $actualResult);
	}

	/**
	 * @test
	 */
	public function renderUsesChildnodesAsSourceIfSpecified() {
		$this->viewHelper->expects($this->atLeastOnce())->method('renderChildren')->will($this->returnValue('Source'));
		$actualResult = $this->viewHelper->render();
		$this->assertEquals('Source', $actualResult);
	}

	/**
	 * @test
	 */
	public function renderDoesNotModifyValueIfItDoesNotContainSpecialCharacters() {
		$source = 'StringWithoutSpecialCharacters';
		$actualResult = $this->viewHelper->render($source);
		$this->assertSame($source, $actualResult);
	}

	/**
	 * @test
	 */
	public function renderEncodesString() {
		$source = 'Foo @+%/ "';
		$expectedResult = 'Foo%20%40%2B%25%2F%20%22';
		$actualResult = $this->viewHelper->render($source);
		$this->assertEquals($expectedResult, $actualResult);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception
	 */
	public function renderThrowsExceptionIfItIsNoStringAndHasNoToStringMethod() {
		$source = new \stdClass();
		$this->viewHelper->render($source);
	}

	/**
	 * @test
	 */
	public function renderRendersObjectWithToStringMethod() {
		$source = new Uri('http://typo3.com/foo&bar=1');
		$actualResult = $this->viewHelper->render($source);
		$this->assertEquals(urlencode('http://typo3.com/foo&bar=1'), $actualResult);
	}
}
?>
