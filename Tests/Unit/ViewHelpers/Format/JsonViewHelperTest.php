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

/**
 */
class JsonViewHelperTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @var \TYPO3\Fluid\ViewHelpers\Format\JsonViewHelper
	 */
	protected $mockViewHelper;

	/**
	 */
	protected function setUp() {
		parent::setUp();
		$this->mockViewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Format\JsonViewHelper', array('renderChildren'));
	}

	/**
	 * @test
	 */
	public function viewHelperConvertsSimpleAssociativeArrayGivenAsChildren() {
		$this->mockViewHelper
				->expects($this->once())
				->method('renderChildren')
				->will($this->returnValue(array('foo' => 'bar')));

		$actualResult = $this->mockViewHelper->render();
		$this->assertEquals('{"foo":"bar"}', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperConvertsSimpleAssociativeArrayGivenAsDataArgument() {
		$this->mockViewHelper
				->expects($this->never())
				->method('renderChildren');

		$actualResult = $this->mockViewHelper->render(array('foo' => 'bar'));
		$this->assertEquals('{"foo":"bar"}', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperOutputsArrayOnIndexedArrayInputAndObjectIfSetSo() {
		$this->mockViewHelper
				->expects($this->any())
				->method('renderChildren')
				->will($this->returnValue(array('foo', 'bar', 42)));

		$this->assertEquals('["foo","bar",42]', $this->mockViewHelper->render());
		$this->assertEquals('{"0":"foo","1":"bar","2":42}', $this->mockViewHelper->render(NULL, TRUE));
	}

	/**
	 * @test
	 */
	public function viewHelperEscapesGreaterThanLowerThanCharacters() {
		$this->assertEquals('["\u003Cfoo\u003E","bar","elephant \u003E mouse"]', $this->mockViewHelper->render(array('<foo>', 'bar', 'elephant > mouse')));
		$this->assertEquals('{"0":"\u003Cfoo\u003E","1":"bar","2":"elephant \u003E mouse"}', $this->mockViewHelper->render(array('<foo>', 'bar', 'elephant > mouse'), TRUE));
	}

}
?>
