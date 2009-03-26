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

/**
 * @package
 * @subpackage
 * @version $Id$
 */

include_once(__DIR__ . '/Fixtures/TestTagBasedViewHelper.php');
/**
 * Testcase for [insert classname here]
 *
 * @package
 * @subpackage Tests
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class TagBasedViewHelperTest extends \F3\Testing\BaseTestCase {

	public function setUp() {
		$this->viewHelper = new \F3\Fluid\TestTagBasedViewHelper();
	}
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function oneTagAttributeIsRenderedCorrectly() {
		$this->viewHelper->registerTagAttribute('x', 'string', 'Description', FALSE);
		$arguments = new \F3\Fluid\Core\ViewHelperArguments(array('x' => 'Hallo'));
		$expected = 'x="Hallo"';

		$this->viewHelper->arguments = $arguments;
		$this->assertEquals($expected, $this->viewHelper->render(), 'A simple tag attribute was not rendered correctly.');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function additionalTagAttributesAreRenderedCorrectly() {
		$arguments = new \F3\Fluid\Core\ViewHelperArguments(array('additionalAttributes' => array('x' => 'Hallo')));
		$expected = 'x="Hallo"';

		$this->viewHelper->arguments = $arguments;
		$this->assertEquals($expected, $this->viewHelper->render(), 'An additional tag attribute was not rendered correctly.');
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function standardTagAttributesAreRegistered() {
		$arguments = new \F3\Fluid\Core\ViewHelperArguments(array('class' => 'classAttribute', 'dir' => 'dirAttribute', 'id' => 'idAttribute', 'lang' => 'langAttribute', 'style' => 'styleAttribute', 'title' => 'titleAttribute', 'accesskey' => 'accesskeyAttribute', 'tabindex' => 'tabindexAttribute'));
		$expected = 'class="classAttribute" dir="dirAttribute" id="idAttribute" lang="langAttribute" style="styleAttribute" title="titleAttribute" accesskey="accesskeyAttribute" tabindex="tabindexAttribute"';

		$this->viewHelper->arguments = $arguments;
		$this->viewHelper->initializeArguments();
		$this->assertEquals($expected, $this->viewHelper->render());
	}
}



?>
