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

use TYPO3\Flow\Tests\UnitTestCase;

/**
 * Test for \TYPO3\Fluid\ViewHelpers\Format\CurrencyViewHelper
 */
class CurrencyViewHelperTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function viewHelperRoundsFloatCorrectly() {
		$viewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Format\CurrencyViewHelper', array('renderChildren'));
		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue(123.456));
		$actualResult = $viewHelper->render();
		$this->assertEquals('123,46', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperRendersCurrencySign() {
		$viewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Format\CurrencyViewHelper', array('renderChildren'));
		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue(123));
		$actualResult = $viewHelper->render('foo');
		$this->assertEquals('123,00 foo', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperRespectsDecimalSeparator() {
		$viewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Format\CurrencyViewHelper', array('renderChildren'));
		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue(12345));
		$actualResult = $viewHelper->render('', '|');
		$this->assertEquals('12.345|00', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperRespectsThousandsSeparator() {
		$viewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Format\CurrencyViewHelper', array('renderChildren'));
		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue(12345));
		$actualResult = $viewHelper->render('', ',', '|');
		$this->assertEquals('12|345,00', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperRendersNullValues() {
		$viewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Format\CurrencyViewHelper', array('renderChildren'));
		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue(NULL));
		$actualResult = $viewHelper->render();
		$this->assertEquals('0,00', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperRendersNegativeAmounts() {
		$viewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Format\CurrencyViewHelper', array('renderChildren'));
		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue(-123.456));
		$actualResult = $viewHelper->render();
		$this->assertEquals('-123,46', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperUsesNumberFormatterOnGivenLocale() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Format\CurrencyViewHelper', array('renderChildren'));

		$mockNumberFormatter = $this->getMock('TYPO3\Flow\I18n\Formatter\NumberFormatter', array('formatCurrencyNumber'));
		$mockNumberFormatter->expects($this->once())->method('formatCurrencyNumber');
		$this->inject($viewHelper, 'numberFormatter', $mockNumberFormatter);

		$viewHelper->setArguments(array('forceLocale' => 'de_DE'));
		$viewHelper->render('EUR', '#', '*');
	}

	/**
	 * @test
	 */
	public function viewHelperFetchesCurrentLocaleViaI18nService() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Format\CurrencyViewHelper', array('renderChildren'));

		$localizationConfiguration = new \TYPO3\Flow\I18n\Configuration('de_DE');

		$mockLocalizationService = $this->getMock('TYPO3\Flow\I18n\Service', array('getConfiguration'));
		$mockLocalizationService->expects($this->once())->method('getConfiguration')->will($this->returnValue($localizationConfiguration));
		$this->inject($viewHelper, 'localizationService', $mockLocalizationService);

		$mockNumberFormatter = $this->getMock('TYPO3\Flow\I18n\Formatter\NumberFormatter', array('formatCurrencyNumber'));
		$mockNumberFormatter->expects($this->once())->method('formatCurrencyNumber');
		$this->inject($viewHelper, 'numberFormatter', $mockNumberFormatter);

		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue(123.456));


		$viewHelper->setArguments(array('forceLocale' => TRUE));
		$viewHelper->render('EUR');
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception\InvalidVariableException
	 */
	public function viewHelperThrowsExceptionIfLocaleIsUsedWithoutExplicitCurrencySign() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Format\CurrencyViewHelper', array('renderChildren'));

		$localizationConfiguration = new \TYPO3\Flow\I18n\Configuration('de_DE');

		$mockLocalizationService = $this->getMock('TYPO3\Flow\I18n\Service', array('getConfiguration'));
		$mockLocalizationService->expects($this->once())->method('getConfiguration')->will($this->returnValue($localizationConfiguration));
		$this->inject($viewHelper, 'localizationService', $mockLocalizationService);

		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue(123.456));
		$viewHelper->setArguments(array('forceLocale' => TRUE));
		$viewHelper->render();
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception
	 */
	public function viewHelperConvertsI18nExceptionsIntoViewHelperExceptions() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Format\CurrencyViewHelper', array('renderChildren'));

		$localizationConfiguration = new \TYPO3\Flow\I18n\Configuration('de_DE');

		$mockLocalizationService = $this->getMock('TYPO3\Flow\I18n\Service', array('getConfiguration'));
		$mockLocalizationService->expects($this->once())->method('getConfiguration')->will($this->returnValue($localizationConfiguration));
		$this->inject($viewHelper, 'localizationService', $mockLocalizationService);

		$mockNumberFormatter = $this->getMock('TYPO3\Flow\I18n\Formatter\NumberFormatter', array('formatCurrencyNumber'));
		$mockNumberFormatter->expects($this->once())->method('formatCurrencyNumber')->will($this->throwException(new \TYPO3\Flow\I18n\Exception()));
		$this->inject($viewHelper, 'numberFormatter', $mockNumberFormatter);

		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue(123.456));
		$viewHelper->setArguments(array('forceLocale' => TRUE));
		$viewHelper->render('$');
	}
}
