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
 * Test for the "Textarea" Form view helper
 *
 * @package
 * @subpackage
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class TextareaViewHelperTest extends \F3\Testing\BaseTestCase {

	/**
	 * var \F3\Fluid\ViewHelpers\Form\TextareaViewHelper
	 */
	protected $viewHelper;

	public function setUp() {
		$this->viewHelper = new \F3\Fluid\ViewHelpers\Form\TextareaViewHelper();
		$this->viewHelper->initializeArguments();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function textareaCorrectlySetsTagName() {
		$tagBuilderMock = $this->getMock('F3\Fluid\Core\TagBuilder', array('setTagName'), array(), '', FALSE);
		$tagBuilderMock->expects($this->once())->method('setTagName')->with('textarea');
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
	public function textareaCorrectlySetsNameAttributeAndContent() {
		$tagBuilderMock = $this->getMock('F3\Fluid\Core\TagBuilder', array('addAttribute', 'setContent', 'render'), array(), '', FALSE);
		$tagBuilderMock->expects($this->once())->method('addAttribute')->with('name', 'NameOfTextarea');
		$tagBuilderMock->expects($this->once())->method('setContent')->with('Current value');
		$tagBuilderMock->expects($this->once())->method('render');
		$this->viewHelper->injectTagBuilder($tagBuilderMock);

		$arguments = new \F3\Fluid\Core\ViewHelperArguments(array(
			'name' => 'NameOfTextarea',
			'value' => 'Current value'
		));

		$this->viewHelper->arguments = $arguments;
		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}
}

?>
