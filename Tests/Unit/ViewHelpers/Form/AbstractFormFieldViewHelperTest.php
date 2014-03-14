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

require_once(__DIR__ . '/FormFieldViewHelperBaseTestcase.php');

/**
 * Test for the Abstract Form view helper
 */
class AbstractFormFieldViewHelperTest extends \TYPO3\Fluid\Tests\Unit\ViewHelpers\Form\FormFieldViewHelperBaseTestcase {

	/**
	 * @test
	 */
	public function ifAnAttributeValueIsAnObjectMaintainedByThePersistenceManagerItIsConvertedToAUUID() {
		$mockPersistenceManager = $this->getMock('TYPO3\Flow\Persistence\PersistenceManagerInterface');
		$mockPersistenceManager->expects($this->any())->method('getIdentifierByObject')->will($this->returnValue('6f487e40-4483-11de-8a39-0800200c9a66'));

		$className = 'Object' . uniqid();
		$fullClassName = 'TYPO3\\Fluid\\ViewHelpers\\Form\\' . $className;
		eval('namespace TYPO3\\Fluid\\ViewHelpers\\Form; class ' . $className . ' {
			public function __clone() {}
		}');
		$object = $this->getMock($fullClassName);
		$object->expects($this->any())->method('Flow_Persistence_isNew')->will($this->returnValue(FALSE));

		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);
		$formViewHelper->injectPersistenceManager($mockPersistenceManager);

		$arguments = array('name' => 'foo', 'value' => $object, 'property' => NULL);
		$formViewHelper->_set('arguments', $arguments);
		$formViewHelper->expects($this->any())->method('isObjectAccessorMode')->will($this->returnValue(FALSE));

		$this->assertSame('foo[__identity]', $formViewHelper->_call('getName'));
		$this->assertSame('6f487e40-4483-11de-8a39-0800200c9a66', $formViewHelper->_call('getValue'));
	}

	/**
	 * @test
	 */
	public function getNameBuildsNameFromFieldNamePrefixFormObjectNameAndPropertyIfInObjectAccessorMode() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('isObjectAccessorMode'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);

		$formViewHelper->expects($this->any())->method('isObjectAccessorMode')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(0))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName')->will($this->returnValue('myObjectName'));
		$this->viewHelperVariableContainer->expects($this->at(1))->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(2))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue('formPrefix'));

		$arguments = array('name' => 'fieldName', 'value' => 'fieldValue', 'property' => 'bla');
		$formViewHelper->_set('arguments', $arguments);
		$expected = 'formPrefix[myObjectName][bla]';
		$actual = $formViewHelper->_call('getName');
		$this->assertSame($expected, $actual);
	}

	/**
	 * @test
	 */
	public function getNameBuildsNameFromFieldNamePrefixFormObjectNameAndHierarchicalPropertyIfInObjectAccessorMode() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('isObjectAccessorMode'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);

		$formViewHelper->expects($this->any())->method('isObjectAccessorMode')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(0))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName')->will($this->returnValue('myObjectName'));
		$this->viewHelperVariableContainer->expects($this->at(1))->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(2))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue('formPrefix'));

		$arguments = array('name' => 'fieldName', 'value' => 'fieldValue', 'property' => 'bla.blubb');
		$formViewHelper->_set('arguments', $arguments);
		$expected = 'formPrefix[myObjectName][bla][blubb]';
		$actual = $formViewHelper->_call('getName');
		$this->assertSame($expected, $actual);
	}

	/**
	 * @test
	 */
	public function getNameBuildsNameFromFieldNamePrefixAndPropertyIfInObjectAccessorModeAndNoFormObjectNameIsSpecified() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('isObjectAccessorMode'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);

		$formViewHelper->expects($this->any())->method('isObjectAccessorMode')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(0))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName')->will($this->returnValue(NULL));
		$this->viewHelperVariableContainer->expects($this->at(1))->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(2))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue('formPrefix'));

		$arguments = array('name' => 'fieldName', 'value' => 'fieldValue', 'property' => 'bla');
		$formViewHelper->_set('arguments', $arguments);
		$expected = 'formPrefix[bla]';
		$actual = $formViewHelper->_call('getName');
		$this->assertSame($expected, $actual);
	}

	/**
	 * @test
	 */
	public function getNameResolvesPropertyPathIfInObjectAccessorModeAndNoFormObjectNameIsSpecified() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('isObjectAccessorMode'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);

		$formViewHelper->expects($this->any())->method('isObjectAccessorMode')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(0))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName')->will($this->returnValue(NULL));
		$this->viewHelperVariableContainer->expects($this->at(1))->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(2))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue('formPrefix'));

		$arguments = array('name' => 'fieldName', 'value' => 'fieldValue', 'property' => 'some.property.path');
		$formViewHelper->_set('arguments', $arguments);
		$expected = 'formPrefix[some][property][path]';
		$actual = $formViewHelper->_call('getName');
		$this->assertSame($expected, $actual);
	}

	/**
	 * @test
	 */
	public function getNameBuildsNameFromFieldNamePrefixAndFieldNameIfNotInObjectAccessorMode() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('isObjectAccessorMode'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);

		$formViewHelper->expects($this->any())->method('isObjectAccessorMode')->will($this->returnValue(FALSE));
		$this->viewHelperVariableContainer->expects($this->at(0))->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(1))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix')->will($this->returnValue('formPrefix'));

		$arguments = array('name' => 'fieldName', 'value' => 'fieldValue', 'property' => 'bla');
		$formViewHelper->_set('arguments', $arguments);
		$expected = 'formPrefix[fieldName]';
		$actual = $formViewHelper->_call('getName');
		$this->assertSame($expected, $actual);
	}


	/**
	 * This is in order to proof that object access behaves similar to a plain array with the same structure
	 */
	public function formObjectVariantsDataProvider() {
		$className = 'test_' . uniqid();
		$mockObject = eval('
			class ' . $className . ' {
				public function getSomething() {
					return "MyString";
				}
				public function getValue() {
					return new ' . $className . ';
				}
			}
			return new ' . $className . ';
		');
		return array(
			array($mockObject),
			array('value' => array('value' => array('something' => 'MyString')))
		);
	}

	/**
	 * @test
	 * @dataProvider formObjectVariantsDataProvider
	 */
	public function getValueBuildsValueFromPropertyAndFormObjectIfInObjectAccessorMode($formObject) {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('isObjectAccessorMode', 'addAdditionalIdentityPropertiesIfNeeded'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);

		$formViewHelper->expects($this->any())->method('isObjectAccessorMode')->will($this->returnValue(TRUE));
		$formViewHelper->expects($this->once())->method('addAdditionalIdentityPropertiesIfNeeded');
		$this->viewHelperVariableContainer->expects($this->atLeastOnce())->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject')->will($this->returnValue($formObject));
		$this->viewHelperVariableContainer->expects($this->atLeastOnce())->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject')->will($this->returnValue(TRUE));

		$arguments = array('name' => NULL, 'value' => NULL, 'property' => 'value.something');
		$formViewHelper->_set('arguments', $arguments);
		$expected = 'MyString';
		$actual = $formViewHelper->_call('getValue');
		$this->assertSame($expected, $actual);
	}

	/**
	 * @test
	 */
	public function getValueReturnsNullIfNotInObjectAccessorModeAndValueArgumentIsNoSet() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('isObjectAccessorMode'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);
		$formViewHelper->expects($this->any())->method('isObjectAccessorMode')->will($this->returnValue(FALSE));

		$mockArguments = array();
		$formViewHelper->_set('arguments', $mockArguments);

		$this->assertNull($formViewHelper->_call('getValue'));
	}

	/**
	 * @test
	 */
	public function getValueReturnsValueArgumentIfSpecified() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('isObjectAccessorMode'), array(), '', FALSE);
		$formViewHelper->expects($this->any())->method('isObjectAccessorMode')->will($this->returnValue(FALSE));
		$this->injectDependenciesIntoViewHelper($formViewHelper);

		$mockArguments = array('value' => 'someValue');
		$formViewHelper->_set('arguments', $mockArguments);

		$this->assertEquals('someValue', $formViewHelper->_call('getValue'));
	}

	/**
	 * @test
	 */
	public function getValueConvertsObjectsToIdentifiersByDefault() {
		$mockObject = $this->getMock('stdClass');

		$mockPersistenceManager = $this->getMock('TYPO3\Flow\Persistence\PersistenceManagerInterface');
		$mockPersistenceManager->expects($this->atLeastOnce())->method('getIdentifierByObject')->with($mockObject)->will($this->returnValue('6f487e40-4483-11de-8a39-0800200c9a66'));

		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('isObjectAccessorMode'), array(), '', FALSE);
		$formViewHelper->expects($this->any())->method('isObjectAccessorMode')->will($this->returnValue(FALSE));
		$this->injectDependenciesIntoViewHelper($formViewHelper);
		$formViewHelper->injectPersistenceManager($mockPersistenceManager);

		$mockArguments = array('value' => $mockObject);
		$formViewHelper->_set('arguments', $mockArguments);

		$this->assertSame('6f487e40-4483-11de-8a39-0800200c9a66', $formViewHelper->_call('getValue'));
	}

	/**
	 * @test
	 */
	public function getValueDoesNotConvertObjectsIfConvertObjectsIsFalse() {
		$mockObject = $this->getMock('stdClass');

		$mockPersistenceManager = $this->getMock('TYPO3\Flow\Persistence\PersistenceManagerInterface');
		$mockPersistenceManager->expects($this->any())->method('getIdentifierByObject')->will($this->returnValue('6f487e40-4483-11de-8a39-0800200c9a66'));

		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('isObjectAccessorMode'), array(), '', FALSE);
		$formViewHelper->expects($this->any())->method('isObjectAccessorMode')->will($this->returnValue(FALSE));
		$this->injectDependenciesIntoViewHelper($formViewHelper);
		$formViewHelper->injectPersistenceManager($mockPersistenceManager);

		$mockArguments = array('value' => $mockObject);
		$formViewHelper->_set('arguments', $mockArguments);

		$this->assertSame($mockObject, $formViewHelper->_call('getValue', FALSE));
	}

	/**
	 * @test
	 */
	public function isObjectAccessorModeReturnsTrueIfPropertyIsSetAndFormObjectIsGiven() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);

		$this->viewHelperVariableContainer->expects($this->once())->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName')->will($this->returnValue(TRUE));

		$formViewHelper->_set('arguments', array('name' => NULL, 'value' => NULL, 'property' => 'bla'));
		$this->assertTrue($formViewHelper->_call('isObjectAccessorMode'));

		$formViewHelper->_set('arguments', array('name' => NULL, 'value' => NULL, 'property' => NULL));
		$this->assertFalse($formViewHelper->_call('isObjectAccessorMode'));
	}

	/**
	 * @test
	 */
	public function getMappingResultsForPropertyReturnsErrorsFromRequestIfPropertyIsSet() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('isObjectAccessorMode'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);
		$formViewHelper->expects($this->once())->method('isObjectAccessorMode')->will($this->returnValue(TRUE));
		$formViewHelper->_set('arguments', array('property' => 'bar'));
		$this->viewHelperVariableContainer->expects($this->any())->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName')->will($this->returnValue('foo'));

		$expectedResult = $this->getMock('TYPO3\Flow\Error\Result');

		$mockFormResult = $this->getMock('TYPO3\Flow\Error\Result');
		$mockFormResult->expects($this->once())->method('forProperty')->with('bar')->will($this->returnValue($expectedResult));

		$mockResult = $this->getMock('TYPO3\Flow\Error\Result');
		$mockResult->expects($this->once())->method('forProperty')->with('foo')->will($this->returnValue($mockFormResult));

		$this->request->expects($this->once())->method('getInternalArgument')->with('__submittedArgumentValidationResults')->will($this->returnValue($mockResult));

		$actualResult = $formViewHelper->_call('getMappingResultsForProperty');
		$this->assertEquals($expectedResult, $actualResult);
	}

	/**
	 * @test
	 */
	public function getMappingResultsForPropertyReturnsEmptyResultIfPropertyIsNotSet() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('hasArgument'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);
		$formViewHelper->expects($this->once())->method('hasArgument')->with('property')->will($this->returnValue(FALSE));

		$actualResult = $formViewHelper->_call('getMappingResultsForProperty');
		$this->assertInstanceOf('TYPO3\Flow\Error\Result', $actualResult);
		$this->assertEmpty($actualResult->getFlattenedErrors());
	}


	/**
	 * @test
	 */
	public function setErrorClassAttributeDoesNotSetClassAttributeIfNoErrorOccurred() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('hasArgument', 'getErrorsForProperty'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);

		$this->tagBuilder->expects($this->never())->method('addAttribute');

		$formViewHelper->_call('setErrorClassAttribute');
	}

	/**
	 * @test
	 */
	public function setErrorClassAttributeSetsErrorClassIfAnErrorOccurred() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('hasArgument', 'getMappingResultsForProperty'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);
		$formViewHelper->expects($this->at(0))->method('hasArgument')->with('class')->will($this->returnValue(FALSE));
		$formViewHelper->expects($this->at(2))->method('hasArgument')->with('errorClass')->will($this->returnValue(FALSE));

		$mockResult = $this->getMock('TYPO3\Flow\Error\Result');
		$mockResult->expects($this->atLeastOnce())->method('hasErrors')->will($this->returnValue(TRUE));
		$formViewHelper->expects($this->once())->method('getMappingResultsForProperty')->will($this->returnValue($mockResult));

		$this->tagBuilder->expects($this->once())->method('addAttribute')->with('class', 'error');

		$formViewHelper->_call('setErrorClassAttribute');
	}

	/**
	 * @test
	 */
	public function setErrorClassAttributeAppendsErrorClassToExistingClassesIfAnErrorOccurred() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('hasArgument', 'getMappingResultsForProperty'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);
		$formViewHelper->expects($this->at(0))->method('hasArgument')->with('class')->will($this->returnValue(TRUE));
		$formViewHelper->expects($this->at(2))->method('hasArgument')->with('errorClass')->will($this->returnValue(FALSE));
		$formViewHelper->_set('arguments', array('class' => 'default classes'));

		$mockResult = $this->getMock('TYPO3\Flow\Error\Result');
		$mockResult->expects($this->atLeastOnce())->method('hasErrors')->will($this->returnValue(TRUE));
		$formViewHelper->expects($this->once())->method('getMappingResultsForProperty')->will($this->returnValue($mockResult));

		$this->tagBuilder->expects($this->once())->method('addAttribute')->with('class', 'default classes error');

		$formViewHelper->_call('setErrorClassAttribute');
	}

	/**
	 * @test
	 */
	public function setErrorClassAttributeSetsCustomErrorClassIfAnErrorOccurred() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('hasArgument', 'getMappingResultsForProperty'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);
		$formViewHelper->expects($this->at(0))->method('hasArgument')->with('class')->will($this->returnValue(FALSE));
		$formViewHelper->expects($this->at(2))->method('hasArgument')->with('errorClass')->will($this->returnValue(TRUE));
		$formViewHelper->_set('arguments', array('errorClass' => 'custom-error-class'));

		$mockResult = $this->getMock('TYPO3\Flow\Error\Result');
		$mockResult->expects($this->atLeastOnce())->method('hasErrors')->will($this->returnValue(TRUE));
		$formViewHelper->expects($this->once())->method('getMappingResultsForProperty')->will($this->returnValue($mockResult));

		$this->tagBuilder->expects($this->once())->method('addAttribute')->with('class', 'custom-error-class');

		$formViewHelper->_call('setErrorClassAttribute');
	}

	/**
	 * @test
	 */
	public function setErrorClassAttributeAppendsCustomErrorClassIfAnErrorOccurred() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('hasArgument', 'getMappingResultsForProperty'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);
		$formViewHelper->expects($this->at(0))->method('hasArgument')->with('class')->will($this->returnValue(TRUE));
		$formViewHelper->expects($this->at(2))->method('hasArgument')->with('errorClass')->will($this->returnValue(TRUE));
		$formViewHelper->_set('arguments', array('class' => 'default classes', 'errorClass' => 'custom-error-class'));

		$mockResult = $this->getMock('TYPO3\Flow\Error\Result');
		$mockResult->expects($this->atLeastOnce())->method('hasErrors')->will($this->returnValue(TRUE));
		$formViewHelper->expects($this->once())->method('getMappingResultsForProperty')->will($this->returnValue($mockResult));

		$this->tagBuilder->expects($this->once())->method('addAttribute')->with('class', 'default classes custom-error-class');

		$formViewHelper->_call('setErrorClassAttribute');
	}

	/**
	 * @test
	 */
	public function addAdditionalIdentityPropertiesIfNeededDoesNotTryToAccessObjectPropertiesIfFormObjectIsNotSet() {
		$formFieldViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('renderHiddenIdentityField'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formFieldViewHelper);
		$arguments = array('property' => 'some.property.name');

		$this->viewHelperVariableContainer->expects($this->atLeastOnce())->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject')->will($this->returnValue(FALSE));

		$formFieldViewHelper->expects($this->never())->method('renderHiddenIdentityField');
		$formFieldViewHelper->_set('arguments', $arguments);
		$formFieldViewHelper->_call('addAdditionalIdentityPropertiesIfNeeded');
	}

	/**
	 * @test
	 */
	public function addAdditionalIdentityPropertiesIfNeededDoesNotCreateAnythingIfPropertyIsWithoutDot() {
		$formFieldViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('renderHiddenIdentityField'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formFieldViewHelper);
		$arguments = array('property' => 'simple');

		$this->viewHelperVariableContainer->expects($this->atLeastOnce())->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject')->will($this->returnValue(TRUE));

		$formFieldViewHelper->expects($this->never())->method('renderHiddenIdentityField');
		$formFieldViewHelper->_set('arguments', $arguments);
		$formFieldViewHelper->_call('addAdditionalIdentityPropertiesIfNeeded');
	}

	/**
	 * @test
	 */
	public function addAdditionalIdentityPropertiesIfNeededCallsRenderIdentityFieldWithTheRightParameters() {
		$className = 'test_' . uniqid();
		$mockFormObject = eval('
			class ' . $className . ' {
				public function getSomething() {
					return "MyString";
				}
				public function getValue() {
					return new ' . $className . ';
				}
			}
			return new ' . $className . ';
		');
		$property = 'value.something';
		$objectName = 'myObject';
		$expectedProperty = 'myObject[value]';

		$formFieldViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('renderHiddenIdentityField'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formFieldViewHelper);
		$arguments = array('property' => $property);
		$formFieldViewHelper->_set('arguments', $arguments);
		$this->viewHelperVariableContainer->expects($this->at(0))->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(1))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject')->will($this->returnValue($mockFormObject));
		$this->viewHelperVariableContainer->expects($this->at(2))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName')->will($this->returnValue($objectName));

		$formFieldViewHelper->expects($this->once())->method('renderHiddenIdentityField')->with($mockFormObject, $expectedProperty);

		$formFieldViewHelper->_call('addAdditionalIdentityPropertiesIfNeeded');
	}

	/**
	 * @test
	 */
	public function addAdditionalIdentityPropertiesIfNeededCallsRenderIdentityFieldWithTheRightParametersWithMoreHierarchyLevels() {
		$className = 'test_' . uniqid();
		$mockFormObject = eval('
			class ' . $className . ' {
				public function getSomething() {
					return "MyString";
				}
				public function getValue() {
					return new ' . $className . ';
				}
			}
			return new ' . $className . ';
		');
		$property = 'value.value.something';
		$objectName = 'myObject';
		$expectedProperty1 = 'myObject[value]';
		$expectedProperty2 = 'myObject[value][value]';

		$formFieldViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('renderHiddenIdentityField'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formFieldViewHelper);
		$arguments = array('property' => $property);
		$formFieldViewHelper->_set('arguments', $arguments);
		$this->viewHelperVariableContainer->expects($this->at(0))->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(1))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject')->will($this->returnValue($mockFormObject));
		$this->viewHelperVariableContainer->expects($this->at(2))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName')->will($this->returnValue($objectName));

		$formFieldViewHelper->expects($this->at(0))->method('renderHiddenIdentityField')->with($mockFormObject, $expectedProperty1);
		$formFieldViewHelper->expects($this->at(1))->method('renderHiddenIdentityField')->with($mockFormObject, $expectedProperty2);

		$formFieldViewHelper->_call('addAdditionalIdentityPropertiesIfNeeded');
	}

	/**
	 * @test
	 */
	public function renderHiddenFieldForEmptyValueAddsHiddenFieldNameToVariableContainer() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('getName'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);

		$formViewHelper->expects($this->any())->method('getName')->will($this->returnValue('NewFieldName'));
		$this->viewHelperVariableContainer->expects($this->at(0))->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'emptyHiddenFieldNames')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(1))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'emptyHiddenFieldNames')->will($this->returnValue(array('OldFieldName')));
		$this->viewHelperVariableContainer->expects($this->at(2))->method('addOrUpdate')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'emptyHiddenFieldNames', array('OldFieldName', 'NewFieldName'));

		$formViewHelper->_call('renderHiddenFieldForEmptyValue');
	}

	/**
	 * @test
	 */
	public function renderHiddenFieldForEmptyValueDoesNotAddTheSameHiddenFieldNameMoreThanOnce() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('getName'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);

		$formViewHelper->expects($this->any())->method('getName')->will($this->returnValue('SomeFieldName'));
		$this->viewHelperVariableContainer->expects($this->at(0))->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'emptyHiddenFieldNames')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(1))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'emptyHiddenFieldNames')->will($this->returnValue(array('SomeFieldName')));
		$this->viewHelperVariableContainer->expects($this->never())->method('addOrUpdate');

		$formViewHelper->_call('renderHiddenFieldForEmptyValue');
	}

	/**
	 * @test
	 */
	public function renderHiddenFieldForEmptyValueRemovesEmptySquareBracketsFromHiddenFieldName() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('getName'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);

		$formViewHelper->expects($this->any())->method('getName')->will($this->returnValue('SomeFieldName[WithBrackets][]'));
		$this->viewHelperVariableContainer->expects($this->at(0))->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'emptyHiddenFieldNames')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(1))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'emptyHiddenFieldNames')->will($this->returnValue(array()));
		$this->viewHelperVariableContainer->expects($this->at(2))->method('addOrUpdate')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'emptyHiddenFieldNames', array('SomeFieldName[WithBrackets]'));

		$formViewHelper->_call('renderHiddenFieldForEmptyValue');
	}

	/**
	 * @test
	 */
	public function renderHiddenFieldForEmptyValueDoesNotRemoveNonEmptySquareBracketsFromHiddenFieldName() {
		$formViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper', array('getName'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($formViewHelper);

		$formViewHelper->expects($this->any())->method('getName')->will($this->returnValue('SomeFieldName[WithBrackets][foo]'));
		$this->viewHelperVariableContainer->expects($this->at(0))->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'emptyHiddenFieldNames')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(1))->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'emptyHiddenFieldNames')->will($this->returnValue(array()));
		$this->viewHelperVariableContainer->expects($this->at(2))->method('addOrUpdate')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'emptyHiddenFieldNames', array('SomeFieldName[WithBrackets][foo]'));

		$formViewHelper->_call('renderHiddenFieldForEmptyValue');
	}
}
