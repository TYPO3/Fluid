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
 * @package 
 * @subpackage 
 * @version $Id:$
 */
/**
 * Testcase for [insert classname here]
 *
 * @package
 * @subpackage Tests
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

include_once(__DIR__ . '/Fixtures/F3_Fluid_View_Fixture_TransparentSyntaxTreeNode.php');
include_once(__DIR__ . '/Fixtures/F3_Fluid_View_Fixture_TemplateViewFixture.php');

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
		$templateParserMock->expects($this->any())
		                   ->method('parse')
		                   ->will($this->returnValue($parsingState));
		                   
		$templateView = new \F3\Fluid\View\Fixture\TemplateViewFixture($this->objectFactory, $packageManager, $resourceManager, $this->objectManager);
		$templateView->injectTemplateParser($templateParserMock);
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
		$templateView = $this->objectManager->getObject('F3\Fluid\View\TemplateView');
		$templateView->setTemplateFile(__DIR__ . '/Fixtures/TemplateViewSectionFixture.html');
		$this->assertEquals($templateView->renderSection('mySection'), 'Output', 'Specific section was not rendered correctly!');
	}
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function layoutEngineMergesTemplateAndLayout() {
		$templateView = $this->objectManager->getObject('F3\Fluid\View\TemplateView');
		$templateView->setTemplateFile(__DIR__ . '/Fixtures/TemplateViewSectionFixture.html');
		$templateView->setLayoutFile(__DIR__ . '/Fixtures/LayoutFixture.html');
		$this->assertEquals($templateView->renderWithLayout('LayoutFixture'), '<div>Output</div>', 'Specific section was not rendered correctly!');
	}
}



?>
