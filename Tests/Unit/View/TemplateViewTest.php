<?php
namespace TYPO3\Fluid\Tests\Unit\View;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

include_once(__DIR__ . '/Fixtures/TransparentSyntaxTreeNode.php');
include_once(__DIR__ . '/Fixtures/TemplateViewFixture.php');

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * Testcase for the TemplateView
 *
 */
class TemplateViewTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function expandGenericPathPatternWorksWithBubblingDisabledAndFormatNotOptional() {
		$mockControllerContext = $this->setupMockControllerContextForPathResolving('MyPackage', NULL, 'My', 'html');

		$templateView = $this->getAccessibleMock('TYPO3\Fluid\View\TemplateView', array('getTemplateRootPath', 'getPartialRootPath', 'getLayoutRootPath'), array(), '', FALSE);
		$templateView->_set('controllerContext', $mockControllerContext);
		$templateView->expects($this->any())->method('getTemplateRootPath')->will($this->returnValue('Resources/Private/'));

		$expected = array('Resources/Private/Templates/My/@action.html');
		$actual = $templateView->_call('expandGenericPathPattern', '@templateRoot/Templates/@subpackage/@controller/@action.@format', FALSE, FALSE);
		$this->assertEquals($expected, $actual);
	}


	/**
	 * @test
	 */
	public function expandGenericPathPatternWorksWithSubpackageAndBubblingDisabledAndFormatNotOptional() {
		$mockControllerContext = $this->setupMockControllerContextForPathResolving('MyPackage', 'MySubPackage', 'My', 'html');

		$templateView = $this->getAccessibleMock('TYPO3\Fluid\View\TemplateView', array('getTemplateRootPath', 'getPartialRootPath', 'getLayoutRootPath'), array(), '', FALSE);
		$templateView->_set('controllerContext', $mockControllerContext);
		$templateView->expects($this->any())->method('getTemplateRootPath')->will($this->returnValue('Resources/Private/'));
		$actual = $templateView->_call('expandGenericPathPattern', '@templateRoot/Templates/@subpackage/@controller/@action.@format', FALSE, FALSE);

		$expected = array(
			'Resources/Private/Templates/MySubPackage/My/@action.html'
		);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @test
	 */
	public function expandGenericPathPatternWorksWithSubpackageAndBubblingDisabledAndFormatOptional() {
		$mockControllerContext = $this->setupMockControllerContextForPathResolving('MyPackage', 'MySubPackage', 'My', 'html');

		$templateView = $this->getAccessibleMock('TYPO3\Fluid\View\TemplateView', array('getTemplateRootPath', 'getPartialRootPath', 'getLayoutRootPath'), array(), '', FALSE);
		$templateView->_set('controllerContext', $mockControllerContext);
		$templateView->expects($this->any())->method('getTemplateRootPath')->will($this->returnValue('Resources/Private/'));
		$actual = $templateView->_call('expandGenericPathPattern', '@templateRoot/Templates/@subpackage/@controller/@action.@format', FALSE, TRUE);

		$expected = array(
			'Resources/Private/Templates/MySubPackage/My/@action.html',
			'Resources/Private/Templates/MySubPackage/My/@action'
		);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @test
	 */
	public function expandGenericPathPatternWorksWithSubpackageAndBubblingEnabledAndFormatOptional() {
		$mockControllerContext = $this->setupMockControllerContextForPathResolving('MyPackage', 'MySubPackage', 'My', 'html');

		$templateView = $this->getAccessibleMock('TYPO3\Fluid\View\TemplateView', array('getTemplateRootPath', 'getPartialRootPath', 'getLayoutRootPath'), array(), '', FALSE);
		$templateView->_set('controllerContext', $mockControllerContext);
		$templateView->expects($this->any())->method('getTemplateRootPath')->will($this->returnValue('Resources/Private/'));
		$actual = $templateView->_call('expandGenericPathPattern', '@templateRoot/Templates/@subpackage/@controller/@action.@format', TRUE, TRUE);

		$expected = array(
			'Resources/Private/Templates/MySubPackage/My/@action.html',
			'Resources/Private/Templates/MySubPackage/My/@action',
			'Resources/Private/Templates/MySubPackage/@action.html',
			'Resources/Private/Templates/MySubPackage/@action',
			'Resources/Private/Templates/@action.html',
			'Resources/Private/Templates/@action',
		);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * Helper to build mock controller context needed to test expandGenericPathPattern.
	 *
	 * @param $packageKey
	 * @param $subPackageKey
	 * @param $controllerClassName
	 * @param $format
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	protected function setupMockControllerContextForPathResolving($packageKey, $subPackageKey, $controllerName, $format) {
		$controllerObjectName = "TYPO3\\$packageKey\\" . ($subPackageKey != $subPackageKey . '\\' ? : '') . 'Controller\\' . $controllerName . 'Controller';

		$httpRequest = \TYPO3\Flow\Http\Request::create(new \TYPO3\Flow\Http\Uri('http://robertlemke.com/blog'));
		$mockRequest = $this->getMock('TYPO3\Flow\Mvc\ActionRequest', array(), array($httpRequest));
		$mockRequest->expects($this->any())->method('getControllerPackageKey')->will($this->returnValue($packageKey));
		$mockRequest->expects($this->any())->method('getControllerSubPackageKey')->will($this->returnValue($subPackageKey));
		$mockRequest->expects($this->any())->method('getControllerName')->will($this->returnValue($controllerName));
		$mockRequest->expects($this->any())->method('getControllerObjectName')->will($this->returnValue($controllerObjectName));
		$mockRequest->expects($this->any())->method('getFormat')->will($this->returnValue($format));

		$mockControllerContext = $this->getMock('TYPO3\Flow\Mvc\Controller\ControllerContext', array('getRequest'), array(), '', FALSE);
		$mockControllerContext->expects($this->any())->method('getRequest')->will($this->returnValue($mockRequest));

		return $mockControllerContext;
	}

	/**
	 * @test
	 */
	public function getTemplateRootPathReturnsUserSpecifiedTemplatePath() {
		$templateView = $this->getAccessibleMock('TYPO3\Fluid\View\TemplateView', array('dummy'), array(), '', FALSE);
		$templateView->setTemplateRootPath('/foo/bar');
		$expected = '/foo/bar';
		$actual = $templateView->_call('getTemplateRootPath');
		$this->assertEquals($expected, $actual, 'A set template root path was not returned correctly.');
	}

	/**
	 * @test
	 */
	public function getPartialRootPathReturnsUserSpecifiedPartialPath() {
		$templateView = $this->getAccessibleMock('TYPO3\Fluid\View\TemplateView', array('dummy'), array(), '', FALSE);
		$templateView->setPartialRootPath('/foo/bar');
		$expected = '/foo/bar';
		$actual = $templateView->_call('getPartialRootPath');
		$this->assertEquals($expected, $actual, 'A set partial root path was not returned correctly.');
	}

	/**
	 * @test
	 */
	public function getLayoutRootPathReturnsUserSpecifiedPartialPath() {
		$templateView = $this->getAccessibleMock('TYPO3\Fluid\View\TemplateView', array('dummy'), array(), '', FALSE);
		$templateView->setLayoutRootPath('/foo/bar');
		$expected = '/foo/bar';
		$actual = $templateView->_call('getLayoutRootPath');
		$this->assertEquals($expected, $actual, 'A set partial root path was not returned correctly.');
	}

	/**
	 * @test
	 */
	public function pathToPartialIsResolvedCorrectly() {
		$this->markTestSkipped('Needs to be finished');
		vfsStreamWrapper::register();
		mkdir('vfs://MyTemplates');
		\file_put_contents('vfs://MyTemplates/MyCoolAction.html', 'contentsOfMyCoolAction');
		$mockRootDirectory = vfsStreamDirectory::create('ExamplePackagePath/Resources/Private/Partials');
		$mockRootDirectory->getChild('Resources/Private/Partials')->addChild('Partials');
		vfsStreamWrapper::setRoot($mockRootDirectory);

		$this->getAccessibleMock('TYPO3\Fluid\Core\Parser\TemplateParser', array(''), array(), '', FALSE);
	}

	/**
	 * @test
	 */
	public function resolveTemplatePathAndFilenameChecksDifferentPathPatternsAndReturnsTheFirstPathWhichExists() {
		vfsStreamWrapper::register();
		mkdir('vfs://MyTemplates');
		\file_put_contents('vfs://MyTemplates/MyCoolAction.html', 'contentsOfMyCoolAction');

		$paths = array(
			 'vfs://NonExistantDir/UnknowFile.html',
			 'vfs://MyTemplates/@action.html'
		);

		$templateView = $this->getAccessibleMock('TYPO3\Fluid\View\TemplateView', array('expandGenericPathPattern'), array(), '', FALSE);
		$templateView->expects($this->once())->method('expandGenericPathPattern')->with('@templateRoot/@subpackage/@controller/@action.@format', FALSE, FALSE)->will($this->returnValue($paths));

		$templateView->setTemplateRootPath('MyTemplates');
		$templateView->setPartialRootPath('MyPartials');
		$templateView->setLayoutRootPath('MyLayouts');

		$this->assertSame('contentsOfMyCoolAction', $templateView->_call('getTemplateSource', 'myCoolAction'));

	}

	/**
	 * @test
	 */
	public function resolveTemplatePathAndFilenameReturnsTheExplicitlyConfiguredTemplatePathAndFilename() {
		vfsStreamWrapper::register();
		mkdir('vfs://MyTemplates');
		\file_put_contents('vfs://MyTemplates/MyCoolAction.html', 'contentsOfMyCoolAction');

		$templateView = $this->getAccessibleMock('TYPO3\Fluid\View\TemplateView', array('dummy'), array(), '', FALSE);
		$templateView->_set('templatePathAndFilename', 'vfs://MyTemplates/MyCoolAction.html');

		$this->assertSame('contentsOfMyCoolAction', $templateView->_call('getTemplateSource'));
	}
}

?>