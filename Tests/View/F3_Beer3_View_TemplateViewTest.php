<?php
declare(ENCODING = 'utf-8');
namespace F3::Beer3::View;

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

include_once(__DIR__ . '/Fixtures/F3_Beer3_View_Fixture_TransparentSyntaxTreeNode.php');
include_once(__DIR__ . '/Fixtures/F3_Beer3_View_Fixture_TemplateViewFixture.php');

class TemplateViewTest extends F3::Testing::BaseTestCase {

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function viewIsPlacedInVariableContainer() {
		$packageManager = $this->objectManager->getObject('F3::FLOW3::Package::ManagerInterface');
		$resourceManager = $this->objectManager->getObject('F3::FLOW3::Resource::Manager');
		
		$syntaxTreeNode = new F3::Beer3::View::Fixture::TransparentSyntaxTreeNode();
		
		$templateParserMock = $this->getMock('F3::Beer3::Core::TemplateParser', array('parse'));
		$templateParserMock->expects($this->any())
		                   ->method('parse')
		                   ->will($this->returnValue($syntaxTreeNode));
		                   
		$templateView = new F3::Beer3::View::Fixture::TemplateViewFixture($this->objectFactory, $packageManager, $resourceManager, $this->objectManager);
		$templateView->injectTemplateParser($templateParserMock);
		$templateView->addVariable('name', 'value');
		$templateView->render();
		
		$this->assertSame($templateView, $syntaxTreeNode->variableContainer->get('view'), 'The view has not been placed in the variable container.');
		$this->assertEquals('value', $syntaxTreeNode->variableContainer->get('name'), 'Context variable has been set.');
		
	}
}



?>