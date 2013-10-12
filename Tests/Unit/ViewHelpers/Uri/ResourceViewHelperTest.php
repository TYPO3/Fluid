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

require_once(__DIR__ . '/../ViewHelperBaseTestcase.php');

/**
 * Testcase for the resource uri view helper
 *
 */
class ResourceViewHelperTest extends \TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * @var \TYPO3\Fluid\ViewHelpers\Uri\ResourceViewHelper
	 */
	protected $viewHelper;

	public function setUp() {
		parent::setUp();
		$this->mockResourcePublisher = $this->getMock('TYPO3\Flow\Resource\Publishing\ResourcePublisher');
		$this->mockI18nService = $this->getMock('TYPO3\Flow\I18n\Service');
		$this->viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Uri\ResourceViewHelper', array('renderChildren'), array(), '', FALSE);
		$this->inject($this->viewHelper, 'resourcePublisher', $this->mockResourcePublisher);
		$this->inject($this->viewHelper, 'i18nService', $this->mockI18nService);
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
		$this->viewHelper->initializeArguments();
	}

	/**
	 * @test
	 */
	public function renderUsesCurrentControllerPackageKeyToBuildTheResourceUri() {
		$this->mockResourcePublisher->expects($this->atLeastOnce())->method('getStaticResourcesWebBaseUri')->will($this->returnValue('Resources/'));
		$this->mockI18nService->expects($this->atLeastOnce())->method('getLocalizedFilename')->will($this->returnValue(array('foo')));
		$this->request->expects($this->atLeastOnce())->method('getControllerPackageKey')->will($this->returnValue('PackageKey'));

		$resourceUri = $this->viewHelper->render('foo');
		$this->assertEquals('Resources/Packages/PackageKey/foo', $resourceUri);
	}

	/**
	 * @test
	 */
	public function renderUsesCustomPackageKeyIfSpecified() {
		$this->mockResourcePublisher->expects($this->atLeastOnce())->method('getStaticResourcesWebBaseUri')->will($this->returnValue('Resources/'));
		$this->mockI18nService->expects($this->atLeastOnce())->method('getLocalizedFilename')->will($this->returnValue(array('foo')));
		$resourceUri = $this->viewHelper->render('foo', 'SomePackage');
		$this->assertEquals('Resources/Packages/SomePackage/foo', $resourceUri);
	}

	/**
	 * @test
	 */
	public function renderUsesStaticResourcesBaseUri() {
		$this->mockResourcePublisher->expects($this->atLeastOnce())->method('getStaticResourcesWebBaseUri')->will($this->returnValue('CustomDirectory/'));
		$this->mockI18nService->expects($this->atLeastOnce())->method('getLocalizedFilename')->will($this->returnValue(array('foo')));
		$resourceUri = $this->viewHelper->render('foo', 'SomePackage');
		$this->assertEquals('CustomDirectory/Packages/SomePackage/foo', $resourceUri);
	}

	/**
	 * @test
	 */
	public function renderUsesProvidedResourceObjectInsteadOfPackageAndPath() {
		$mockResource = $this->getMock('TYPO3\Flow\Resource\Resource', array(), array(), '', FALSE);

		$this->mockResourcePublisher->expects($this->once())->method('getPersistentResourceWebUri')->with($mockResource)->will($this->returnValue('http://foo/Resources/Persistent/ac9b6187f4c55b461d69e22a57925ff61ee89cb2.jpg'));

		$resourceUri = $this->viewHelper->render(NULL, NULL, $mockResource);
		$this->assertEquals('http://foo/Resources/Persistent/ac9b6187f4c55b461d69e22a57925ff61ee89cb2.jpg', $resourceUri);
	}

	/**
	 * @test
	 */
	public function renderCreatesASpecialBrokenResourceUriIfTheResourceCouldNotBePublished() {
		$mockResource = $this->getMock('TYPO3\Flow\Resource\Resource', array(), array(), '', FALSE);

		$this->mockResourcePublisher->expects($this->once())->method('getPersistentResourceWebUri')->with($mockResource)->will($this->returnValue(FALSE));
		$this->mockResourcePublisher->expects($this->atLeastOnce())->method('getStaticResourcesWebBaseUri')->will($this->returnValue('http://foo/MyOwnResources/'));

		$resourceUri = $this->viewHelper->render(NULL, NULL, $mockResource);
		$this->assertEquals('http://foo/MyOwnResources/BrokenResource', $resourceUri);
	}

	/**
	 * @test
	 */
	public function renderLocalizesResource() {
		$this->mockResourcePublisher->expects($this->atLeastOnce())->method('getStaticResourcesWebBaseUri')->will($this->returnValue('CustomDirectory/'));
		$this->mockI18nService->expects($this->once())->method('getLocalizedFilename')->with('resource://SomePackage/Public/foo')->will($this->returnValue(array('resource://SomePackage/Public/foo.de', new \TYPO3\Flow\I18n\Locale('de'))));
		$resourceUri = $this->viewHelper->render('foo', 'SomePackage');
		$this->assertEquals('CustomDirectory/Packages/SomePackage/foo.de', $resourceUri);
	}

	/**
	 * @test
	 */
	public function renderLocalizesResourceGivenAsResourceUri() {
		$this->mockResourcePublisher->expects($this->atLeastOnce())->method('getStaticResourcesWebBaseUri')->will($this->returnValue('CustomDirectory/'));
		$this->mockI18nService
			->expects($this->once())
			->method('getLocalizedFilename')
			->with('resource://SomePackage/Public/Images/foo.jpg')
			->will($this->returnValue(array('resource://SomePackage/Public/Images/foo.de.jpg', new \TYPO3\Flow\I18n\Locale('de'))));

		$resourceUri = $this->viewHelper->render('resource://SomePackage/Public/Images/foo.jpg');
		$this->assertEquals('CustomDirectory/Packages/SomePackage/Images/foo.de.jpg', $resourceUri);
	}

	/**
	 * @test
	 */
	public function renderSkipsLocalizationIfRequested() {
		$this->mockResourcePublisher->expects($this->atLeastOnce())->method('getStaticResourcesWebBaseUri')->will($this->returnValue('CustomDirectory/'));
		$this->mockI18nService->expects($this->never())->method('getLocalizedFilename');
		$resourceUri = $this->viewHelper->render('foo', 'SomePackage', NULL, FALSE);
		$this->assertEquals('CustomDirectory/Packages/SomePackage/foo', $resourceUri);
	}

	/**
	 * @test
	 */
	public function renderSkipsLocalizationForResourcesGivenAsResourceUriIfRequested() {
		$this->mockResourcePublisher->expects($this->atLeastOnce())->method('getStaticResourcesWebBaseUri')->will($this->returnValue('CustomDirectory/'));
		$this->mockI18nService->expects($this->never())->method('getLocalizedFilename');

		$resourceUri = $this->viewHelper->render('resource://SomePackage/Public/Images/foo.jpg', NULL, NULL, FALSE);
		$this->assertEquals('CustomDirectory/Packages/SomePackage/Images/foo.jpg', $resourceUri);
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
