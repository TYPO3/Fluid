<?php
declare(ENCODING = 'utf-8');
namespace F3::Beer3::ViewHelpers;

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

include_once(__DIR__ . '/Fixtures/F3_Beer3_ViewHelpers_Fixtures_EmptySyntaxTreeNode.php');
/**
 * @package 
 * @subpackage 
 * @version $Id:$
 */
/**
 * [Enter description here]
 *
 * @package
 * @subpackage
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class SelectViewHelperTest extends F3::Testing::BaseTestCase {
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function selectReturnsExpectedXML() {
		$this->viewHelper = new F3::Beer3::ViewHelpers::Form::SelectViewHelper();
		$this->viewHelper->initializeArguments();
		
		$arguments = new F3::Beer3::Core::ViewHelperArguments(array(
			'options' => array(
				'k1' => 'v1',
				'k2' => 'v2'
			),
			'selectedValue' => 'k2',
			'name' => 'myName'
		));

		$this->viewHelper->arguments = $arguments;
		$this->viewHelper->setViewHelperNode(new F3::Beer3::ViewHelpers::Fixtures::EmptySyntaxTreeNode());
		$output = $this->viewHelper->render();
		$element = new ::SimpleXMLElement($output);
		
		$this->assertEquals('myName', (string)$element['name'], 'Name was not correctly read out');
		
		$selectedNode = $element->xpath('/select/option[@value="k2"]');
		$this->assertEquals('selected', (string)$selectedNode[0]['selected'], 'The selected value was not correct.');
		
		$this->assertEquals('v1', (string)$element->option[0], 'One option was not rendered, albeit it should (1).');
		$this->assertEquals('v2', (string)$element->option[1], 'One option was not rendered, albeit it should (2).');
	}
}


?>