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
class CurrencyViewHelperTest extends \TYPO3\Flow\Tests\UnitTestCase {

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
		$numberFormatterMock = $this->getMock('TYPO3\Flow\I18n\Formatter\NumberFormatter', array('formatCurrencyNumber'));
		$numberFormatterMock->expects($this->once())->method('formatCurrencyNumber');

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Format\CurrencyViewHelper', array('renderChildren'));
		$viewHelper->_set('numberFormatter', $numberFormatterMock);
		$viewHelper->render('EUR', '#', '*', 'de_DE');
	}

	/**
	 * @test
	 */
	public function viewHelperFetchesCurrentLocaleViaI18nService() {
		$localizationConfiguration = new \TYPO3\Flow\I18n\Configuration('de_DE');

		$localizationServiceMock = $this->getMock('TYPO3\Flow\I18n\Service', array('getConfiguration'));
		$localizationServiceMock->expects($this->once())->method('getConfiguration')->will($this->returnValue($localizationConfiguration));

		$numberFormatterMock = $this->getMock('TYPO3\Flow\I18n\Formatter\NumberFormatter', array('formatCurrencyNumber'));
		$numberFormatterMock->expects($this->once())->method('formatCurrencyNumber');

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Format\CurrencyViewHelper', array('renderChildren'));
		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue(123.456));
		$viewHelper->_set('localizationService', $localizationServiceMock);
		$viewHelper->_set('numberFormatter', $numberFormatterMock);
		$viewHelper->render('EUR', ',', '.', TRUE);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception\InvalidVariableException
	 */
	public function viewHelperThrowsExceptionIfLocaleIsUsedWithoutExplicitCurrencySign() {
		$viewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Format\CurrencyViewHelper', array('renderChildren'));
		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue(123.456));
		$viewHelper->render('', ',', '.', TRUE);
	}
}
?>