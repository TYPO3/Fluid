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
class DateViewHelperTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function viewHelperFormatsDateCorrectly() {
		$viewHelper = new \TYPO3\Fluid\ViewHelpers\Format\DateViewHelper();
		$actualResult = $viewHelper->render(new \DateTime('1980-12-13'));
		$this->assertEquals('1980-12-13', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperFormatsDateStringCorrectly() {
		$viewHelper = new \TYPO3\Fluid\ViewHelpers\Format\DateViewHelper();
		$actualResult = $viewHelper->render('1980-12-13');
		$this->assertEquals('1980-12-13', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperRespectsCustomFormat() {
		$viewHelper = new \TYPO3\Fluid\ViewHelpers\Format\DateViewHelper();
		$actualResult = $viewHelper->render(new \DateTime('1980-02-01'), 'd.m.Y');
		$this->assertEquals('01.02.1980', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperReturnsEmptyStringIfNULLIsGiven() {
		$viewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Format\DateViewHelper', array('renderChildren'));
		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue(NULL));
		$actualResult = $viewHelper->render();
		$this->assertEquals('', $actualResult);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception
	 */
	public function viewHelperThrowsExceptionIfDateStringCantBeParsed() {
		$viewHelper = new \TYPO3\Fluid\ViewHelpers\Format\DateViewHelper();
		$viewHelper->render('foo');
	}

	/**
	 * @test
	 */
	public function viewHelperUsesChildNodesIfDateAttributeIsNotSpecified() {
		$viewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Format\DateViewHelper', array('renderChildren'));
		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue(new \DateTime('1980-12-13')));
		$actualResult = $viewHelper->render();
		$this->assertEquals('1980-12-13', $actualResult);
	}

	/**
	 * @test
	 */
	public function dateArgumentHasPriorityOverChildNodes() {
		$viewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Format\DateViewHelper', array('renderChildren'));
		$viewHelper->expects($this->never())->method('renderChildren');
		$actualResult = $viewHelper->render('1980-12-12');
		$this->assertEquals('1980-12-12', $actualResult);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception\InvalidVariableException
	 */
	public function viewHelperThrowsExceptionIfInvalidLocaleIdentifierIsGiven() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Format\DateViewHelper', array('renderChildren'));
		$viewHelper->render(new \DateTime(), NULL, '123-not-existing-locale');
	}

	/**
	 * @test
	 */
	public function viewHelperCallsDateTimeFormatterWithCorrectlyBuiltConfigurationArguments() {
		$dateTime = new \DateTime();
		$locale = new \TYPO3\Flow\I18n\Locale('de');
		$formatType = 'date';

		$formatterMock = $this->getMock('TYPO3\Flow\I18n\Formatter\DatetimeFormatter', array('format'));
		$formatterMock
			->expects($this->once())
			->method('format')
			->with($dateTime, $locale, array(0 => $formatType, 1 => NULL));

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Format\DateViewHelper', array('renderChildren'));
		$viewHelper->_set('formatter', $formatterMock);
		$viewHelper->render($dateTime, NULL, $locale, $formatType);
	}

	/**
	 * @test
	 */
	public function viewHelperFetchesCurrentLocaleViaI18nService() {
		$localizationConfiguration = new \TYPO3\Flow\I18n\Configuration('de_DE');

		$localizationServiceMock = $this->getMock('TYPO3\Flow\I18n\Service', array('getConfiguration'));
		$localizationServiceMock->expects($this->once())->method('getConfiguration')->will($this->returnValue($localizationConfiguration));

		$formatterMock = $this->getMock('TYPO3\Flow\I18n\Formatter\DatetimeFormatter', array('format'));
		$formatterMock->expects($this->once())->method('format');

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Format\DateViewHelper', array('renderChildren'));
		$viewHelper->_set('formatter', $formatterMock);
		$viewHelper->_set('localizationService', $localizationServiceMock);
		$viewHelper->render(new \DateTime(), NULL, TRUE);
	}

}
?>
