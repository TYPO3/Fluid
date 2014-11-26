<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\I18n\Locale;
use TYPO3\Flow\I18n\Translator;
use TYPO3\Fluid\ViewHelpers\TranslateViewHelper;
use TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase;

require_once(__DIR__ . '/ViewHelperBaseTestcase.php');

/**
 * Test case for the Translate ViewHelper
 */
class TranslateViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @var TranslateViewHelper
	 */
	protected $translateViewHelper;

	/**
	 * @var Locale
	 */
	protected $dummyLocale;

	/**
	 * @var Translator|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mockTranslator;

	public function setUp() {
		parent::setUp();

		$this->translateViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\TranslateViewHelper', array('renderChildren'));

		$this->request->expects($this->any())->method('getControllerPackageKey')->will($this->returnValue('TYPO3.Fluid'));

		$this->dummyLocale = new Locale('de_DE');

		$this->mockTranslator = $this->getMockBuilder('TYPO3\Flow\I18n\Translator')->disableOriginalConstructor()->getMock();
		$this->inject($this->translateViewHelper, 'translator', $this->mockTranslator);

		$this->injectDependenciesIntoViewHelper($this->translateViewHelper);
	}

	/**
	 * @test
	 */
	public function viewHelperTranslatesByOriginalLabel() {
		$this->mockTranslator->expects($this->once())->method('translateByOriginalLabel', 'Untranslated Label', 'Main', 'TYPO3.Flow', array(), NULL, $this->dummyLocale)->will($this->returnValue('Translated Label'));

		$this->translateViewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue('Untranslated Label'));

		$result = $this->translateViewHelper->render(NULL, NULL, array(), 'Main', NULL, NULL, 'de_DE');
		$this->assertEquals('Translated Label', $result);
	}

	/**
	 * @test
	 */
	public function viewHelperTranslatesById() {
		$this->mockTranslator->expects($this->once())->method('translateById', 'some.label', 'Main', 'TYPO3.Flow', array(), NULL, $this->dummyLocale)->will($this->returnValue('Translated Label'));

		$result = $this->translateViewHelper->render('some.label', NULL, array(), 'Main', NULL, NULL, 'de_DE');
		$this->assertEquals('Translated Label', $result);
	}

	/**
	 * @test
	 */
	public function viewHelperUsesValueIfIdIsNotFound() {
		$this->mockTranslator->expects($this->once())->method('translateById', 'some.label', 'Main', 'TYPO3.Flow', array(), NULL, $this->dummyLocale)->will($this->returnValue('some.label'));
		$this->mockTranslator->expects($this->once())->method('translateByOriginalLabel', 'Default from value', 'Main', 'TYPO3.Flow', array(), NULL, $this->dummyLocale)->will($this->returnValue('Default from value'));

		$this->translateViewHelper->expects($this->never())->method('renderChildren');

		$result = $this->translateViewHelper->render('some.label', 'Default from value', array(), 'Main', NULL, NULL, 'de_DE');
		$this->assertEquals('Default from value', $result);
	}

	/**
	 * @test
	 */
	public function viewHelperUsesRenderChildrenIfIdIsNotFound() {
		$this->mockTranslator->expects($this->once())->method('translateById', 'some.label', 'Main', 'TYPO3.Flow', array(), NULL, $this->dummyLocale)->will($this->returnValue('some.label'));
		$this->mockTranslator->expects($this->once())->method('translateByOriginalLabel', 'Default from renderChildren', 'Main', 'TYPO3.Flow', array(), NULL, $this->dummyLocale)->will($this->returnValue('Default from renderChildren'));

		$this->translateViewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue('Default from renderChildren'));

		$result = $this->translateViewHelper->render('some.label', NULL, array(), 'Main', NULL, NULL, 'de_DE');
		$this->assertEquals('Default from renderChildren', $result);
	}

	/**
	 * @test
	 */
	public function viewHelperReturnsIdWhenRenderChildrenReturnsEmptyResultIfIdIsNotFound() {
		$this->mockTranslator->expects($this->once())->method('translateById', 'some.label', 'Main', 'TYPO3.Flow', array(), NULL, $this->dummyLocale)->will($this->returnValue('some.label'));

		$this->translateViewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue(NULL));

		$result = $this->translateViewHelper->render('some.label', NULL, array(), 'Main', NULL, NULL, 'de_DE');
		$this->assertEquals('some.label', $result);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception
	 */
	public function renderThrowsExceptionIfGivenLocaleIdentifierIsInvalid() {
		 $this->translateViewHelper->render('some.label', NULL, array(), 'Main', NULL, NULL, 'INVALIDLOCALE');
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception
	 */
	public function renderThrowsExceptionIfNoPackageCouldBeResolved() {
		$mockRequest = $this->getMockBuilder('TYPO3\Flow\Mvc\ActionRequest')->disableOriginalConstructor()->getMock();
		$mockRequest->expects($this->any())->method('getControllerPackageKey')->will($this->returnValue(NULL));

		$mockControllerContext = $this->getMockBuilder('TYPO3\Flow\Mvc\Controller\ControllerContext')->disableOriginalConstructor()->getMock();
		$mockControllerContext->expects($this->any())->method('getRequest')->will($this->returnValue($mockRequest));

		$this->renderingContext->setControllerContext($mockControllerContext);

		$this->injectDependenciesIntoViewHelper($this->translateViewHelper);

		$this->translateViewHelper->render('some.label');
	}
}
