<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers\Form;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

require_once(__DIR__ . '/../ViewHelperBaseTestcase.php');

/**
 * Test for the Abstract Form view helper
 *
 */
class AbstractFormViewHelperTest extends \TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * @test
	 */
	public function renderHiddenIdentityFieldReturnsAHiddenInputFieldContainingTheObjectsUUID() {
		$className = 'Object' . uniqid();
		$fullClassName = 'TYPO3\\Fluid\\ViewHelpers\\Form\\' . $className;
		eval('namespace TYPO3\\Fluid\\ViewHelpers\\Form; class ' . $className . ' {
			public function __clone() {}
		}');
		$object = $this->getMock($fullClassName);

		$mockPersistenceManager = $this->getMock('TYPO3\Flow\Persistence\PersistenceManagerInterface');
		$mockPersistenceManager->expects($this->once())->method('getIdentifierByObject')->with($object)->will($this->returnValue('123'));

		$expectedResult = chr(10) . '<input type="hidden" name="prefix[theName][__identity]" value="123" />' . chr(10);

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('prefixFieldName', 'registerFieldNameForFormTokenGeneration'), array(), '', FALSE);
		$viewHelper->expects($this->any())->method('prefixFieldName')->with('theName')->will($this->returnValue('prefix[theName]'));
		$viewHelper->_set('persistenceManager', $mockPersistenceManager);

		$actualResult = $viewHelper->_call('renderHiddenIdentityField', $object, 'theName');
		$this->assertSame($expectedResult, $actualResult);
	}

	/**
	 * @test
	 */
	public function renderHiddenIdentityFieldReturnsAHiddenInputFieldIfObjectIsNewButAClone() {
		$className = 'Object' . uniqid();
		$fullClassName = 'TYPO3\\Fluid\\ViewHelpers\\Form\\' . $className;
		eval('namespace TYPO3\\Fluid\\ViewHelpers\\Form; class ' . $className . ' {
			public function __clone() {}
		}');
		$object = $this->getMock($fullClassName);

		$mockPersistenceManager = $this->getMock('TYPO3\Flow\Persistence\PersistenceManagerInterface');
		$mockPersistenceManager->expects($this->once())->method('getIdentifierByObject')->with($object)->will($this->returnValue('123'));

		$expectedResult = chr(10) . '<input type="hidden" name="prefix[theName][__identity]" value="123" />' . chr(10);

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('prefixFieldName', 'registerFieldNameForFormTokenGeneration'), array(), '', FALSE);
		$viewHelper->expects($this->any())->method('prefixFieldName')->with('theName')->will($this->returnValue('prefix[theName]'));
		$viewHelper->_set('persistenceManager', $mockPersistenceManager);

		$actualResult = $viewHelper->_call('renderHiddenIdentityField', $object, 'theName');
		$this->assertSame($expectedResult, $actualResult);
	}

	/**
	 * @test
	 */
	public function renderHiddenIdentityFieldReturnsACommentIfTheObjectIsWithoutIdentity() {
		$className = 'Object' . uniqid();
		$fullClassName = 'TYPO3\\Fluid\\ViewHelpers\\Form\\' . $className;
		eval('namespace TYPO3\\Fluid\\ViewHelpers\\Form; class ' . $className . ' {
			public function __clone() {}
		}');
		$object = $this->getMock($fullClassName);

		$mockPersistenceManager = $this->getMock('TYPO3\Flow\Persistence\PersistenceManagerInterface');
		$mockPersistenceManager->expects($this->once())->method('getIdentifierByObject')->with($object)->will($this->returnValue(NULL));

		$expectedResult = chr(10) . '<!-- Object of type ' . get_class($object) . ' is without identity -->' . chr(10);

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('prefixFieldName', 'registerFieldNameForFormTokenGeneration'), array(), '', FALSE);
		$viewHelper->_set('persistenceManager', $mockPersistenceManager);

		$actualResult = $viewHelper->_call('renderHiddenIdentityField', $object, 'theName');
		$this->assertSame($expectedResult, $actualResult);
	}

	/**
	 * @test
	 */
	public function prefixFieldNameReturnsEmptyStringIfGivenFieldNameIsNULL() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormViewHelper', array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$this->assertSame('', $viewHelper->_call('prefixFieldName', NULL));
	}

	/**
	 * @test
	 */
	public function prefixFieldNameReturnsEmptyStringIfGivenFieldNameIsEmpty() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormViewHelper', array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$this->assertSame('', $viewHelper->_call('prefixFieldName', ''));
	}

	/**
	 * @test
	 */
	public function prefixFieldNameReturnsGivenFieldNameIfFieldNamePrefixIsEmpty() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormViewHelper', array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$this->viewHelperVariableContainer->expects($this->any())->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->once())->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue(''));

		$this->assertSame('someFieldName', $viewHelper->_call('prefixFieldName', 'someFieldName'));
	}

	/**
	 * @test
	 */
	public function prefixFieldNamePrefixesGivenFieldNameWithFieldNamePrefix() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormViewHelper', array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$this->viewHelperVariableContainer->expects($this->any())->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->once())->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue('somePrefix'));

		$this->assertSame('somePrefix[someFieldName]', $viewHelper->_call('prefixFieldName', 'someFieldName'));
	}

	/**
	 * @test
	 */
	public function prefixFieldNamePreservesSquareBracketsOfFieldName() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormViewHelper', array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$this->viewHelperVariableContainer->expects($this->any())->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->once())->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue('somePrefix[foo]'));

		$this->assertSame('somePrefix[foo][someFieldName][bar]', $viewHelper->_call('prefixFieldName', 'someFieldName[bar]'));
	}
}
