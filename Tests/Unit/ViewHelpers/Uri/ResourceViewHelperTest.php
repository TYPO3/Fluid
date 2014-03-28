<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers\Uri;

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
use TYPO3\Flow\Resource\Resource;

require_once(__DIR__ . '/../ViewHelperBaseTestcase.php');

/**
 * Test case for the resource uri view helper
 */
class ResourceViewHelperTest extends \TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * @var \TYPO3\Fluid\ViewHelpers\Uri\ResourceViewHelper
	 */
	protected $viewHelper;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mockI18nService;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mockResourceManager;

	public function setUp() {
		parent::setUp();
		$this->mockResourceManager = $this->getMock('TYPO3\Flow\Resource\ResourceManager');
		$this->mockI18nService = $this->getMock('TYPO3\Flow\I18n\Service');

		$this->viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Uri\ResourceViewHelper', array('renderChildren'), array(), '', FALSE);
		$this->inject($this->viewHelper, 'resourceManager', $this->mockResourceManager);
		$this->inject($this->viewHelper, 'i18nService', $this->mockI18nService);
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
		$this->viewHelper->initializeArguments();
	}

	/**
	 * @test
	 */
	public function renderUsesCurrentControllerPackageKeyToBuildTheResourceUri() {
		$this->mockResourceManager->expects($this->atLeastOnce())->method('getPublicPackageResourceUri')->with('ThePackageKey', 'Styles/Main.css')->will($this->returnValue('TheCorrectResourceUri'));
		$this->request->expects($this->atLeastOnce())->method('getControllerPackageKey')->will($this->returnValue('ThePackageKey'));
		$resourceUri = $this->viewHelper->render('Styles/Main.css', NULL, NULL, FALSE);
		$this->assertEquals('TheCorrectResourceUri', $resourceUri);
	}

	/**
	 * @test
	 */
	public function renderUsesCustomPackageKeyIfSpecified() {
		$this->mockResourceManager->expects($this->atLeastOnce())->method('getPublicPackageResourceUri')->with('ThePackageKey', 'Styles/Main.css')->will($this->returnValue('TheCorrectResourceUri'));
		$resourceUri = $this->viewHelper->render('Styles/Main.css', 'ThePackageKey', NULL, FALSE);
		$this->assertEquals('TheCorrectResourceUri', $resourceUri);
	}

	/**
	 * @test
	 */
	public function renderUsesProvidedResourceObjectInsteadOfPackageAndPath() {
		$resource = new Resource();
		$this->mockResourceManager->expects($this->atLeastOnce())->method('getPublicPersistentResourceUri')->with($resource)->will($this->returnValue('TheCorrectResourceUri'));
		$resourceUri = $this->viewHelper->render(NULL, NULL, $resource, FALSE);
		$this->assertEquals('TheCorrectResourceUri', $resourceUri);
	}

	/**
	 * @test
	 */
	public function renderCreatesASpecialBrokenResourceUriIfTheResourceCouldNotBePublished() {
		$resource = new Resource();
		$this->mockResourceManager->expects($this->atLeastOnce())->method('getPublicPersistentResourceUri')->with($resource)->will($this->returnValue(FALSE));
		$resourceUri = $this->viewHelper->render(NULL, NULL, $resource, FALSE);
		$this->assertEquals('404-Resource-Not-Found', $resourceUri);
	}

	/**
	 * @test
	 */
	public function renderLocalizesResource() {
		$this->mockI18nService->expects($this->once())->method('getLocalizedFilename')->with('resource://ThePackageKey/Public/Styles/Main.css')->will($this->returnValue(array('resource://ThePackageKey/Public/Styles/Main.css.de', new Locale('de'))));
		$this->mockResourceManager->expects($this->atLeastOnce())->method('getPublicPackageResourceUri')->with('ThePackageKey', 'Styles/Main.css.de')->will($this->returnValue('TheCorrectResourceUri'));
		$resourceUri = $this->viewHelper->render('Styles/Main.css', 'ThePackageKey');
		$this->assertEquals('TheCorrectResourceUri', $resourceUri);
	}

	/**
	 * @test
	 */
	public function renderLocalizesResourceGivenAsResourceUri() {
		$this->mockI18nService->expects($this->once())->method('getLocalizedFilename')->with('resource://ThePackageKey/Public/Styles/Main.css')->will($this->returnValue(array('resource://ThePackageKey/Public/Styles/Main.de.css', new Locale('de'))));
		$this->mockResourceManager->expects($this->atLeastOnce())->method('getPublicPackageResourceUri')->with('ThePackageKey', 'Styles/Main.de.css')->will($this->returnValue('TheCorrectResourceUri'));
		$resourceUri = $this->viewHelper->render('resource://ThePackageKey/Public/Styles/Main.css');
		$this->assertEquals('TheCorrectResourceUri', $resourceUri);
	}

	/**
	 * @test
	 */
	public function renderSkipsLocalizationIfRequested() {
		$this->mockI18nService->expects($this->never())->method('getLocalizedFilename');
		$this->viewHelper->render('foo', 'SomePackage', NULL, FALSE);
	}

	/**
	 * @test
	 */
	public function renderSkipsLocalizationForResourcesGivenAsResourceUriIfRequested() {
		$this->mockI18nService->expects($this->never())->method('getLocalizedFilename');
		$this->viewHelper->render('resource://SomePackage/Public/Images/foo.jpg', NULL, NULL, FALSE);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception\InvalidVariableException
	 */
	public function renderThrowsExceptionIfNeitherResourceNorPathWereGiven() {
		$this->viewHelper->render(NULL, 'SomePackage', NULL);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception\InvalidVariableException
	 */
	public function renderThrowsExceptionIfResourceUriNotPointingToPublicWasGivenAsPath() {
		$this->viewHelper->render('resource://Some.Package/Private/foobar.txt', 'SomePackage');
	}

}
