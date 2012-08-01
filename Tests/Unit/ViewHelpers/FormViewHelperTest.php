<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

require_once(__DIR__ . '/ViewHelperBaseTestcase.php');

/**
 * Test for the Form view helper
 *
 */
class FormViewHelperTest extends \TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * @var \TYPO3\FLOW3\Security\Cryptography\HashService
	 */
	protected $hashService;

	/**
	 * Set up test dependencies
	 */
	public function setUp() {
		parent::setUp();
		$this->arguments['action'] = '';
		$this->arguments['arguments'] = array();
		$this->arguments['controller'] = '';
		$this->arguments['package'] = '';
		$this->arguments['subpackage'] = '';
		$this->arguments['method'] = '';
		$this->arguments['object'] = NULL;
		$this->arguments['section'] = '';
		$this->arguments['absolute'] = FALSE;
		$this->arguments['addQueryString'] = FALSE;
		$this->arguments['format'] = '';
		$this->arguments['additionalParams'] = array();
		$this->arguments['argumentsToBeExcludedFromQueryString'] = array();
	}

	/**
	 * @param \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper $viewHelper
	 */
	protected function injectDependenciesIntoViewHelper(\TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper $viewHelper) {
		$this->hashService = $this->getMock('TYPO3\FLOW3\Security\Cryptography\HashService');
		$this->inject($viewHelper, 'hashService', $this->hashService);
		$this->mvcPropertyMappingConfigurationService = $this->getMock('TYPO3\FLOW3\Mvc\Controller\MvcPropertyMappingConfigurationService');
		$this->inject($viewHelper, 'mvcPropertyMappingConfigurationService', $this->mvcPropertyMappingConfigurationService);
		parent::injectDependenciesIntoViewHelper($viewHelper);
	}

	/**
	 * @test
	 */
	public function renderAddsObjectToViewHelperVariableContainer() {
		$formObject = new \stdClass();

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('renderChildren', 'renderHiddenIdentityField', 'renderAdditionalIdentityFields', 'renderHiddenReferrerFields', 'addFormObjectNameToViewHelperVariableContainer', 'addFieldNamePrefixToViewHelperVariableContainer', 'removeFormObjectNameFromViewHelperVariableContainer', 'removeFieldNamePrefixFromViewHelperVariableContainer', 'addFormFieldNamesToViewHelperVariableContainer', 'removeFormFieldNamesFromViewHelperVariableContainer', 'renderEmptyHiddenFields', 'renderTrustedPropertiesField'), array(), '', FALSE);
		$this->arguments['object'] = $formObject;
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$this->viewHelperVariableContainer->expects($this->at(0))->method('add')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject', $formObject);
		$this->viewHelperVariableContainer->expects($this->at(1))->method('add')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'additionalIdentityProperties', array());
		$this->viewHelperVariableContainer->expects($this->at(2))->method('add')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'emptyHiddenFieldNames', array());
		$this->viewHelperVariableContainer->expects($this->at(3))->method('remove')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject');
		$this->viewHelperVariableContainer->expects($this->at(4))->method('remove')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'additionalIdentityProperties');
		$this->viewHelperVariableContainer->expects($this->at(5))->method('remove')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'emptyHiddenFieldNames');
		$viewHelper->render();
	}

	/**
	 * @test
	 */
	public function renderAddsObjectNameToTemplateVariableContainer() {
		$objectName = 'someObjectName';

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('renderChildren', 'renderHiddenIdentityField', 'renderHiddenReferrerFields', 'addFormObjectToViewHelperVariableContainer', 'addFieldNamePrefixToViewHelperVariableContainer', 'removeFormObjectFromViewHelperVariableContainer', 'removeFieldNamePrefixFromViewHelperVariableContainer', 'addFormFieldNamesToViewHelperVariableContainer', 'removeFormFieldNamesFromViewHelperVariableContainer', 'addEmptyHiddenFieldNamesToViewHelperVariableContainer', 'removeEmptyHiddenFieldNamesFromViewHelperVariableContainer', 'renderEmptyHiddenFields', 'renderTrustedPropertiesField'), array(), '', FALSE);
		$this->arguments['name'] = $objectName;
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$this->viewHelperVariableContainer->expects($this->once())->method('add')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName', $objectName);
		$this->viewHelperVariableContainer->expects($this->once())->method('remove')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName');
		$viewHelper->render();
	}

	/**
	 * @test
	 */
	public function formObjectNameArgumentOverrulesNameArgument() {
		$objectName = 'someObjectName';

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('renderChildren', 'renderHiddenIdentityField', 'renderHiddenReferrerFields', 'addFormObjectToViewHelperVariableContainer', 'addFieldNamePrefixToViewHelperVariableContainer', 'removeFormObjectFromViewHelperVariableContainer', 'removeFieldNamePrefixFromViewHelperVariableContainer', 'addFormFieldNamesToViewHelperVariableContainer', 'removeFormFieldNamesFromViewHelperVariableContainer', 'addEmptyHiddenFieldNamesToViewHelperVariableContainer', 'removeEmptyHiddenFieldNamesFromViewHelperVariableContainer', 'renderEmptyHiddenFields', 'renderTrustedPropertiesField'), array(), '', FALSE);
		$this->arguments['name'] = 'formName';
		$this->arguments['objectName'] = $objectName;
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$this->viewHelperVariableContainer->expects($this->once())->method('add')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName', $objectName);
		$this->viewHelperVariableContainer->expects($this->once())->method('remove')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName');
		$viewHelper->render();
	}

	/**
	 * @test
	 */
	public function renderCallsRenderHiddenReferrerFields() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('renderChildren', 'renderHiddenReferrerFields', 'renderEmptyHiddenFields', 'renderTrustedPropertiesField'), array(), '', FALSE);
		$viewHelper->expects($this->once())->method('renderHiddenReferrerFields');
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$viewHelper->render();
	}

	/**
	 * @test
	 */
	public function renderCallsRenderHiddenIdentityField() {
		$this->markTestIncomplete('Sebastian -- TODO after T3BOARD');
		$object = new \stdClass();
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('renderChildren', 'renderHiddenIdentityField', 'getFormObjectName'), array(), '', FALSE);

		$this->arguments['object'] = $object;
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$mockObjectSerializer = $this->getMock('TYPO3\FLOW3\Object\ObjectSerializer');
		$viewHelper->injectObjectSerializer($mockObjectSerializer);

		$viewHelper->expects($this->atLeastOnce())->method('getFormObjectName')->will($this->returnValue('MyName'));
		$viewHelper->expects($this->once())->method('renderHiddenIdentityField')->with($object, 'MyName');

		$viewHelper->render();
	}

	/**
	 * @test
	 */
	public function renderWithMethodGetAddsActionUriQueryAsHiddenFields() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('renderChildren'), array(), '', FALSE);

		$this->arguments['method'] = 'GET';
		$this->arguments['actionUri'] = 'http://localhost/fluid/test?foo=bar%20baz';
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$viewHelper->expects($this->any())->method('renderChildren')->will($this->returnValue('formContent'));

		$expectedResult = chr(10) .
			'<div style="display: none">' . chr(10) .
			'<input type="hidden" name="foo" value="bar baz" />' . chr(10) .
			'<input type="hidden" name="__referrer[@package]" value="" />' . chr(10) .
			'<input type="hidden" name="__referrer[@subpackage]" value="" />' . chr(10) .
			'<input type="hidden" name="__referrer[@controller]" value="" />' . chr(10) .
			'<input type="hidden" name="__referrer[@action]" value="" />' . chr(10) .
			'<input type="hidden" name="__referrer[arguments]" value="" />' . chr(10) .
			'<input type="hidden" name="__trustedProperties" value="" />' . chr(10) . chr(10) .
			'</div>' . chr(10) .
			'formContent';
		$this->tagBuilder->expects($this->once())->method('setContent')->with($expectedResult);

		$viewHelper->render();
	}

	/**
	 * @test
	 */
	public function renderWithMethodGetDoesNotBreakInRenderHiddenActionUriQueryParametersIfNoQueryStringExists() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('renderChildren'), array(), '', FALSE);

		$this->arguments['method'] = 'GET';
		$this->arguments['actionUri'] = 'http://localhost/fluid/test';
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$viewHelper->expects($this->any())->method('renderChildren')->will($this->returnValue('formContent'));

		$expectedResult = chr(10) .
			'<div style="display: none">' . chr(10) .
			'<input type="hidden" name="__referrer[@package]" value="" />' . chr(10) .
			'<input type="hidden" name="__referrer[@subpackage]" value="" />' . chr(10) .
			'<input type="hidden" name="__referrer[@controller]" value="" />' . chr(10) .
			'<input type="hidden" name="__referrer[@action]" value="" />' . chr(10) .
			'<input type="hidden" name="__referrer[arguments]" value="" />' . chr(10) .
			'<input type="hidden" name="__trustedProperties" value="" />' . chr(10) . chr(10) .
			'</div>' . chr(10) .
			'formContent';
		$this->tagBuilder->expects($this->once())->method('setContent')->with($expectedResult);

		$viewHelper->render();
	}

	/**
	 * @test
	 */
	public function renderCallsRenderAdditionalIdentityFields() {
		$this->markTestIncomplete('Sebastian -- TODO after T3BOARD');
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('renderChildren', 'renderAdditionalIdentityFields'), array(), '', FALSE);
		$viewHelper->expects($this->once())->method('renderAdditionalIdentityFields');
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$mockObjectSerializer = $this->getMock('TYPO3\FLOW3\Object\ObjectSerializer');
		$viewHelper->injectObjectSerializer($mockObjectSerializer);

		$viewHelper->render();
	}

	/**
	 * @test
	 */
	public function renderWrapsHiddenFieldsWithDivForXhtmlCompatibility() {
		$viewHelper = $this->getMock($this->buildAccessibleProxy('TYPO3\Fluid\ViewHelpers\FormViewHelper'), array('renderChildren', 'renderHiddenIdentityField', 'renderAdditionalIdentityFields', 'renderHiddenReferrerFields', 'renderEmptyHiddenFields', 'renderTrustedPropertiesField'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$viewHelper->expects($this->once())->method('renderHiddenIdentityField')->will($this->returnValue('hiddenIdentityField'));
		$viewHelper->expects($this->once())->method('renderAdditionalIdentityFields')->will($this->returnValue('additionalIdentityFields'));
		$viewHelper->expects($this->once())->method('renderHiddenReferrerFields')->will($this->returnValue('hiddenReferrerFields'));
		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue('formContent'));
		$viewHelper->expects($this->once())->method('renderEmptyHiddenFields')->will($this->returnValue('emptyHiddenFields'));
		$viewHelper->expects($this->once())->method('renderTrustedPropertiesField')->will($this->returnValue('trustedPropertiesField'));

		$expectedResult = chr(10) . '<div style="display: none">' . 'hiddenIdentityFieldadditionalIdentityFieldshiddenReferrerFieldsemptyHiddenFieldstrustedPropertiesField' . chr(10) . '</div>' . chr(10) . 'formContent';
		$this->tagBuilder->expects($this->once())->method('setContent')->with($expectedResult);

		$viewHelper->render();
	}


	/**
	 * @test
	 */
	public function renderAdditionalIdentityFieldsFetchesTheFieldsFromViewHelperVariableContainerAndBuildsHiddenFieldsForThem() {
		$identityProperties = array(
			'object1[object2]' => '<input type="hidden" name="object1[object2][__identity]" value="42" />',
			'object1[object2][subobject]' => '<input type="hidden" name="object1[object2][subobject][__identity]" value="21" />'
		);
		$this->viewHelperVariableContainer->expects($this->once())->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'additionalIdentityProperties')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->once())->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'additionalIdentityProperties')->will($this->returnValue($identityProperties));
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('renderChildren'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$expected = chr(10) . '<input type="hidden" name="object1[object2][__identity]" value="42" />' . chr(10) .
			'<input type="hidden" name="object1[object2][subobject][__identity]" value="21" />';
		$actual = $viewHelper->_call('renderAdditionalIdentityFields');
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @test
	 */
	public function renderHiddenReferrerFieldsAddCurrentControllerAndActionAsHiddenFields() {
		$this->markTestIncomplete('Sebastian -- TODO after T3BOARD');
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$mockObjectSerializer = $this->getMock('TYPO3\FLOW3\Object\ObjectSerializer');
		$viewHelper->injectObjectSerializer($mockObjectSerializer);

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
	 */
	public function renderHiddenReferrerFieldsAddCurrentControllerAndActionOfParentAndSubRequestAsHiddenFields() {
		$this->markTestIncomplete('Sebastian -- TODO after T3BOARD');
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$mockObjectSerializer = $this->getMock('TYPO3\FLOW3\Object\ObjectSerializer');
		$viewHelper->injectObjectSerializer($mockObjectSerializer);

		$mockSubRequest = $this->getMock('TYPO3\FLOW3\Mvc\Web\SubRequest', array(), array(), '', FALSE);
		$mockSubRequest->expects($this->atLeastOnce())->method('getControllerPackageKey')->will($this->returnValue('subRequestPackageKey'));
		$mockSubRequest->expects($this->atLeastOnce())->method('getControllerSubpackageKey')->will($this->returnValue('subRequestSubpackageKey'));
		$mockSubRequest->expects($this->atLeastOnce())->method('getControllerName')->will($this->returnValue('subRequestControllerName'));
		$mockSubRequest->expects($this->atLeastOnce())->method('getControllerActionName')->will($this->returnValue('subRequestControllerActionName'));
		$mockSubRequest->expects($this->any())->method('getParentRequest')->will($this->returnValue($this->request));
		$mockSubRequest->expects($this->any())->method('getArgumentNamespace')->will($this->returnValue('subRequestArgumentNamespace'));

		$this->request->expects($this->atLeastOnce())->method('getControllerPackageKey')->will($this->returnValue('packageKey'));
		$this->request->expects($this->atLeastOnce())->method('getControllerSubpackageKey')->will($this->returnValue('subpackageKey'));
		$this->request->expects($this->atLeastOnce())->method('getControllerName')->will($this->returnValue('controllerName'));
		$this->request->expects($this->atLeastOnce())->method('getControllerActionName')->will($this->returnValue('controllerActionName'));

		$this->controllerContext = $this->getMock('TYPO3\FLOW3\Mvc\Controller\ControllerContext', array(), array(), '', FALSE);
		$this->controllerContext->expects($this->any())->method('getUriBuilder')->will($this->returnValue($this->uriBuilder));
		$this->controllerContext->expects($this->any())->method('getRequest')->will($this->returnValue($mockSubRequest));
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$hiddenFields = $viewHelper->_call('renderHiddenReferrerFields');
		$expectedResult = chr(10) . '<input type="hidden" name="subRequestArgumentNamespace[__referrer][packageKey]" value="subRequestPackageKey" />' . chr(10) .
			'<input type="hidden" name="subRequestArgumentNamespace[__referrer][subpackageKey]" value="subRequestSubpackageKey" />' . chr(10) .
			'<input type="hidden" name="subRequestArgumentNamespace[__referrer][controllerName]" value="subRequestControllerName" />' . chr(10) .
			'<input type="hidden" name="subRequestArgumentNamespace[__referrer][actionName]" value="subRequestControllerActionName" />' . chr(10) .
			'<input type="hidden" name="__referrer[packageKey]" value="packageKey" />' . chr(10) .
			'<input type="hidden" name="__referrer[subpackageKey]" value="subpackageKey" />' . chr(10) .
			'<input type="hidden" name="__referrer[controllerName]" value="controllerName" />' . chr(10) .
			'<input type="hidden" name="__referrer[actionName]" value="controllerActionName" />' . chr(10);

		$this->assertEquals($expectedResult, $hiddenFields);
	}

	/**
	 * @test
	 */
	public function renderAddsSpecifiedPrefixToTemplateVariableContainer() {
		$prefix = 'somePrefix';

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('renderChildren', 'renderHiddenIdentityField', 'renderHiddenReferrerFields', 'addFormFieldNamesToViewHelperVariableContainer', 'removeFormFieldNamesFromViewHelperVariableContainer', 'addEmptyHiddenFieldNamesToViewHelperVariableContainer', 'removeEmptyHiddenFieldNamesFromViewHelperVariableContainer', 'renderEmptyHiddenFields', 'renderTrustedPropertiesField'), array(), '', FALSE);
		$this->arguments['fieldNamePrefix'] = $prefix;
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$this->viewHelperVariableContainer->expects($this->once())->method('add')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix', $prefix);
		$this->viewHelperVariableContainer->expects($this->once())->method('remove')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix');
		$viewHelper->render();
	}

	/**
	 * @test
	 */
	public function renderAddsNoFieldNamePrefixToTemplateVariableContainerIfNoPrefixIsSpecified() {
		$expectedPrefix = '';

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('renderChildren', 'renderHiddenIdentityField', 'renderHiddenReferrerFields', 'addFormFieldNamesToViewHelperVariableContainer', 'removeFormFieldNamesFromViewHelperVariableContainer', 'addEmptyHiddenFieldNamesToViewHelperVariableContainer', 'removeEmptyHiddenFieldNamesFromViewHelperVariableContainer', 'renderEmptyHiddenFields', 'renderTrustedPropertiesField'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$this->viewHelperVariableContainer->expects($this->once())->method('add')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix', $expectedPrefix);
		$this->viewHelperVariableContainer->expects($this->once())->method('remove')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix');
		$viewHelper->render();
	}

	/**
	 * @test
	 */
	public function renderAddsDefaultFieldNamePrefixToTemplateVariableContainerIfNoPrefixIsSpecifiedAndRequestIsASubRequest() {
		$expectedPrefix = 'someArgumentPrefix';
		$mockSubRequest = $this->getMock('TYPO3\FLOW3\Mvc\ActionRequest', array(), array(), '', FALSE);
		$mockSubRequest->expects($this->once())->method('getArgumentNamespace')->will($this->returnValue($expectedPrefix));

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('renderChildren', 'renderHiddenIdentityField', 'renderHiddenReferrerFields', 'addFormFieldNamesToViewHelperVariableContainer', 'removeFormFieldNamesFromViewHelperVariableContainer', 'addEmptyHiddenFieldNamesToViewHelperVariableContainer', 'removeEmptyHiddenFieldNamesFromViewHelperVariableContainer', 'renderEmptyHiddenFields', 'renderTrustedPropertiesField'), array(), '', FALSE);
		$this->controllerContext = $this->getMock('TYPO3\FLOW3\Mvc\Controller\ControllerContext', array(), array(), '', FALSE);
		$this->controllerContext->expects($this->any())->method('getUriBuilder')->will($this->returnValue($this->uriBuilder));
		$this->controllerContext->expects($this->any())->method('getRequest')->will($this->returnValue($mockSubRequest));
		$this->renderingContext->setControllerContext($this->controllerContext);
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$this->viewHelperVariableContainer->expects($this->once())->method('add')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix', $expectedPrefix);
		$this->viewHelperVariableContainer->expects($this->once())->method('remove')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix');
		$viewHelper->render();
	}

	/**
	 * @test
	 */
	public function renderEmptyHiddenFieldsRendersEmptyStringByDefault() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('renderChildren'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$expected = '';
		$actual = $viewHelper->_call('renderEmptyHiddenFields');
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @test
	 */
	public function renderEmptyHiddenFieldsRendersOneHiddenFieldPerEntry() {
		$emptyHiddenFieldNames = array('fieldName1', 'fieldName2');
		$this->viewHelperVariableContainer->expects($this->once())->method('exists')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'emptyHiddenFieldNames')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->once())->method('get')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'emptyHiddenFieldNames')->will($this->returnValue($emptyHiddenFieldNames));
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('renderChildren'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$expected = '<input type="hidden" name="fieldName1" value="" />' . chr(10) . '<input type="hidden" name="fieldName2" value="" />' . chr(10);
		$actual = $viewHelper->_call('renderEmptyHiddenFields');
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @test
	 */
	public function renderResetsFormActionUri() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('renderChildren'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$viewHelper->_set('formActionUri', 'someUri');

		$viewHelper->render();
		$this->assertNull($viewHelper->_get('formActionUri'));
	}


}
?>