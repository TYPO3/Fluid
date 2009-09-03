<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core;

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

include_once(__DIR__ . '/Fixtures/TestTagBasedViewHelper.php');

/**
 * Testcase for TagBasedViewHelper
 *
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class TagBasedViewHelperTest extends \F3\Testing\BaseTestCase {

	public function setUp() {
		$this->viewHelper = new \F3\Fluid\Core\Fixtures\TestTagBasedViewHelper();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function initializeResetsUnderlyingTagBuilder() {
		$mockTagBuilder = $this->getMock('F3\Fluid\Core\ViewHelper\TagBuilder', array('reset'), array(), '', FALSE);
		$mockTagBuilder->expects($this->once())->method('reset');
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$this->viewHelper->initialize();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function oneTagAttributeIsRenderedCorrectly() {
		$mockTagBuilder = $this->getMock('F3\Fluid\Core\ViewHelper\TagBuilder', array('addAttribute'), array(), '', FALSE);
		$mockTagBuilder->expects($this->once())->method('addAttribute')->with('foo', 'bar');
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$this->viewHelper->registerTagAttribute('foo', 'string', 'Description', FALSE);
		$arguments = new \F3\Fluid\Core\ViewHelper\Arguments(array('foo' => 'bar'));
		$this->viewHelper->setArguments($arguments);
		$this->viewHelper->initialize();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function additionalTagAttributesAreRenderedCorrectly() {
		$mockTagBuilder = $this->getMock('F3\Fluid\Core\ViewHelper\TagBuilder', array('addAttribute'), array(), '', FALSE);
		$mockTagBuilder->expects($this->once())->method('addAttribute')->with('foo', 'bar');
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$this->viewHelper->registerTagAttribute('foo', 'string', 'Description', FALSE);
		$arguments = new \F3\Fluid\Core\ViewHelper\Arguments(array('additionalAttributes' => array('foo' => 'bar')));
		$this->viewHelper->setArguments($arguments);
		$this->viewHelper->initialize();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function standardTagAttributesAreRegistered() {
		$mockTagBuilder = $this->getMock('F3\Fluid\Core\ViewHelper\TagBuilder', array('addAttribute'), array(), '', FALSE);
		$mockTagBuilder->expects($this->at(0))->method('addAttribute')->with('class', 'classAttribute');
		$mockTagBuilder->expects($this->at(1))->method('addAttribute')->with('dir', 'dirAttribute');
		$mockTagBuilder->expects($this->at(2))->method('addAttribute')->with('id', 'idAttribute');
		$mockTagBuilder->expects($this->at(3))->method('addAttribute')->with('lang', 'langAttribute');
		$mockTagBuilder->expects($this->at(4))->method('addAttribute')->with('style', 'styleAttribute');
		$mockTagBuilder->expects($this->at(5))->method('addAttribute')->with('title', 'titleAttribute');
		$mockTagBuilder->expects($this->at(6))->method('addAttribute')->with('accesskey', 'accesskeyAttribute');
		$mockTagBuilder->expects($this->at(7))->method('addAttribute')->with('tabindex', 'tabindexAttribute');
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$arguments = new \F3\Fluid\Core\ViewHelper\Arguments(
			array(
				'class' => 'classAttribute',
				'dir' => 'dirAttribute',
				'id' => 'idAttribute',
				'lang' => 'langAttribute',
				'style' => 'styleAttribute',
				'title' => 'titleAttribute',
				'accesskey' => 'accesskeyAttribute',
				'tabindex' => 'tabindexAttribute'
			)
		);
		$this->viewHelper->setArguments($arguments);
		$this->viewHelper->initializeArguments();
		$this->viewHelper->initialize();
	}
}
?>