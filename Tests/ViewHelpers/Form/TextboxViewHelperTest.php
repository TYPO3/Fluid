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
 * @version $Id$
 */

/**
 * Test for the "Textbox" Form view helper
 *
 * @package
 * @subpackage
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class TextboxViewHelperTest extends \F3\Testing\BaseTestCase {

	/**
	 * var \F3\Fluid\ViewHelpers\Form\TextboxViewHelper
	 */
	protected $viewHelper;

	public function setUp() {
		$this->viewHelper = new \F3\Fluid\ViewHelpers\Form\TextboxViewHelper();
		$this->viewHelper->initializeArguments();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function selectCorrectlySetsTagName() {
		$tagBuilderMock = $this->getMock('F3\Fluid\Core\TagBuilder', array('setTagName'), array(), '', FALSE);
		$tagBuilderMock->expects($this->once())->method('setTagName')->with('input');
		$this->viewHelper->injectTagBuilder($tagBuilderMock);
		$this->viewHelper->arguments = new \F3\Fluid\Core\ViewHelperArguments(array());

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function textboxCorrectlySetsTypeNameAndValueAttributes() {
		$tagBuilderMock = $this->getMock('F3\Fluid\Core\TagBuilder', array('addAttribute', 'setContent', 'render'), array(), '', FALSE);
		$tagBuilderMock->expects($this->at(0))->method('addAttribute')->with('type', 'text');
		$tagBuilderMock->expects($this->at(1))->method('addAttribute')->with('name', 'NameOfTextbox');
		$tagBuilderMock->expects($this->at(2))->method('addAttribute')->with('value', 'Current value');
		$tagBuilderMock->expects($this->once())->method('render');
		$this->viewHelper->injectTagBuilder($tagBuilderMock);

		$arguments = new \F3\Fluid\Core\ViewHelperArguments(array(
			'name' => 'NameOfTextbox',
			'value' => 'Current value'
		));

		$this->viewHelper->arguments = $arguments;
		$this->viewHelper->setViewHelperNode(new \F3\Fluid\ViewHelpers\Fixtures\EmptySyntaxTreeNode());
		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}
}

?>
