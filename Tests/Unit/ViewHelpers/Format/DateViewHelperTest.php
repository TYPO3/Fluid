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
 * Test for date view helper \TYPO3\Fluid\ViewHelpers\Format\DateViewHelper
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
		$viewHelper->setArguments(array('forceLocale' => '123-not-existing-locale'));
		$viewHelper->render(new \DateTime());
	}

	/**
	 * @test
	 */
	public function viewHelperCallsDateTimeFormatterWithCorrectlyBuiltConfigurationArguments() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Format\DateViewHelper', array('renderChildren'));

		$dateTime = new \DateTime();
		$locale = new \TYPO3\Flow\I18n\Locale('de');
		$formatType = 'date';

		$mockDatetimeFormatter = $this->getMock('TYPO3\Flow\I18n\Formatter\DatetimeFormatter', array('format'));
		$mockDatetimeFormatter
			->expects($this->once())
			->method('format')
			->with($dateTime, $locale, array(0 => $formatType, 1 => NULL));
		$this->inject($viewHelper, 'datetimeFormatter', $mockDatetimeFormatter);

		$viewHelper->setArguments(array('forceLocale' => $locale));
		$viewHelper->render($dateTime, NULL, $formatType);
	}

	/**
	 * @test
	 */
	public function viewHelperFetchesCurrentLocaleViaI18nService() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Format\DateViewHelper', array('renderChildren'));

		$localizationConfiguration = new \TYPO3\Flow\I18n\Configuration('de_DE');

		$mockLocalizationService = $this->getMock('TYPO3\Flow\I18n\Service', array('getConfiguration'));
		$mockLocalizationService->expects($this->once())->method('getConfiguration')->will($this->returnValue($localizationConfiguration));
		$this->inject($viewHelper, 'localizationService', $mockLocalizationService);

		$mockDatetimeFormatter = $this->getMock('TYPO3\Flow\I18n\Formatter\DatetimeFormatter', array('format'));
		$mockDatetimeFormatter->expects($this->once())->method('format');
		$this->inject($viewHelper, 'datetimeFormatter', $mockDatetimeFormatter);

		$viewHelper->setArguments(array('forceLocale' => TRUE));
		$viewHelper->render(new \DateTime());
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception
	 */
	public function viewHelperConvertsI18nExceptionsIntoViewHelperExceptions() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Format\DateViewHelper', array('renderChildren'));

		$localizationConfiguration = new \TYPO3\Flow\I18n\Configuration('de_DE');

		$mockLocalizationService = $this->getMock('TYPO3\Flow\I18n\Service', array('getConfiguration'));
		$mockLocalizationService->expects($this->once())->method('getConfiguration')->will($this->returnValue($localizationConfiguration));
		$this->inject($viewHelper, 'localizationService', $mockLocalizationService);

		$mockDatetimeFormatter = $this->getMock('TYPO3\Flow\I18n\Formatter\DatetimeFormatter', array('format'));
		$mockDatetimeFormatter->expects($this->once())->method('format')->will($this->throwException(new \TYPO3\Flow\I18n\Exception()));
		$this->inject($viewHelper, 'datetimeFormatter', $mockDatetimeFormatter);

		$viewHelper->setArguments(array('forceLocale' => TRUE));
		$viewHelper->render(new \DateTime());
	}
}
