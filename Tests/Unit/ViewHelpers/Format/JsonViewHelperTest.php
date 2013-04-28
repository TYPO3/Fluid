<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers\Format;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

require_once(__DIR__ . '/../ViewHelperBaseTestcase.php');

use TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase;

/**
 * Test for \TYPO3\Fluid\ViewHelpers\Format\JsonViewHelper
 */
class JsonViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @var \TYPO3\Fluid\ViewHelpers\Format\JsonViewHelper
	 */
	protected $viewHelper;

	public function setUp() {
		parent::setUp();
		$this->viewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Format\JsonViewHelper', array('renderChildren'));
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
		$this->viewHelper->initializeArguments();
	}

	/**
	 * @test
	 */
	public function viewHelperConvertsSimpleAssociativeArrayGivenAsChildren() {
		$this->viewHelper
				->expects($this->once())
				->method('renderChildren')
				->will($this->returnValue(array('foo' => 'bar')));

		$actualResult = $this->viewHelper->render();
		$this->assertEquals('{"foo":"bar"}', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperConvertsSimpleAssociativeArrayGivenAsDataArgument() {
		$this->viewHelper
				->expects($this->never())
				->method('renderChildren');

		$actualResult = $this->viewHelper->render(array('foo' => 'bar'));
		$this->assertEquals('{"foo":"bar"}', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperOutputsArrayOnIndexedArrayInputAndObjectIfSetSo() {
		$this->viewHelper
				->expects($this->any())
				->method('renderChildren')
				->will($this->returnValue(array('foo', 'bar', 42)));

		$this->assertEquals('["foo","bar",42]', $this->viewHelper->render());
		$this->assertEquals('{"0":"foo","1":"bar","2":42}', $this->viewHelper->render(NULL, TRUE));
	}

	/**
	 * @test
	 */
	public function viewHelperEscapesGreaterThanLowerThanCharacters() {
		$this->assertEquals('["\u003Cfoo\u003E","bar","elephant \u003E mouse"]', $this->viewHelper->render(array('<foo>', 'bar', 'elephant > mouse')));
		$this->assertEquals('{"0":"\u003Cfoo\u003E","1":"bar","2":"elephant \u003E mouse"}', $this->viewHelper->render(array('<foo>', 'bar', 'elephant > mouse'), TRUE));
	}

}
