<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\View;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 * @version $Id$
 */
/**
 * Testcase for the TemplateView
 *
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

include_once(__DIR__ . '/Fixtures/TransparentSyntaxTreeNode.php');
include_once(__DIR__ . '/Fixtures/TemplateViewFixture.php');

class TemplateViewTest extends \F3\Testing\BaseTestCase {



	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function expandGenericPathPatternWorksWithBubblingDisabledAndFormatNotOptional() {
		$mockControllerContext = $this->setupMockControllerContextForPathResolving('F3\MyPackage\Controller\MyController', 'html');

		$templateView = $this->getMock($this->buildAccessibleProxy('F3\Fluid\View\TemplateView'), array('getTemplateRootPath'), array(), '', FALSE);
		$templateView->_set('controllerContext', $mockControllerContext);
		$templateView->expects($this->any())->method('getTemplateRootPath')->will($this->returnValue('Resources/Private/'));
		$actual = $templateView->_call('expandGenericPathPattern', '@templateRoot/Templates/@subpackage/@controller/@action.@format', FALSE, FALSE);

		$expected = array(
			'Resources/Private/Templates/My/@action.html'
		);
		$this->assertEquals($expected, $actual);
	}


	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function expandGenericPathPatternWorksWithSubpackageAndBubblingDisabledAndFormatNotOptional() {
		$mockControllerContext = $this->setupMockControllerContextForPathResolving('F3\MyPackage\MySubPackage\Controller\MyController', 'html');

		$templateView = $this->getMock($this->buildAccessibleProxy('F3\Fluid\View\TemplateView'), array('getTemplateRootPath'), array(), '', FALSE);
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
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function expandGenericPathPatternWorksWithSubpackageAndBubblingDisabledAndFormatOptional() {
		$mockControllerContext = $this->setupMockControllerContextForPathResolving('F3\MyPackage\MySubPackage\Controller\MyController', 'html');

		$templateView = $this->getMock($this->buildAccessibleProxy('F3\Fluid\View\TemplateView'), array('getTemplateRootPath'), array(), '', FALSE);
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
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function expandGenericPathPatternWorksWithSubpackageAndBubblingEnabledAndFormatOptional() {
		$mockControllerContext = $this->setupMockControllerContextForPathResolving('F3\MyPackage\MySubPackage\Controller\MyController', 'html');

		$templateView = $this->getMock($this->buildAccessibleProxy('F3\Fluid\View\TemplateView'), array('getTemplateRootPath'), array(), '', FALSE);
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
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function expandGenericPathPatternWorksWithNoControllerAndSubpackageAndBubblingEnabledAndFormatOptional() {
		$mockControllerContext = $this->setupMockControllerContextForPathResolving('F3\MyPackage\MySubPackage\Controller\MyController', 'html');

		$templateView = $this->getMock($this->buildAccessibleProxy('F3\Fluid\View\TemplateView'), array('getTemplateRootPath'), array(), '', FALSE);
		$templateView->_set('controllerContext', $mockControllerContext);
		$templateView->expects($this->any())->method('getTemplateRootPath')->will($this->returnValue('Resources/Private/'));
		$actual = $templateView->_call('expandGenericPathPattern', '@templateRoot/Templates/@subpackage/@action.@format', TRUE, TRUE);

		$expected = array(
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
	 * @param $controllerObjectName
	 * @param $action
	 * @param $format
	 *
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function setupMockControllerContextForPathResolving($controllerObjectName, $format) {
		$mockRequest = $this->getMock('F3\FLOW3\MVC\RequestInterface');
		$mockRequest->expects($this->any())->method('getControllerObjectName')->will($this->returnValue($controllerObjectName));
		$mockRequest->expects($this->any())->method('getFormat')->will($this->returnValue($format));

		$mockControllerContext = $this->getMock('F3\FLOW3\MVC\Controller\ControllerContext', array('getRequest'));
		$mockControllerContext->expects($this->any())->method('getRequest')->will($this->returnValue($mockRequest));

		return $mockControllerContext;
	}
	
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getTemplateRootPathReturnsUserSpecifiedTemplatePath() {
		$templateView = $this->getMock($this->buildAccessibleProxy('F3\Fluid\View\TemplateView'), array('dummy'), array(), '', FALSE);
		$templateView->setTemplateRootPath('/foo/bar');
		$expected = '/foo/bar';
		$actual = $templateView->_call('getTemplateRootPath');
		$this->assertEquals($expected, $actual, 'A set template root path was not returned correctly.');
	}

	/**
	 * test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function renderCallsRenderOnParsedTemplateInterface() {
		$templateView = $this->getMock($this->buildAccessibleProxy('F3\Fluid\View\TemplateView'), array('parseTemplate', 'resolveTemplatePathAndFilename'), array(), '', FALSE);
		$parsedTemplate = $this->getMock('F3\Fluid\Core\Parser\ParsedTemplateInterface');
		$objectFactory = $this->getMock('F3\FLOW3\Object\FactoryInterface');
		$controllerContext = $this->getMock('F3\FLOW3\MVC\Controller\ControllerContext');

		$variableContainer = $this->getMock('F3\Fluid\Core\ViewHelper\TemplateVariableContainer');
		$renderingContext = $this->getMock('F3\Fluid\Core\Rendering\RenderingContext', array(), array(), '', FALSE);

		$renderingConfiguration = $this->getMock('F3\Fluid\Core\Rendering\RenderingConfiguration');

		$objectAccessorPostProcessor = $this->getMock('F3\Fluid\Core\Rendering\HTMLSpecialCharsPostProcessor');
		$viewHelperVariableContainer = $this->getMock('F3\Fluid\Core\ViewHelper\ViewHelperVariableContainer');
		$objectFactory->expects($this->exactly(5))->method('create')->will($this->onConsecutiveCalls($variableContainer, $renderingConfiguration, $objectAccessorPostProcessor, $renderingContext, $viewHelperVariableContainer));

		$templateView->_set('objectFactory', $objectFactory);
		$templateView->setControllerContext($controllerContext);

		$templateView->expects($this->once())->method('parseTemplate')->will($this->returnValue($parsedTemplate));

		// Real expectations
		$parsedTemplate->expects($this->once())->method('render')->with($renderingContext)->will($this->returnValue('Hello World'));

		$this->assertEquals('Hello World', $templateView->render(), 'The output of the ParsedTemplates render Method is not returned by the TemplateView');

	}


	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function pathToPartialIsResolvedCorrectly() {
	/*	$mockRequest = $this->getMock('F3\FLOW3\MVC\Request', array('getControllerPackageKey', ''));
		$mockRequest->expects($this->any())->method('getControllerPackageKey')->will($this->returnValue('DummyPackageKey'));
		$mockControllerContext = $this->getMock('F3\FLOW3\MVC\Controller\ControllerContext', array('getRequest'));
		$mockControllerContext->expects($this->any())->method('getRequest')->will($this->returnValue($mockRequest));

		$mockPackage = $this->getMock('F3\FLOW3\Package\PackageInterface', array('getPackagePath'));
		$mockPackage->expects($this->any())->method('getPackagePath')->will($this->returnValue('/ExamplePackagePath/'));
		$mockPackageManager = $this->getMock('F3\FLOW3\Package\ManagerInterface', array('getPackage'));
		$mockPackageManager->expects($this->any())->method('getPackage')->with('DummyPackageKey')->will($this->returnValue($mockPackage));

		\vfsStreamWrapper::register();
		$mockRootDirectory = vfsStreamDirectory::create('ExamplePackagePath/Resources/Private/Partials');
		$mockRootDirectory->getChild('Resources/Private/Partials')->addChild('Partials')
		\vfsStreamWrapper::setRoot($mockRootDirectory);

		$this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\Parser\TemplateParser'), array(''), array(), '', FALSE);*/
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function viewIsPlacedInVariableContainer() {
		$this->markTestSkipped('view will be placed in ViewHelperContext soon');
		$packageManager = $this->objectManager->getObject('F3\FLOW3\Package\ManagerInterface');
		$resourceManager = $this->objectManager->getObject('F3\FLOW3\Resource\Manager');

		$syntaxTreeNode = new \F3\Fluid\View\Fixture\TransparentSyntaxTreeNode();

		$parsingState = new \F3\Fluid\Core\Parser\ParsingState();
		$parsingState->setRootNode($syntaxTreeNode);

		$templateParserMock = $this->getMock('F3\Fluid\Core\Parser\TemplateParser', array('parse'));
		$templateParserMock->expects($this->any())->method('parse')->will($this->returnValue($parsingState));

		//$mockSyntaxTreeCache = $this->getMock('F3\FLOW3\Cache\Frontend\variableFrontend', array(), array(), '', FALSE);

		$mockRequest = $this->getMock('F3\FLOW3\MVC\RequestInterface');
		$mockRequest->expects($this->any())->method('getControllerActionName')->will($this->returnValue('index'));
		$mockRequest->expects($this->any())->method('getControllerObjectName')->will($this->returnValue('F3\Fluid\Foo\Bar\Controller\BazController'));
		$mockRequest->expects($this->any())->method('getControllerPackageKey')->will($this->returnValue('Fluid'));
		$mockControllerContext = $this->getMock('F3\FLOW3\MVC\Controller\ControllerContext', array('getRequest'), array(), '', FALSE);
		$mockControllerContext->expects($this->any())->method('getRequest')->will($this->returnValue($mockRequest));

		$templateView = new \F3\Fluid\View\Fixture\TemplateViewFixture($this->objectFactory, $packageManager, $resourceManager, $this->objectManager);
		$templateView->injectTemplateParser($templateParserMock);
		//$templateView->injectSyntaxTreeCache($mockSyntaxTreeCache);
		$templateView->setTemplatePathAndFilename(__DIR__ . '/Fixtures/TemplateViewSectionFixture.html');
		$templateView->setLayoutPathAndFilename(__DIR__ . '/Fixtures/LayoutFixture.html');
		$templateView->setControllerContext($mockControllerContext);
		$templateView->initializeObject();
		$templateView->addVariable('name', 'value');
		$templateView->render();

		$this->assertSame($templateView, $syntaxTreeNode->variableContainer->get('view'), 'The view has not been placed in the variable container.');
		$this->assertEquals('value', $syntaxTreeNode->variableContainer->get('name'), 'Context variable has been set.');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function renderSingleSectionWorks() {
		$this->markTestSkipped('needs refactoring - this is a functional test with too many side effects');
		$templateView = new \F3\Fluid\View\TemplateView();
		$templateView->setTemplatePathAndFilename(__DIR__ . '/Fixtures/TemplateViewSectionFixture.html');
		$this->assertEquals($templateView->renderSection('mySection'), 'Output', 'Specific section was not rendered correctly!');
	}
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function layoutEngineMergesTemplateAndLayout() {
		$this->markTestSkipped('needs refactoring - this is a functional test with too many side effects');
		$templateView = new \F3\Fluid\View\TemplateView();
		$templateView->setTemplatePathAndFilename(__DIR__ . '/Fixtures/TemplateViewSectionFixture.html');
		$templateView->setLayoutPathAndFilename(__DIR__ . '/Fixtures/LayoutFixture.html');
		$this->assertEquals($templateView->renderWithLayout('LayoutFixture'), '<div>Output</div>', 'Specific section was not rendered correctly!');
	}
}

?>