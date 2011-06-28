<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers\Uri;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

require_once(__DIR__ . '/../ViewHelperBaseTestcase.php');

/**
 * Testcase for the resource uri view helper
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
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
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function renderUsesCurrentControllerPackageKeyToBuildTheResourceUri() {
		$this->mockResourcePublisher->expects($this->atLeastOnce())->method('getStaticResourcesWebBaseUri')->will($this->returnValue('Resources/'));
		$this->request->expects($this->atLeastOnce())->method('getControllerPackageKey')->will($this->returnValue('PackageKey'));

		$resourceUri = $this->viewHelper->render('foo');
		$this->assertEquals('Resources/Packages/PackageKey/foo', $resourceUri);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderUsesCustomPackageKeyIfSpecified() {
		$this->mockResourcePublisher->expects($this->atLeastOnce())->method('getStaticResourcesWebBaseUri')->will($this->returnValue('Resources/'));
		$resourceUri = $this->viewHelper->render('foo', 'SomePackage');
		$this->assertEquals('Resources/Packages/SomePackage/foo', $resourceUri);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function renderUsesStaticResourcesBaseUri() {
		$this->mockResourcePublisher->expects($this->atLeastOnce())->method('getStaticResourcesWebBaseUri')->will($this->returnValue('CustomDirectory/'));
		$resourceUri = $this->viewHelper->render('foo', 'SomePackage');
		$this->assertEquals('CustomDirectory/Packages/SomePackage/foo', $resourceUri);
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function renderUsesProvidedResourceObjectInsteadOfPackageAndPath() {
		$mockResource = $this->getMock('TYPO3\FLOW3\Resource\Resource', array(), array(), '', FALSE);

		$this->mockResourcePublisher->expects($this->once())->method('getPersistentResourceWebUri')->with($mockResource)->will($this->returnValue('http://foo/Resources/Persistent/ac9b6187f4c55b461d69e22a57925ff61ee89cb2.jpg'));

		$resourceUri = $this->viewHelper->render(NULL, NULL, $mockResource);
		$this->assertEquals('http://foo/Resources/Persistent/ac9b6187f4c55b461d69e22a57925ff61ee89cb2.jpg', $resourceUri);
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
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