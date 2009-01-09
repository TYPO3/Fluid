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

/**
 * @package
 * @subpackage
 * @version $Id:$
 */

include_once(__DIR__ . '/Fixtures/F3_Fluid_ViewHelpers_Fixtures_EmptySyntaxTreeNode.php');
include_once(__DIR__ . '/Fixtures/Fixture_UserDomainClass.php');

/**
 * Test for the "Select" Form view helper
 *
 * @package
 * @subpackage
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class SelectViewHelperTest extends \F3\Testing\BaseTestCase {
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function selectReturnsExpectedXML() {
		$this->viewHelper = new \F3\Fluid\ViewHelpers\Form\SelectViewHelper();
		$this->viewHelper->initializeArguments();

		$arguments = new \F3\Fluid\Core\ViewHelperArguments(array(
			'options' => array(
				'k1' => 'v1',
				'k2' => 'v2'
			),
			'value' => 'k2',
			'name' => 'myName'
		));

		$this->viewHelper->arguments = $arguments;
		$this->viewHelper->setViewHelperNode(new \F3\Fluid\ViewHelpers\Fixtures\EmptySyntaxTreeNode());
		$output = $this->viewHelper->render();
		$element = new \SimpleXMLElement($output);

		$this->assertEquals('myName', (string)$element['name'], 'Name was not correctly read out');

		$selectedNode = $element->xpath('/select/option[@value="k2"]');
		$this->assertEquals('selected', (string)$selectedNode[0]['selected'], 'The selected value was not correct.');

		$this->assertEquals('v1', (string)$element->option[0], 'One option was not rendered, albeit it should (1).');
		$this->assertEquals('v2', (string)$element->option[1], 'One option was not rendered, albeit it should (2).');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function selectOnDomainObjectsReturnsExpectedXML() {
		$this->viewHelper = new \F3\Fluid\ViewHelpers\Form\SelectViewHelper();
		$this->viewHelper->initializeArguments();

		$user_sk = new \F3\Fluid\ViewHelpers\Fixtures\UserDomainClass(2, 'Sebastian', 'Kurfuerst');

		$arguments = new \F3\Fluid\Core\ViewHelperArguments(array(
			'options' => array(
				new \F3\Fluid\ViewHelpers\Fixtures\UserDomainClass(1, 'Ingmar', 'Schlecht'),
				$user_sk,
				new \F3\Fluid\ViewHelpers\Fixtures\UserDomainClass(3, 'Robert', 'Lemke')
			),
			'value' => $user_sk,
			'optionKey' => 'id',
			'optionValue' => 'firstName',
			'name' => 'myName'
		));

		$this->viewHelper->arguments = $arguments;
		$this->viewHelper->setViewHelperNode(new \F3\Fluid\ViewHelpers\Fixtures\EmptySyntaxTreeNode());
		$output = $this->viewHelper->render();
		$element = new \SimpleXMLElement($output);

		$selectedNode = $element->xpath('/select/option[@value="2"]');
		$this->assertEquals('selected', (string)$selectedNode[0]['selected'], 'The selected value was not correct.');

		$this->assertEquals('Ingmar', (string)$element->option[0], 'One option was not rendered, albeit it should (1).');
		$this->assertEquals('Sebastian', (string)$element->option[1], 'One option was not rendered, albeit it should (2).');
	}
}

?>