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

/**
 * Test for \TYPO3\Fluid\ViewHelpers\Format\NumberViewHelper
 */
class NumberViewHelperTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function formatNumberDefaultsToEnglishNotationWithTwoDecimals() {
		$viewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Format\NumberViewHelper', array('renderChildren'));
		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue(10000.0 / 3.0));
		$actualResult = $viewHelper->render();
		$this->assertEquals('3,333.33', $actualResult);
	}

	/**
	 * @test
	 */
	public function formatNumberWithDecimalsDecimalPointAndSeparator() {
		$viewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Format\NumberViewHelper', array('renderChildren'));
		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue(10000.0 / 3.0));
		$actualResult = $viewHelper->render(3, ',', '.');
		$this->assertEquals('3.333,333', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperUsesNumberFormatterOnGivenLocale() {
		$numberFormatterMock = $this->getMock('TYPO3\Flow\I18n\Formatter\NumberFormatter', array('formatDecimalNumber'));
		$numberFormatterMock->expects($this->once())->method('formatDecimalNumber');

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Format\NumberViewHelper', array('renderChildren'));
		$viewHelper->_set('numberFormatter', $numberFormatterMock);
		$viewHelper->setArguments(array('forceLocale' => 'de_DE'));
		$viewHelper->render(2, '#', '*');
	}

	/**
	 * @test
	 */
	public function viewHelperFetchesCurrentLocaleViaI18nService() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Format\NumberViewHelper', array('renderChildren'));

		$localizationConfiguration = new \TYPO3\Flow\I18n\Configuration('de_DE');

		$mockLocalizationService = $this->getMock('TYPO3\Flow\I18n\Service', array('getConfiguration'));
		$mockLocalizationService->expects($this->once())->method('getConfiguration')->will($this->returnValue($localizationConfiguration));
		$this->inject($viewHelper, 'localizationService', $mockLocalizationService);

		$mockNumberFormatter = $this->getMock('TYPO3\Flow\I18n\Formatter\NumberFormatter', array('formatDecimalNumber'));
		$mockNumberFormatter->expects($this->once())->method('formatDecimalNumber');
		$this->inject($viewHelper, 'numberFormatter', $mockNumberFormatter);

		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue(123.456));
		$viewHelper->setArguments(array('forceLocale' => TRUE));
		$viewHelper->render();
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception
	 */
	public function viewHelperConvertsI18nExceptionsIntoViewHelperExceptions() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Format\NumberViewHelper', array('renderChildren'));

		$localizationConfiguration = new \TYPO3\Flow\I18n\Configuration('de_DE');

		$mockLocalizationService = $this->getMock('TYPO3\Flow\I18n\Service', array('getConfiguration'));
		$mockLocalizationService->expects($this->once())->method('getConfiguration')->will($this->returnValue($localizationConfiguration));
		$this->inject($viewHelper, 'localizationService', $mockLocalizationService);

		$mockNumberFormatter = $this->getMock('TYPO3\Flow\I18n\Formatter\NumberFormatter', array('formatDecimalNumber'));
		$mockNumberFormatter->expects($this->once())->method('formatDecimalNumber')->will($this->throwException(new \TYPO3\Flow\I18n\Exception()));
		$this->inject($viewHelper, 'numberFormatter', $mockNumberFormatter);

		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue(123.456));
		$viewHelper->setArguments(array('forceLocale' => TRUE));
		$viewHelper->render();
	}
}
