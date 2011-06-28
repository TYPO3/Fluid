<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers\Link;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

require_once(__DIR__ . '/../ViewHelperBaseTestcase.php');

/**
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class EmailViewHelperTest extends \TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * var \TYPO3\Fluid\ViewHelpers\Link\EmailViewHelper
	 */
	protected $viewHelper;

	public function setUp() {
		parent::setUp();
		$this->viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Link\EmailViewHelper', array('renderChildren'));
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
		$this->viewHelper->initializeArguments();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderCorrectlySetsTagNameAndAttributesAndContent() {
		$mockTagBuilder = $this->getMock('TYPO3\Fluid\Core\ViewHelper\TagBuilder', array('setTagName', 'addAttribute', 'setContent'));
		$mockTagBuilder->expects($this->once())->method('setTagName')->with('a');
		$mockTagBuilder->expects($this->once())->method('addAttribute')->with('href', 'mailto:some@email.tld');
		$mockTagBuilder->expects($this->once())->method('setContent')->with('some content');
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$this->viewHelper->expects($this->any())->method('renderChildren')->will($this->returnValue('some content'));

		$this->viewHelper->initialize();
		$this->viewHelper->render('some@email.tld');
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderSetsTagContentToEmailIfRenderChildrenReturnNull() {
		$mockTagBuilder = $this->getMock('TYPO3\Fluid\Core\ViewHelper\TagBuilder', array('setTagName', 'addAttribute', 'setContent'));
		$mockTagBuilder->expects($this->once())->method('setContent')->with('some@email.tld');
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$this->viewHelper->expects($this->any())->method('renderChildren')->will($this->returnValue(NULL));

		$this->viewHelper->initialize();
		$this->viewHelper->render('some@email.tld');
	}
}

?>