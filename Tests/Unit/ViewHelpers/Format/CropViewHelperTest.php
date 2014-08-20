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
 * Test for \TYPO3\Fluid\ViewHelpers\Format\CropViewHelper
 */
class CropViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @var \TYPO3\Fluid\ViewHelpers\Format\CropViewHelper|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $viewHelper;

	public function setUp() {
		parent::setUp();
		$this->viewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Format\CropViewHelper', array('renderChildren'));
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
		$this->viewHelper->initializeArguments();
	}

	/**
	 * @test
	 */
	public function viewHelperDoesNotCropTextIfMaxCharactersIsLargerThanNumberOfCharacters() {
		$this->viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue('some text'));
		$actualResult = $this->viewHelper->render(50);
		$this->assertEquals('some text', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperAppendsEllipsisToTruncatedText() {
		$this->viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue('some text'));
		$actualResult = $this->viewHelper->render(5);
		$this->assertEquals('some ...', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperAppendsCustomSuffix() {
		$this->viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue('some text'));
		$actualResult = $this->viewHelper->render(3, '[custom suffix]');
		$this->assertEquals('som[custom suffix]', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperAppendsSuffixEvenIfResultingTextIsLongerThanMaxCharacters() {
		$this->viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue('some text'));
		$actualResult = $this->viewHelper->render(8);
		$this->assertEquals('some tex...', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperUsesProvidedValueInsteadOfRenderingChildren() {
		$this->viewHelper->expects($this->never())->method('renderChildren');
		$actualResult = $this->viewHelper->render(8, '...', 'some text');
		$this->assertEquals('some tex...', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperDoesNotFallbackToRenderChildNodesIfEmptyValueArgumentIsProvided() {
		$this->viewHelper->expects($this->never())->method('renderChildren');
		$actualResult = $this->viewHelper->render(8, '...', '');
		$this->assertEquals('', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperHandlesMultiByteValuesCorrectly() {
		$this->viewHelper->expects($this->never())->method('renderChildren');
		$actualResult = $this->viewHelper->render(3, '...', 'Äßütest');
		$this->assertEquals('Äßü...', $actualResult);
	}
}
