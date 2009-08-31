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

require_once(__DIR__ . '/../ViewHelperBaseTestcase.php');

/**
 * Test for the "Checkbox" Form view helper
 *
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class CheckboxViewHelperTest extends \F3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * var \F3\Fluid\ViewHelpers\Form\CheckboxViewHelper
	 */
	protected $viewHelper;

	public function setUp() {
		parent::setUp();
		$this->viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\Form\CheckboxViewHelper'), array('setErrorClassAttribute', 'getName', 'getValue', 'isObjectAccessorMode', 'getPropertyValue'));
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
		$this->viewHelper->initializeArguments();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderCorrectlySetsTagNameAndDefaultAttributes() {
		$mockTagBuilder = $this->getMock('F3\Fluid\Core\ViewHelper\TagBuilder', array('setTagName', 'addAttribute'));
		$mockTagBuilder->expects($this->once())->method('setTagName')->with('input');
		$mockTagBuilder->expects($this->at(1))->method('addAttribute')->with('type', 'checkbox');
		$mockTagBuilder->expects($this->at(2))->method('addAttribute')->with('name', 'foo');
		$mockTagBuilder->expects($this->at(3))->method('addAttribute')->with('value', 'bar');

		$this->viewHelper->expects($this->any())->method('getName')->will($this->returnValue('foo'));
		$this->viewHelper->expects($this->any())->method('getValue')->will($this->returnValue('bar'));
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderSetsCheckedAttributeIfSpecified() {
		$mockTagBuilder = $this->getMock('F3\Fluid\Core\ViewHelper\TagBuilder', array('setTagName', 'addAttribute'));
		$mockTagBuilder->expects($this->at(1))->method('addAttribute')->with('type', 'checkbox');
		$mockTagBuilder->expects($this->at(2))->method('addAttribute')->with('name', 'foo');
		$mockTagBuilder->expects($this->at(3))->method('addAttribute')->with('value', 'bar');
		$mockTagBuilder->expects($this->at(4))->method('addAttribute')->with('checked', 'checked');

		$this->viewHelper->expects($this->any())->method('getName')->will($this->returnValue('foo'));
		$this->viewHelper->expects($this->any())->method('getValue')->will($this->returnValue('bar'));
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$this->viewHelper->initialize();
		$this->viewHelper->render(TRUE);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderIgnoresBoundPropertyIfCheckedIsSet() {
		$mockTagBuilder = $this->getMock('F3\Fluid\Core\ViewHelper\TagBuilder', array('setTagName', 'addAttribute'));
		$mockTagBuilder->expects($this->at(1))->method('addAttribute')->with('type', 'checkbox');
		$mockTagBuilder->expects($this->at(2))->method('addAttribute')->with('name', 'foo');
		$mockTagBuilder->expects($this->at(3))->method('addAttribute')->with('value', 'bar');

		$this->viewHelper->expects($this->any())->method('getName')->will($this->returnValue('foo'));
		$this->viewHelper->expects($this->any())->method('getValue')->will($this->returnValue('bar'));
		$this->viewHelper->expects($this->never())->method('isObjectAccessorMode')->will($this->returnValue(TRUE));
		$this->viewHelper->expects($this->never())->method('getPropertyValue')->will($this->returnValue(TRUE));
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$this->viewHelper->initialize();
		$this->viewHelper->render(TRUE);
		$this->viewHelper->render(FALSE);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderCorrectlySetsCheckedAttributeIfCheckboxIsBoundToAPropertyOfTypeBoolean() {
		$mockTagBuilder = $this->getMock('F3\Fluid\Core\ViewHelper\TagBuilder', array('setTagName', 'addAttribute'));
		$mockTagBuilder->expects($this->at(1))->method('addAttribute')->with('type', 'checkbox');
		$mockTagBuilder->expects($this->at(2))->method('addAttribute')->with('name', 'foo');
		$mockTagBuilder->expects($this->at(3))->method('addAttribute')->with('value', 'bar');
		$mockTagBuilder->expects($this->at(4))->method('addAttribute')->with('checked', 'checked');

		$this->viewHelper->expects($this->any())->method('getName')->will($this->returnValue('foo'));
		$this->viewHelper->expects($this->any())->method('getValue')->will($this->returnValue('bar'));
		$this->viewHelper->expects($this->any())->method('isObjectAccessorMode')->will($this->returnValue(TRUE));
		$this->viewHelper->expects($this->any())->method('getPropertyValue')->will($this->returnValue(TRUE));
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderAppendsSquareBracketsToNameAttributeIfBoundToAPropertyOfTypeArray() {
		$mockTagBuilder = $this->getMock('F3\Fluid\Core\ViewHelper\TagBuilder', array('setTagName', 'addAttribute'));
		$mockTagBuilder->expects($this->at(1))->method('addAttribute')->with('type', 'checkbox');
		$mockTagBuilder->expects($this->at(2))->method('addAttribute')->with('name', 'foo[]');
		$mockTagBuilder->expects($this->at(3))->method('addAttribute')->with('value', 'bar');

		$this->viewHelper->expects($this->any())->method('getName')->will($this->returnValue('foo'));
		$this->viewHelper->expects($this->any())->method('getValue')->will($this->returnValue('bar'));
		$this->viewHelper->expects($this->any())->method('isObjectAccessorMode')->will($this->returnValue(TRUE));
		$this->viewHelper->expects($this->any())->method('getPropertyValue')->will($this->returnValue(array()));
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderCorrectlySetsCheckedAttributeIfCheckboxIsBoundToAPropertyOfTypeArray() {
		$mockTagBuilder = $this->getMock('F3\Fluid\Core\ViewHelper\TagBuilder', array('setTagName', 'addAttribute'));
		$mockTagBuilder->expects($this->at(1))->method('addAttribute')->with('type', 'checkbox');
		$mockTagBuilder->expects($this->at(2))->method('addAttribute')->with('name', 'foo[]');
		$mockTagBuilder->expects($this->at(3))->method('addAttribute')->with('value', 'bar');
		$mockTagBuilder->expects($this->at(4))->method('addAttribute')->with('checked', 'checked');

		$this->viewHelper->expects($this->any())->method('getName')->will($this->returnValue('foo'));
		$this->viewHelper->expects($this->any())->method('getValue')->will($this->returnValue('bar'));
		$this->viewHelper->expects($this->any())->method('isObjectAccessorMode')->will($this->returnValue(TRUE));
		$this->viewHelper->expects($this->any())->method('getPropertyValue')->will($this->returnValue(array('foo', 'bar', 'baz')));
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 * @expectedException \F3\Fluid\Core\ViewHelper\Exception
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function bindingObjectsToACheckboxThatAreNotOfTypeBooleanOrArrayThrowsException() {
		$mockTagBuilder = $this->getMock('F3\Fluid\Core\ViewHelper\TagBuilder', array('setTagName', 'addAttribute'));

		$this->viewHelper->expects($this->any())->method('isObjectAccessorMode')->will($this->returnValue(TRUE));
		$this->viewHelper->expects($this->any())->method('getPropertyValue')->will($this->returnValue(new \stdClass()));
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderCallsSetErrorClassAttribute() {
		$this->viewHelper->expects($this->once())->method('setErrorClassAttribute');
		$this->viewHelper->render();
	}
}

?>