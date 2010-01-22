<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\View;

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

include_once(__DIR__ . '/Fixtures/TransparentSyntaxTreeNode.php');
include_once(__DIR__ . '/Fixtures/TemplateViewFixture.php');

/**
 * Testcase for the TemplateView
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class TemplateViewTest extends \F3\Testing\BaseTestCase {

	public function initializeViewSetsParserConfiguration() {
		$this->markTestSkipped('incomplete, needs to be written!');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function expandGenericPathPatternWorksWithBubblingDisabledAndFormatNotOptional() {
		$mockControllerContext = $this->setupMockControllerContextForPathResolving('MyPackage', NULL, 'My', 'html');

		$templateView = $this->getMock($this->buildAccessibleProxy('F3\Fluid\View\TemplateView'), array('getTemplateRootPath', 'getPartialRootPath', 'getLayoutRootPath'), array(), '', FALSE);
		$templateView->_set('controllerContext', $mockControllerContext);
		$templateView->expects($this->any())->method('getTemplateRootPath')->will($this->returnValue('Resources/Private/'));

		$expected = array('Resources/Private/Templates/My/@action.html');
		$actual = $templateView->_call('expandGenericPathPattern', '@templateRoot/Templates/@subpackage/@controller/@action.@format', FALSE, FALSE);
		$this->assertEquals($expected, $actual);
	}


	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function expandGenericPathPatternWorksWithSubpackageAndBubblingDisabledAndFormatNotOptional() {
		$mockControllerContext = $this->setupMockControllerContextForPathResolving('MyPackage', 'MySubPackage', 'My', 'html');

		$templateView = $this->getMock($this->buildAccessibleProxy('F3\Fluid\View\TemplateView'), array('getTemplateRootPath', 'getPartialRootPath', 'getLayoutRootPath'), array(), '', FALSE);
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
		$mockControllerContext = $this->setupMockControllerContextForPathResolving('MyPackage', 'MySubPackage', 'My', 'html');

		$templateView = $this->getMock($this->buildAccessibleProxy('F3\Fluid\View\TemplateView'), array('getTemplateRootPath', 'getPartialRootPath', 'getLayoutRootPath'), array(), '', FALSE);
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
		$mockControllerContext = $this->setupMockControllerContextForPathResolving('MyPackage', 'MySubPackage', 'My', 'html');

		$templateView = $this->getMock($this->buildAccessibleProxy('F3\Fluid\View\TemplateView'), array('getTemplateRootPath', 'getPartialRootPath', 'getLayoutRootPath'), array(), '', FALSE);
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
	 *
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function setupMockControllerContextForPathResolving($packageKey, $subPackageKey, $controllerName, $format) {
		$controllerObjectName = "F3\\$packageKey\\" . ($subPackageKey != $subPackageKey . '\\' ? : '') . 'Controller\\' . $controllerName . 'Controller';

		$mockRequest = $this->getMock('F3\FLOW3\MVC\RequestInterface');
		$mockRequest->expects($this->any())->method('getControllerPackageKey')->will($this->returnValue($packageKey));
		$mockRequest->expects($this->any())->method('getControllerSubPackageKey')->will($this->returnValue($subPackageKey));
		$mockRequest->expects($this->any())->method('getControllerName')->will($this->returnValue($controllerName));
		$mockRequest->expects($this->any())->method('getControllerObjectName')->will($this->returnValue($controllerObjectName));
		$mockRequest->expects($this->any())->method('getFormat')->will($this->returnValue($format));

		$mockControllerContext = $this->getMock('F3\FLOW3\MVC\Controller\Context', array('getRequest'), array(), '', FALSE);
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
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function getPartialRootPathReturnsUserSpecifiedPartialPath() {
		$templateView = $this->getMock($this->buildAccessibleProxy('F3\Fluid\View\TemplateView'), array('dummy'), array(), '', FALSE);
		$templateView->setPartialRootPath('/foo/bar');
		$expected = '/foo/bar';
		$actual = $templateView->_call('getPartialRootPath');
		$this->assertEquals($expected, $actual, 'A set partial root path was not returned correctly.');
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function getLayoutRootPathReturnsUserSpecifiedPartialPath() {
		$templateView = $this->getMock($this->buildAccessibleProxy('F3\Fluid\View\TemplateView'), array('dummy'), array(), '', FALSE);
		$templateView->setLayoutRootPath('/foo/bar');
		$expected = '/foo/bar';
		$actual = $templateView->_call('getLayoutRootPath');
		$this->assertEquals($expected, $actual, 'A set partial root path was not returned correctly.');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function renderCallsRenderOnParsedTemplateInterface() {
		$templateView = $this->getMock($this->buildAccessibleProxy('F3\Fluid\View\TemplateView'), array('parseTemplate', 'resolveTemplatePathAndFilename', 'buildParserConfiguration'), array(), '', FALSE);
		$parsedTemplate = $this->getMock('F3\Fluid\Core\Parser\ParsedTemplateInterface');
		$objectFactory = $this->getMock('F3\FLOW3\Object\ObjectFactoryInterface');
		$controllerContext = $this->getMock('F3\FLOW3\MVC\Controller\Context', array(), array(), '', FALSE);

		$variableContainer = $this->getMock('F3\Fluid\Core\ViewHelper\TemplateVariableContainer');
		$renderingContext = $this->getMock('F3\Fluid\Core\Rendering\RenderingContext', array(), array(), '', FALSE);

		$viewHelperVariableContainer = $this->getMock('F3\Fluid\Core\ViewHelper\ViewHelperVariableContainer');
		$objectFactory->expects($this->exactly(3))->method('create')->will($this->onConsecutiveCalls($variableContainer, $renderingContext, $viewHelperVariableContainer));

		$templateView->_set('objectFactory', $objectFactory);
		$templateView->injectTemplateParser($this->getMock('F3\Fluid\Core\Parser\TemplateParser'));
		$templateView->setControllerContext($controllerContext);

		$templateView->expects($this->once())->method('parseTemplate')->will($this->returnValue($parsedTemplate));

			// Real expectations
		$parsedTemplate->expects($this->once())->method('render')->with($renderingContext)->will($this->returnValue('Hello World'));

		$this->assertEquals('Hello World', $templateView->render(), 'The output of the ParsedTemplates render Method is not returned by the TemplateView');
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function parseTemplateReadsTheGivenTemplateAndReturnsTheParsedResult() {
		$mockTemplateParser = $this->getMock('F3\Fluid\Core\Parser\TemplateParser', array('parse'));
		$mockTemplateParser->expects($this->once())->method('parse')->with('Unparsed Template')->will($this->returnValue('Parsed Template'));

		$templateView = $this->getMock($this->buildAccessibleProxy('F3\Fluid\View\TemplateView'), array('dummy'), array(), '', FALSE);
		$templateView->injectTemplateParser($mockTemplateParser);

		$parsedTemplate = $templateView->_call('parseTemplate', __DIR__ . '/Fixtures/UnparsedTemplateFixture.html');
		$this->assertSame('Parsed Template', $parsedTemplate);
	}

	/**
	 * @test
	 * @expectedException F3\Fluid\View\Exception\InvalidTemplateResourceException
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function parseTemplateThrowsAnExceptionIfTheSpecifiedTemplateResourceDoesNotExist() {
		$templateView = $this->getMock($this->buildAccessibleProxy('F3\Fluid\View\TemplateView'), array('dummy'), array(), '', FALSE);
		$templateView->_call('parseTemplate', 'foo');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function pathToPartialIsResolvedCorrectly() {
		$this->markTestSkipped('Needs proper implementation.');
		$mockRequest = $this->getMock('F3\FLOW3\MVC\Request', array('getControllerPackageKey', ''));
		$mockRequest->expects($this->any())->method('getControllerPackageKey')->will($this->returnValue('DummyPackageKey'));
		$mockControllerContext = $this->getMock('F3\FLOW3\MVC\Controller\Context', array('getRequest'));
		$mockControllerContext->expects($this->any())->method('getRequest')->will($this->returnValue($mockRequest));

		$mockPackage = $this->getMock('F3\FLOW3\Package\PackageInterface', array('getPackagePath'));
		$mockPackage->expects($this->any())->method('getPackagePath')->will($this->returnValue('/ExamplePackagePath/'));
		$mockPackageManager = $this->getMock('F3\FLOW3\Package\PackageManagerInterface', array('getPackage'));
		$mockPackageManager->expects($this->any())->method('getPackage')->with('DummyPackageKey')->will($this->returnValue($mockPackage));

		\vfsStreamWrapper::register();
		$mockRootDirectory = vfsStreamDirectory::create('ExamplePackagePath/Resources/Private/Partials');
		$mockRootDirectory->getChild('Resources/Private/Partials')->addChild('Partials');
		\vfsStreamWrapper::setRoot($mockRootDirectory);

		$this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\Parser\TemplateParser'), array(''), array(), '', FALSE);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function viewIsPlacedInVariableContainer() {
		$this->markTestSkipped('view will be placed in ViewHelperContext soon');
		$packageManager = $this->objectManager->getObject('F3\FLOW3\Package\PackageManagerInterface');
		$resourceManager = $this->objectManager->getObject('F3\FLOW3\Resource\ResourceManager');

		$syntaxTreeNode = new \F3\Fluid\View\Fixture\TransparentSyntaxTreeNode();

		$parsingState = new \F3\Fluid\Core\Parser\ParsingState();
		$parsingState->setRootNode($syntaxTreeNode);

		$templateParserMock = $this->getMock('F3\Fluid\Core\Parser\TemplateParser', array('parse'));
		$templateParserMock->expects($this->any())->method('parse')->will($this->returnValue($parsingState));

		$mockRequest = $this->getMock('F3\FLOW3\MVC\RequestInterface');
		$mockRequest->expects($this->any())->method('getControllerActionName')->will($this->returnValue('index'));
		$mockRequest->expects($this->any())->method('getControllerObjectName')->will($this->returnValue('F3\Fluid\Foo\Bar\Controller\BazController'));
		$mockRequest->expects($this->any())->method('getControllerPackageKey')->will($this->returnValue('Fluid'));
		$mockControllerContext = $this->getMock('F3\FLOW3\MVC\Controller\Context', array('getRequest'), array(), '', FALSE);
		$mockControllerContext->expects($this->any())->method('getRequest')->will($this->returnValue($mockRequest));

		$templateView = new \F3\Fluid\View\Fixture\TemplateViewFixture($this->objectFactory, $packageManager, $resourceManager, $this->objectManager);
		$templateView->injectTemplateParser($templateParserMock);
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