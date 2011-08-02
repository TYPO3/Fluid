<?php
namespace TYPO3\Fluid\Tests\Unit\Core\ViewHelper;

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

require_once(__DIR__ . '/../Fixtures/TestViewHelper.php');

/**
 * Testcase for AbstractViewHelper
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ViewHelperVariableContainerTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 *
	 * @var TYPO3\Fluid\Core\ViewHelper\ViewHelperVariableContainer
	 */
	protected $viewHelperVariableContainer;

	protected function setUp() {
		$this->viewHelperVariableContainer = new \TYPO3\Fluid\Core\ViewHelper\ViewHelperVariableContainer();
	}
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function storedDataCanBeReadOutAgain() {
		$variable = 'Hello world';
		$this->assertFalse($this->viewHelperVariableContainer->exists('TYPO3\Fluid\ViewHelpers\TestViewHelper', 'test'));
		$this->viewHelperVariableContainer->add('TYPO3\Fluid\ViewHelpers\TestViewHelper', 'test', $variable);
		$this->assertTrue($this->viewHelperVariableContainer->exists('TYPO3\Fluid\ViewHelpers\TestViewHelper', 'test'));

		$this->assertEquals($variable, $this->viewHelperVariableContainer->get('TYPO3\Fluid\ViewHelpers\TestViewHelper', 'test'));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception\InvalidVariableException
	 */
	public function gettingNonNonExistentValueThrowsException() {
		$this->viewHelperVariableContainer->get('TYPO3\Fluid\ViewHelper\NonExistent', 'nonExistentKey');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception\InvalidVariableException
	 */
	public function settingKeyWhichIsAlreadyStoredThrowsException() {
		$this->viewHelperVariableContainer->add('TYPO3\Fluid\ViewHelper\NonExistent', 'nonExistentKey', 'value1');
		$this->viewHelperVariableContainer->add('TYPO3\Fluid\ViewHelper\NonExistent', 'nonExistentKey', 'value2');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function addOrUpdateWorks() {
		$this->viewHelperVariableContainer->add('TYPO3\Fluid\ViewHelper\NonExistent', 'nonExistentKey', 'value1');
		$this->viewHelperVariableContainer->addOrUpdate('TYPO3\Fluid\ViewHelper\NonExistent', 'nonExistentKey', 'value2');
		$this->assertEquals($this->viewHelperVariableContainer->get('TYPO3\Fluid\ViewHelper\NonExistent', 'nonExistentKey'), 'value2');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function aSetValueCanBeRemovedAgain() {
		$this->viewHelperVariableContainer->add('TYPO3\Fluid\ViewHelper\NonExistent', 'nonExistentKey', 'value1');
		$this->viewHelperVariableContainer->remove('TYPO3\Fluid\ViewHelper\NonExistent', 'nonExistentKey');
		$this->assertFalse($this->viewHelperVariableContainer->exists('TYPO3\Fluid\ViewHelper\NonExistent', 'nonExistentKey'));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception\InvalidVariableException
	 */
	public function removingNonExistentKeyThrowsException() {
		$this->viewHelperVariableContainer->remove('TYPO3\Fluid\ViewHelper\NonExistent', 'nonExistentKey');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function viewCanBeReadOutAgain() {
		$view = $this->getMock('TYPO3\Fluid\View\AbstractTemplateView', array('getTemplateSource', 'getLayoutSource', 'getPartialSource', 'hasTemplate', 'canRender', 'getTemplateIdentifier', 'getLayoutIdentifier', 'getPartialIdentifier'));
		$this->viewHelperVariableContainer->setView($view);
		$this->assertSame($view, $this->viewHelperVariableContainer->getView());
	}
}
?>