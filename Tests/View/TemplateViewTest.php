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
 * @version $Id:$
 */
/**
 * Testcase for the TemplateView
 *
 * @package Fluid
 * @subpackage Core
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

include_once(__DIR__ . '/Fixtures/TransparentSyntaxTreeNode.php');
include_once(__DIR__ . '/Fixtures/TemplateViewFixture.php');

class TemplateViewTest extends \F3\Testing\BaseTestCase {

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function viewIsPlacedInVariableContainer() {
		$packageManager = $this->objectManager->getObject('F3\FLOW3\Package\ManagerInterface');
		$resourceManager = $this->objectManager->getObject('F3\FLOW3\Resource\Manager');

		$syntaxTreeNode = new \F3\Fluid\View\Fixture\TransparentSyntaxTreeNode();

		$parsingState = new \F3\Fluid\Core\ParsingState();
		$parsingState->setRootNode($syntaxTreeNode);

		$templateParserMock = $this->getMock('F3\Fluid\Core\TemplateParser', array('parse'));
		$templateParserMock->expects($this->any())->method('parse')->will($this->returnValue($parsingState));

		$mockRequest = $this->getMock('F3\FLOW3\MVC\Request');
		$mockRequest->expects($this->any())->method('getControllerActionName')->will($this->returnValue('index'));
		$mockRequest->expects($this->any())->method('getControllerObjectName')->will($this->returnValue('F3\Fluid\Foo\Bar\Controller\BazController'));
		$mockRequest->expects($this->any())->method('getControllerPackageKey')->will($this->returnValue('Fluid'));

		$templateView = new \F3\Fluid\View\Fixture\TemplateViewFixture($this->objectFactory, $packageManager, $resourceManager, $this->objectManager);
		$templateView->injectTemplateParser($templateParserMock);
		$templateView->setTemplatePathAndFilename(__DIR__ . '/Fixtures/TemplateViewSectionFixture.html');
		$templateView->setLayoutPathAndFilename(__DIR__ . '/Fixtures/LayoutFixture.html');
		$templateView->setRequest($mockRequest);
		$templateView->addVariable('name', 'value');
		$templateView->initializeView();
		$templateView->render();

		$this->assertSame($templateView, $syntaxTreeNode->variableContainer->get('view'), 'The view has not been placed in the variable container.');
		$this->assertEquals('value', $syntaxTreeNode->variableContainer->get('name'), 'Context variable has been set.');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function renderSingleSectionWorks() {
		$templateView = $this->objectManager->getObject('F3\Fluid\View\TemplateView');
		$templateView->setTemplatePathAndFilename(__DIR__ . '/Fixtures/TemplateViewSectionFixture.html');
		$this->assertEquals($templateView->renderSection('mySection'), 'Output', 'Specific section was not rendered correctly!');
	}
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function layoutEngineMergesTemplateAndLayout() {
		$templateView = $this->objectManager->getObject('F3\Fluid\View\TemplateView');
		$templateView->setTemplatePathAndFilename(__DIR__ . '/Fixtures/TemplateViewSectionFixture.html');
		$templateView->setLayoutPathAndFilename(__DIR__ . '/Fixtures/LayoutFixture.html');
		$this->assertEquals($templateView->renderWithLayout('LayoutFixture'), '<div>Output</div>', 'Specific section was not rendered correctly!');
	}
}



?>
