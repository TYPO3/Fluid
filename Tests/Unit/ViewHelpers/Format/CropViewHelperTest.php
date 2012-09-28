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
class CropViewHelperTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function viewHelperDoesNotCropTextIfMaxCharactersIsLargerThanNumberOfCharacters() {
		$viewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Format\CropViewHelper', array('renderChildren'));
		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue('some text'));
		$actualResult = $viewHelper->render(50);
		$this->assertEquals('some text', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperAppendsEllipsisToTruncatedText() {
		$viewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Format\CropViewHelper', array('renderChildren'));
		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue('some text'));
		$actualResult = $viewHelper->render(5);
		$this->assertEquals('some ...', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperAppendsCustomSuffix() {
		$viewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Format\CropViewHelper', array('renderChildren'));
		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue('some text'));
		$actualResult = $viewHelper->render(3, '[custom suffix]');
		$this->assertEquals('som[custom suffix]', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperAppendsSuffixEvenIfResultingTextIsLongerThanMaxCharacters() {
		$viewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Format\CropViewHelper', array('renderChildren'));
		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue('some text'));
		$actualResult = $viewHelper->render(8);
		$this->assertEquals('some tex...', $actualResult);
	}
}
?>
