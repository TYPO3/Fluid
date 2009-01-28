<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers\Form;

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

include_once(__DIR__ . '/Fixtures/EmptySyntaxTreeNode.php');
include_once(__DIR__ . '/Fixtures/Fixture_UserDomainClass.php');
/**
 * @package 
 * @subpackage 
 * @version $Id:$
 */
/**
 * Test for the "Textbox" Form view helper
 *
 * @package
 * @subpackage
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class TextboxViewHelperTest extends \F3\Testing\BaseTestCase {
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function textBoxReturnsExpectedXML() {
		$this->viewHelper = new \F3\Fluid\ViewHelpers\Form\TextboxViewHelper();
		$this->viewHelper->initializeArguments();
		
		$arguments = new \F3\Fluid\Core\ViewHelperArguments(array(
			'name' => 'NameOfTextbox',
			'value' => 'Current value'
		));

		$this->viewHelper->arguments = $arguments;
		$this->viewHelper->setViewHelperNode(new \F3\Fluid\ViewHelpers\Fixtures\EmptySyntaxTreeNode());
		$output = $this->viewHelper->render();
		$element = new \SimpleXMLElement($output);
		
		$this->assertEquals('NameOfTextbox', (string)$element['name'], 'Name was not correctly read out');
		$this->assertEquals('Current value', (string)$element['value'], 'Value was not correctly read out');
	}
}

?>
