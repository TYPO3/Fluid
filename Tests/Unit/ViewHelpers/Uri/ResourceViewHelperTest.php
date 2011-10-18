<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers\Uri;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
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
	 * var \TYPO3\Fluid\ViewHelpers\Uri\ResourceViewHelper
	 */
	protected $viewHelper;

	public function setUp() {
		parent::setUp();
		$this->mockResourcePublisher = $this->getMock('TYPO3\FLOW3\Resource\Publishing\ResourcePublisher');
		$this->viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Uri\ResourceViewHelper', array('renderChildren'), array(), '', FALSE);
		$this->viewHelper->injectResourcePublisher($this->mockResourcePublisher);
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
		$this->viewHelper->initializeArguments();
	}

	/**
	 * @test
	 */
	public function renderUsesCurrentControllerPackageKeyToBuildTheResourceUri() {
		$this->mockResourcePublisher->expects($this->atLeastOnce())->method('getStaticResourcesWebBaseUri')->will($this->returnValue('Resources/'));
		$this->request->expects($this->atLeastOnce())->method('getControllerPackageKey')->will($this->returnValue('PackageKey'));

		$resourceUri = $this->viewHelper->render('foo');
		$this->assertEquals('Resources/Packages/PackageKey/foo', $resourceUri);
	}

	/**
	 * @test
	 */
	public function renderUsesCustomPackageKeyIfSpecified() {
		$this->mockResourcePublisher->expects($this->atLeastOnce())->method('getStaticResourcesWebBaseUri')->will($this->returnValue('Resources/'));
		$resourceUri = $this->viewHelper->render('foo', 'SomePackage');
		$this->assertEquals('Resources/Packages/SomePackage/foo', $resourceUri);
	}

	/**
	 * @test
	 */
	public function renderUsesStaticResourcesBaseUri() {
		$this->mockResourcePublisher->expects($this->atLeastOnce())->method('getStaticResourcesWebBaseUri')->will($this->returnValue('CustomDirectory/'));
		$resourceUri = $this->viewHelper->render('foo', 'SomePackage');
		$this->assertEquals('CustomDirectory/Packages/SomePackage/foo', $resourceUri);
	}

	/**
	 * @test
	 */
	public function renderUsesProvidedResourceObjectInsteadOfPackageAndPath() {
		$mockResource = $this->getMock('TYPO3\FLOW3\Resource\Resource', array(), array(), '', FALSE);

		$this->mockResourcePublisher->expects($this->once())->method('getPersistentResourceWebUri')->with($mockResource)->will($this->returnValue('http://foo/Resources/Persistent/ac9b6187f4c55b461d69e22a57925ff61ee89cb2.jpg'));

		$resourceUri = $this->viewHelper->render(NULL, NULL, $mockResource);
		$this->assertEquals('http://foo/Resources/Persistent/ac9b6187f4c55b461d69e22a57925ff61ee89cb2.jpg', $resourceUri);
	}

	/**
	 * @test
	 */
	public function renderCreatesASpecialBrokenResourceUriIfTheResourceCouldNotBePublished() {
		$mockResource = $this->getMock('TYPO3\FLOW3\Resource\Resource', array(), array(), '', FALSE);

		$this->mockResourcePublisher->expects($this->once())->method('getPersistentResourceWebUri')->with($mockResource)->will($this->returnValue(FALSE));
		$this->mockResourcePublisher->expects($this->atLeastOnce())->method('getStaticResourcesWebBaseUri')->will($this->returnValue('http://foo/MyOwnResources/'));

		$resourceUri = $this->viewHelper->render(NULL, NULL, $mockResource);
		$this->assertEquals('http://foo/MyOwnResources/BrokenResource', $resourceUri);
	}
}

?>