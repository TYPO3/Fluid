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
 * @package Fluid
 * @subpackage Core
 * @version $Id$
 */
/**
 * Testcase for the TemplateView
 *
 * @package Fluid
 * @subpackage Core
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
	public function renderCallsRenderOnParsedTemplateInterface() {
		$templateView = $this->getMock($this->buildAccessibleProxy('F3\Fluid\View\TemplateView'), array('parseTemplate', 'resolveTemplatePathAndFilename'), array(), '', FALSE);
		$parsedTemplate = $this->getMock('F3\Fluid\Core\ParsedTemplateInterface');
		$objectFactory = $this->getMock('F3\FLOW3\Object\FactoryInterface');

		$variableContainer = $this->getMock('F3\Fluid\Core\VariableContainer');
		$viewHelperContext = $this->getMock('F3\Fluid\Core\ViewHelperContext', array(), array(), '', FALSE);

		$objectFactory->expects($this->exactly(2))->method('create')->will($this->onConsecutiveCalls($variableContainer, $viewHelperContext));

		$templateView->_set('objectFactory', $objectFactory);

		$templateView->expects($this->once())->method('parseTemplate')->will($this->returnValue($parsedTemplate));

		// Real expectations
		$parsedTemplate->expects($this->once())->method('render')->with($variableContainer, $viewHelperContext)->will($this->returnValue('Hello World'));

		$this->assertEquals('Hello World', $templateView->render(), 'The output of the ParsedTemplates render Method is not returned by the TemplateView');

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

		$parsingState = new \F3\Fluid\Core\ParsingState();
		$parsingState->setRootNode($syntaxTreeNode);

		$templateParserMock = $this->getMock('F3\Fluid\Core\TemplateParser', array('parse'));
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