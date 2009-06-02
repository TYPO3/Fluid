<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * @package Fluid
 * @subpackage ViewHelpers
 */

require_once(__DIR__ . '/ViewHelperBaseTestcase.php');

/**
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id:$
 */
class FormViewHelperTest extends \F3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function renderHiddenIdentityFieldReturnsAHiddenInputFieldContainingTheObjectsUUID() {
		$object = new \stdClass();

		$mockBackend = $this->getMock('F3\FLOW3\Persistence\BackendInterface');
		$mockBackend->expects($this->once())->method('getUUIDByObject')->with($object)->will($this->returnValue('123'));

		$mockPersistenceManager = $this->getMock('F3\FLOW3\Persistence\ManagerInterface');
		$mockPersistenceManager->expects($this->once())->method('getBackend')->will($this->returnValue($mockBackend));

		$expectedResult = '<input type="hidden" name="theName[__identity]" value="123" />';

		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\FormViewHelper'), array('dummy'), array(), '', FALSE);
		$viewHelper->setArguments(new \F3\Fluid\Core\ViewHelper\Arguments(array('name' => 'theName')));
		$viewHelper->_set('persistenceManager', $mockPersistenceManager);

		$actualResult = $viewHelper->_call('renderHiddenIdentityField', $object);
		$this->assertSame($expectedResult, $actualResult);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function renderAddsObjectToTemplateVariableContainer() {
		$formObject = new \stdClass();

		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\FormViewHelper'), array('renderChildren', 'renderHiddenIdentityField'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);


		$this->viewHelperVariableContainer->expects($this->once())->method('add')->with('F3\Fluid\ViewHelpers\FormViewHelper', 'formObject', $formObject);
		$this->viewHelperVariableContainer->expects($this->once())->method('remove')->with('F3\Fluid\ViewHelpers\FormViewHelper', 'formObject');
		$viewHelper->render('', array(), NULL, NULL, NULL, $formObject);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function renderAddsFormNameToTemplateVariableContainer() {
		$formName = 'someFormName';

		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\FormViewHelper'), array('renderChildren', 'renderHiddenIdentityField'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$viewHelper->setArguments(new \F3\Fluid\Core\ViewHelper\Arguments(array('name' => $formName)));

		$this->viewHelperVariableContainer->expects($this->once())->method('add')->with('F3\Fluid\ViewHelpers\FormViewHelper', 'formName', $formName);
		$this->viewHelperVariableContainer->expects($this->once())->method('remove')->with('F3\Fluid\ViewHelpers\FormViewHelper', 'formName');
		$viewHelper->render('', array(), NULL, NULL, NULL, NULL);
	}
}
?>