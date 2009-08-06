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
 */

require_once(__DIR__ . '/ViewHelperBaseTestcase.php');

/**
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
		$mockBackend->expects($this->once())->method('getIdentifierByObject')->with($object)->will($this->returnValue('123'));

		$mockPersistenceManager = $this->getMock('F3\FLOW3\Persistence\ManagerInterface');
		$mockPersistenceManager->expects($this->any())->method('getBackend')->will($this->returnValue($mockBackend));

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

		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\FormViewHelper'), array('renderChildren', 'renderHiddenIdentityField', 'renderHiddenReferrerFields'), array(), '', FALSE);
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

		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\FormViewHelper'), array('renderChildren', 'renderHiddenIdentityField', 'renderHiddenReferrerFields'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$viewHelper->setArguments(new \F3\Fluid\Core\ViewHelper\Arguments(array('name' => $formName)));

		$this->viewHelperVariableContainer->expects($this->once())->method('add')->with('F3\Fluid\ViewHelpers\FormViewHelper', 'formName', $formName);
		$this->viewHelperVariableContainer->expects($this->once())->method('remove')->with('F3\Fluid\ViewHelpers\FormViewHelper', 'formName');
		$viewHelper->render('', array(), NULL, NULL, NULL, NULL);
	}

	/**
	 * @test
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function renderCallsRenderHiddenReferrerFields() {
		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\FormViewHelper'), array('renderChildren', 'renderHiddenReferrerFields'), array(), '', FALSE);
		$viewHelper->expects($this->once())->method('renderHiddenReferrerFields');
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$viewHelper->render('', array(), NULL, NULL, NULL, NULL);
	}

	/**
	 * @test
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function renderHiddenReferrerFieldsAddCurrentControllerAndActionAsHiddenFields() {
		$mockRequest = $this->getMock('F3\FLOW3\MVC\RequestInterface');
		$this->controllerContext->expects($this->atLeastOnce())->method('getRequest')->will($this->returnValue($mockRequest));

		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\FormViewHelper'), array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$mockRequest->expects($this->atLeastOnce())->method('getControllerPackageKey')->will($this->returnValue('packageKey'));
		$mockRequest->expects($this->atLeastOnce())->method('getControllerSubpackageKey')->will($this->returnValue('subpackageKey'));
		$mockRequest->expects($this->atLeastOnce())->method('getControllerName')->will($this->returnValue('controllerName'));
		$mockRequest->expects($this->atLeastOnce())->method('getControllerActionName')->will($this->returnValue('controllerActionName'));

		$hiddenFields = $viewHelper->_call('renderHiddenReferrerFields');
		$expectedResult = PHP_EOL . '<input type="hidden" name="__referrer[packageKey]" value="packageKey" />' . PHP_EOL .
			'<input type="hidden" name="__referrer[subpackageKey]" value="subpackageKey" />' . PHP_EOL .
			'<input type="hidden" name="__referrer[controllerName]" value="controllerName" />' . PHP_EOL .
			'<input type="hidden" name="__referrer[actionName]" value="controllerActionName" />';
		$this->assertEquals($expectedResult, $hiddenFields);
	}
}
?>