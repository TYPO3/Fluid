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
 * Test for the Abstract Form view helper
 *
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class AbstractFormViewHelperTest extends \F3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

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

		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\FormViewHelper'), array('prefixFieldName', 'registerFieldNameForFormTokenGeneration'), array(), '', FALSE);
		$viewHelper->expects($this->any())->method('prefixFieldName')->with('theName')->will($this->returnValue('prefix[theName]'));
		$viewHelper->_set('persistenceManager', $mockPersistenceManager);

		$actualResult = $viewHelper->_call('renderHiddenIdentityField', $object, 'theName');
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

		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\FormViewHelper'), array('prefixFieldName', 'registerFieldNameForFormTokenGeneration'), array(), '', FALSE);
		$viewHelper->expects($this->any())->method('prefixFieldName')->with('theName')->will($this->returnValue('prefix[theName]'));
		$viewHelper->_set('persistenceManager', $mockPersistenceManager);

		$actualResult = $viewHelper->_call('renderHiddenIdentityField', $object, 'theName');
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

		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\FormViewHelper'), array('prefixFieldName', 'registerFieldNameForFormTokenGeneration'), array(), '', FALSE);
		$viewHelper->_set('persistenceManager', $mockPersistenceManager);

		$actualResult = $viewHelper->_call('renderHiddenIdentityField', $object, 'theName');
		$this->assertSame($expectedResult, $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function prefixFieldNameReturnsEmptyStringIfGivenFieldNameIsNULL() {
		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\Form\AbstractFormViewHelper'), array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$this->assertSame('', $viewHelper->_call('prefixFieldName', NULL));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function prefixFieldNameReturnsEmptyStringIfGivenFieldNameIsEmpty() {
		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\Form\AbstractFormViewHelper'), array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$this->assertSame('', $viewHelper->_call('prefixFieldName', ''));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function prefixFieldNameReturnsGivenFieldNameIfFieldNamePrefixIsEmpty() {
		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\Form\AbstractFormViewHelper'), array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$this->viewHelperVariableContainer->expects($this->once())->method('get')->with('F3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue(''));

		$this->assertSame('someFieldName', $viewHelper->_call('prefixFieldName', 'someFieldName'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function prefixFieldNamePrefixesGivenFieldNameWithFieldNamePrefix() {
		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\Form\AbstractFormViewHelper'), array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$this->viewHelperVariableContainer->expects($this->once())->method('get')->with('F3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue('somePrefix'));

		$this->assertSame('somePrefix[someFieldName]', $viewHelper->_call('prefixFieldName', 'someFieldName'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function prefixFieldNamePreservesSquareBracketsOfFieldName() {
		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\Form\AbstractFormViewHelper'), array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$this->viewHelperVariableContainer->expects($this->once())->method('get')->with('F3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue('somePrefix[foo]'));

		$this->assertSame('somePrefix[foo][someFieldName][bar]', $viewHelper->_call('prefixFieldName', 'someFieldName[bar]'));
	}
}

?>