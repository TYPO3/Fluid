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
		$className = 'Object' . uniqid();
		$fullClassName = 'F3\\Fluid\\ViewHelpers\\Form\\' . $className;
		eval('namespace F3\\Fluid\\ViewHelpers\\Form; class ' . $className . ' implements \\F3\\FLOW3\\Persistence\\Aspect\\DirtyMonitoringInterface {
			public function FLOW3_Persistence_isNew() { return FALSE; }
			public function FLOW3_Persistence_isClone() { return FALSE; }
			public function FLOW3_Persistence_isDirty($propertyName) {}
			public function FLOW3_Persistence_memorizeCleanState($propertyName = NULL) {}
			public function FLOW3_AOP_Proxy_getProperty($name) {}
			public function FLOW3_AOP_Proxy_getProxyTargetClassName() {}
			public function __clone() {}
		}');
		$object = $this->getMock($fullClassName);

		$mockBackend = $this->getMock('F3\FLOW3\Persistence\BackendInterface');
		$mockBackend->expects($this->once())->method('getIdentifierByObject')->with($object)->will($this->returnValue('123'));

		$mockPersistenceManager = $this->getMock('F3\FLOW3\Persistence\ManagerInterface');
		$mockPersistenceManager->expects($this->any())->method('getBackend')->will($this->returnValue($mockBackend));

		$expectedResult = chr(10) . '<input type="hidden" name="prefix[theName][__identity]" value="123" />' . chr(10);

		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\FormViewHelper'), array('prefixFieldName'), array(), '', FALSE);
		$viewHelper->setArguments(new \F3\Fluid\Core\ViewHelper\Arguments(array('name' => 'theName', 'object' => $object)));
		$viewHelper->expects($this->any())->method('prefixFieldName')->with('theName')->will($this->returnValue('prefix[theName]'));
		$viewHelper->_set('persistenceManager', $mockPersistenceManager);

		$actualResult = $viewHelper->_call('renderHiddenIdentityField');
		$this->assertSame($expectedResult, $actualResult);
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function renderHiddenIdentityFieldReturnsAHiddenInputFieldIfObjectIsNewButAClone() {
		$className = 'Object' . uniqid();
		$fullClassName = 'F3\\Fluid\\ViewHelpers\\Form\\' . $className;
		eval('namespace F3\\Fluid\\ViewHelpers\\Form; class ' . $className . ' implements \\F3\\FLOW3\\Persistence\\Aspect\\DirtyMonitoringInterface {
			public function FLOW3_Persistence_isNew() { return TRUE; }
			public function FLOW3_Persistence_isClone() { return TRUE; }
			public function FLOW3_Persistence_isDirty($propertyName) {}
			public function FLOW3_Persistence_memorizeCleanState($propertyName = NULL) {}
			public function FLOW3_AOP_Proxy_getProperty($name) {}
			public function FLOW3_AOP_Proxy_getProxyTargetClassName() {}
			public function __clone() {}
		}');
		$object = $this->getMock($fullClassName);

		$mockBackend = $this->getMock('F3\FLOW3\Persistence\BackendInterface');
		$mockBackend->expects($this->once())->method('getIdentifierByObject')->with($object)->will($this->returnValue('123'));

		$mockPersistenceManager = $this->getMock('F3\FLOW3\Persistence\ManagerInterface');
		$mockPersistenceManager->expects($this->any())->method('getBackend')->will($this->returnValue($mockBackend));

		$expectedResult = chr(10) . '<input type="hidden" name="prefix[theName][__identity]" value="123" />' . chr(10);

		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\FormViewHelper'), array('prefixFieldName'), array(), '', FALSE);
		$viewHelper->setArguments(new \F3\Fluid\Core\ViewHelper\Arguments(array('name' => 'theName', 'object' => $object)));
		$viewHelper->expects($this->any())->method('prefixFieldName')->with('theName')->will($this->returnValue('prefix[theName]'));
		$viewHelper->_set('persistenceManager', $mockPersistenceManager);

		$actualResult = $viewHelper->_call('renderHiddenIdentityField');
		$this->assertSame($expectedResult, $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderHiddenIdentityFieldReturnsACommentIfTheObjectIsWithoutIdentity() {
		$className = 'Object' . uniqid();
		$fullClassName = 'F3\\Fluid\\ViewHelpers\\Form\\' . $className;
		eval('namespace F3\\Fluid\\ViewHelpers\\Form; class ' . $className . ' implements \\F3\\FLOW3\\Persistence\\Aspect\\DirtyMonitoringInterface {
			public function FLOW3_Persistence_isNew() { return FALSE; }
			public function FLOW3_Persistence_isClone() { return FALSE; }
			public function FLOW3_Persistence_isDirty($propertyName) {}
			public function FLOW3_Persistence_memorizeCleanState($propertyName = NULL) {}
			public function FLOW3_AOP_Proxy_getProperty($name) {}
			public function FLOW3_AOP_Proxy_getProxyTargetClassName() {}
			public function __clone() {}
		}');
		$object = $this->getMock($fullClassName);

		$mockBackend = $this->getMock('F3\FLOW3\Persistence\BackendInterface');
		$mockBackend->expects($this->once())->method('getIdentifierByObject')->with($object)->will($this->returnValue(NULL));

		$mockPersistenceManager = $this->getMock('F3\FLOW3\Persistence\ManagerInterface');
		$mockPersistenceManager->expects($this->any())->method('getBackend')->will($this->returnValue($mockBackend));

		$expectedResult = chr(10) . '<!-- Object of type ' . get_class($object) . ' is without identity -->' . chr(10);

		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\FormViewHelper'), array('prefixFieldName'), array(), '', FALSE);
		$viewHelper->setArguments(new \F3\Fluid\Core\ViewHelper\Arguments(array('name' => 'theName', 'object' => $object)));
		$viewHelper->_set('persistenceManager', $mockPersistenceManager);

		$actualResult = $viewHelper->_call('renderHiddenIdentityField');
		$this->assertSame($expectedResult, $actualResult);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function renderAddsObjectToTemplateVariableContainer() {
		$formObject = new \stdClass();

		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\FormViewHelper'), array('renderChildren', 'renderHiddenIdentityField', 'renderHiddenReferrerFields', 'addFormNameToViewHelperVariableContainer', 'addFieldNamePrefixToViewHelperVariableContainer', 'removeFormNameFromViewHelperVariableContainer', 'removeFieldNamePrefixFromViewHelperVariableContainer'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$viewHelper->setArguments(new \F3\Fluid\Core\ViewHelper\Arguments(array('object' => $formObject)));
		$this->viewHelperVariableContainer->expects($this->once())->method('add')->with('F3\Fluid\ViewHelpers\FormViewHelper', 'formObject', $formObject);
		$this->viewHelperVariableContainer->expects($this->once())->method('remove')->with('F3\Fluid\ViewHelpers\FormViewHelper', 'formObject');
		$viewHelper->render();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function renderAddsFormNameToTemplateVariableContainer() {
		$formName = 'someFormName';

		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\FormViewHelper'), array('renderChildren', 'renderHiddenIdentityField', 'renderHiddenReferrerFields', 'addFormObjectToViewHelperVariableContainer', 'addFieldNamePrefixToViewHelperVariableContainer', 'removeFormObjectFromViewHelperVariableContainer', 'removeFieldNamePrefixFromViewHelperVariableContainer'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$viewHelper->setArguments(new \F3\Fluid\Core\ViewHelper\Arguments(array('name' => $formName)));

		$this->viewHelperVariableContainer->expects($this->once())->method('add')->with('F3\Fluid\ViewHelpers\FormViewHelper', 'formName', $formName);
		$this->viewHelperVariableContainer->expects($this->once())->method('remove')->with('F3\Fluid\ViewHelpers\FormViewHelper', 'formName');
		$viewHelper->render();
	}

	/**
	 * @test
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function renderCallsRenderHiddenReferrerFields() {
		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\FormViewHelper'), array('renderChildren', 'renderHiddenReferrerFields'), array(), '', FALSE);
		$viewHelper->expects($this->once())->method('renderHiddenReferrerFields');
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$viewHelper->render();
	}

	/**
	 * @test
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function renderHiddenReferrerFieldsAddCurrentControllerAndActionAsHiddenFields() {
		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\FormViewHelper'), array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$this->request->expects($this->atLeastOnce())->method('getControllerPackageKey')->will($this->returnValue('packageKey'));
		$this->request->expects($this->atLeastOnce())->method('getControllerSubpackageKey')->will($this->returnValue('subpackageKey'));
		$this->request->expects($this->atLeastOnce())->method('getControllerName')->will($this->returnValue('controllerName'));
		$this->request->expects($this->atLeastOnce())->method('getControllerActionName')->will($this->returnValue('controllerActionName'));

		$hiddenFields = $viewHelper->_call('renderHiddenReferrerFields');
		$expectedResult = chr(10) . '<input type="hidden" name="__referrer[packageKey]" value="packageKey" />' . chr(10) .
			'<input type="hidden" name="__referrer[subpackageKey]" value="subpackageKey" />' . chr(10) .
			'<input type="hidden" name="__referrer[controllerName]" value="controllerName" />' . chr(10) .
			'<input type="hidden" name="__referrer[actionName]" value="controllerActionName" />' . chr(10);
		$this->assertEquals($expectedResult, $hiddenFields);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderAddsSpecifiedPrefixToTemplateVariableContainer() {
		$prefix = 'somePrefix';

		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\FormViewHelper'), array('renderChildren', 'renderHiddenIdentityField', 'renderHiddenReferrerFields'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$viewHelper->setArguments(new \F3\Fluid\Core\ViewHelper\Arguments(array('fieldNamePrefix' => $prefix)));

		$this->viewHelperVariableContainer->expects($this->once())->method('add')->with('F3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix', $prefix);
		$this->viewHelperVariableContainer->expects($this->once())->method('remove')->with('F3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix');
		$viewHelper->render();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderAddsNoFieldNamePrefixToTemplateVariableContainerIfNoPrefixIsSpecified() {
		$expectedPrefix = '';

		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\FormViewHelper'), array('renderChildren', 'renderHiddenIdentityField', 'renderHiddenReferrerFields'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$this->viewHelperVariableContainer->expects($this->once())->method('add')->with('F3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix', $expectedPrefix);
		$this->viewHelperVariableContainer->expects($this->once())->method('remove')->with('F3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix');
		$viewHelper->render();
	}

}
?>