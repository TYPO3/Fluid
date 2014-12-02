<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService;
use TYPO3\Flow\Security\Authentication\AuthenticationManagerInterface;
use TYPO3\Flow\Security\Context;
use TYPO3\Flow\Security\Cryptography\HashService;
use TYPO3\Fluid\ViewHelpers\FormViewHelper;

require_once(__DIR__ . '/ViewHelperBaseTestcase.php');

/**
 * Test for the Form view helper
 */
class FormViewHelperTest extends \TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * @var HashService|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $hashService;

	/**
	 * @var Context|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $securityContext;

	/**
	 * @var AuthenticationManagerInterface|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mockAuthenticationManager;

	/**
	 * @var MvcPropertyMappingConfigurationService|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mvcPropertyMappingConfigurationService;

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
		$this->arguments['useParentRequest'] = FALSE;
	}

	/**
	 * @param \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper $viewHelper
	 */
	protected function injectDependenciesIntoViewHelper(\TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper $viewHelper) {
		$this->hashService = $this->getMock('TYPO3\Flow\Security\Cryptography\HashService');
		$this->inject($viewHelper, 'hashService', $this->hashService);
		$this->mvcPropertyMappingConfigurationService = $this->getMock('TYPO3\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService');
		$this->inject($viewHelper, 'mvcPropertyMappingConfigurationService', $this->mvcPropertyMappingConfigurationService);
		$this->securityContext = $this->getMock('TYPO3\Flow\Security\Context');
		$this->inject($viewHelper, 'securityContext', $this->securityContext);
		$this->mockAuthenticationManager = $this->getMock('TYPO3\Flow\Security\Authentication\AuthenticationManagerInterface');
		$this->inject($viewHelper, 'authenticationManager', $this->mockAuthenticationManager);
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
		$this->securityContext->expects($this->any())->method('isInitialized')->will($this->returnValue(FALSE));

		$this->viewHelperVariableContainer->expects($this->at(0))->method('add')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject', $formObject);
		$this->viewHelperVariableContainer->expects($this->at(1))->method('add')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'additionalIdentityProperties', array());
		$this->viewHelperVariableContainer->expects($this->at(2))->method('add')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'emptyHiddenFieldNames', array());
		$this->viewHelperVariableContainer->expects($this->at(3))->method('remove')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject');
		$this->viewHelperVariableContainer->expects($this->at(4))->method('remove')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'additionalIdentityProperties');
		$this->viewHelperVariableContainer->expects($this->at(5))->method('remove')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'emptyHiddenFieldNames');
		$viewHelper->render('index');
	}

	/**
	 * @test
	 */
	public function renderAddsObjectNameToTemplateVariableContainer() {
		$objectName = 'someObjectName';

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('renderChildren', 'renderHiddenIdentityField', 'renderHiddenReferrerFields', 'addFormObjectToViewHelperVariableContainer', 'addFieldNamePrefixToViewHelperVariableContainer', 'removeFormObjectFromViewHelperVariableContainer', 'removeFieldNamePrefixFromViewHelperVariableContainer', 'addFormFieldNamesToViewHelperVariableContainer', 'removeFormFieldNamesFromViewHelperVariableContainer', 'addEmptyHiddenFieldNamesToViewHelperVariableContainer', 'removeEmptyHiddenFieldNamesFromViewHelperVariableContainer', 'renderEmptyHiddenFields', 'renderTrustedPropertiesField'), array(), '', FALSE);
		$this->arguments['name'] = $objectName;
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$this->securityContext->expects($this->any())->method('isInitialized')->will($this->returnValue(FALSE));

		$this->viewHelperVariableContainer->expects($this->once())->method('add')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName', $objectName);
		$this->viewHelperVariableContainer->expects($this->once())->method('remove')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName');
		$viewHelper->render('index');
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
		$this->securityContext->expects($this->any())->method('isInitialized')->will($this->returnValue(FALSE));

		$this->viewHelperVariableContainer->expects($this->once())->method('add')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName', $objectName);
		$this->viewHelperVariableContainer->expects($this->once())->method('remove')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName');
		$viewHelper->render('index');
	}

	/**
	 * @test
	 */
	public function renderCallsRenderHiddenReferrerFields() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('renderChildren', 'renderHiddenReferrerFields', 'renderEmptyHiddenFields', 'renderTrustedPropertiesField'), array(), '', FALSE);
		$viewHelper->expects($this->once())->method('renderHiddenReferrerFields');
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$this->securityContext->expects($this->any())->method('isInitialized')->will($this->returnValue(FALSE));

		$viewHelper->render('index');
	}

	/**
	 * @test
	 */
	public function renderCallsRenderHiddenIdentityField() {
		$object = new \stdClass();
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('renderChildren', 'renderHiddenIdentityField', 'getFormObjectName'), array(), '', FALSE);

		$this->arguments['object'] = $object;
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$this->securityContext->expects($this->any())->method('isInitialized')->will($this->returnValue(FALSE));

		$viewHelper->expects($this->atLeastOnce())->method('getFormObjectName')->will($this->returnValue('MyName'));
		$viewHelper->expects($this->once())->method('renderHiddenIdentityField')->with($object, 'MyName');

		$viewHelper->render('index');
	}

	/**
	 * @test
	 */
	public function renderWithMethodGetAddsActionUriQueryAsHiddenFields() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('renderChildren'), array(), '', FALSE);

		$this->arguments['method'] = 'GET';
		$this->arguments['actionUri'] = 'http://localhost/fluid/test?foo=bar%20baz';
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$this->securityContext->expects($this->any())->method('isInitialized')->will($this->returnValue(FALSE));
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

		$viewHelper->render('index');
	}

	/**
	 * @test
	 */
	public function renderWithMethodGetAddsActionUriQueryAsHiddenFieldsWithHtmlescape() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('renderChildren'), array(), '', FALSE);

		$this->arguments['method'] = 'GET';
		$this->arguments['actionUri'] = 'http://localhost/fluid/test?foo=<bar>';
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$this->securityContext->expects($this->any())->method('isInitialized')->will($this->returnValue(FALSE));
		$viewHelper->expects($this->any())->method('renderChildren')->will($this->returnValue('formContent'));

		$expectedResult = '<input type="hidden" name="foo" value="&lt;bar&gt;" />';
		$this->tagBuilder->expects($this->once())->method('setContent')->with($this->stringContains($expectedResult));

		$viewHelper->render('index');
	}

	/**
	 * @test
	 */
	public function renderWithMethodGetDoesNotBreakInRenderHiddenActionUriQueryParametersIfNoQueryStringExists() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('renderChildren'), array(), '', FALSE);

		$this->arguments['method'] = 'GET';
		$this->arguments['actionUri'] = 'http://localhost/fluid/test';
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$this->securityContext->expects($this->any())->method('isInitialized')->will($this->returnValue(FALSE));
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

		$viewHelper->render('index');
	}

	/**
	 * @test
	 */
	public function renderCallsRenderAdditionalIdentityFields() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('renderChildren', 'renderAdditionalIdentityFields'), array(), '', FALSE);
		$viewHelper->expects($this->once())->method('renderAdditionalIdentityFields');
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$this->securityContext->expects($this->any())->method('isInitialized')->will($this->returnValue(FALSE));

		$viewHelper->render('index');
	}

	/**
	 * @test
	 */
	public function renderWrapsHiddenFieldsWithDivForXhtmlCompatibility() {
		$viewHelper = $this->getMock($this->buildAccessibleProxy('TYPO3\Fluid\ViewHelpers\FormViewHelper'), array('renderChildren', 'renderHiddenIdentityField', 'renderAdditionalIdentityFields', 'renderHiddenReferrerFields', 'renderEmptyHiddenFields', 'renderTrustedPropertiesField'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$this->securityContext->expects($this->any())->method('isInitialized')->will($this->returnValue(FALSE));
		$viewHelper->expects($this->once())->method('renderHiddenIdentityField')->will($this->returnValue('hiddenIdentityField'));
		$viewHelper->expects($this->once())->method('renderAdditionalIdentityFields')->will($this->returnValue('additionalIdentityFields'));
		$viewHelper->expects($this->once())->method('renderHiddenReferrerFields')->will($this->returnValue('hiddenReferrerFields'));
		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue('formContent'));
		$viewHelper->expects($this->once())->method('renderEmptyHiddenFields')->will($this->returnValue('emptyHiddenFields'));
		$viewHelper->expects($this->once())->method('renderTrustedPropertiesField')->will($this->returnValue('trustedPropertiesField'));

		$expectedResult = chr(10) . '<div style="display: none">hiddenIdentityFieldadditionalIdentityFieldshiddenReferrerFieldsemptyHiddenFieldstrustedPropertiesField' . chr(10) . '</div>' . chr(10) . 'formContent';
		$this->tagBuilder->expects($this->once())->method('setContent')->with($expectedResult);

		$viewHelper->render('index');
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
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$this->securityContext->expects($this->any())->method('isInitialized')->will($this->returnValue(FALSE));

		$this->request->expects($this->atLeastOnce())->method('getControllerPackageKey')->will($this->returnValue('packageKey'));
		$this->request->expects($this->atLeastOnce())->method('getControllerSubpackageKey')->will($this->returnValue('subpackageKey'));
		$this->request->expects($this->atLeastOnce())->method('getControllerName')->will($this->returnValue('controllerName'));
		$this->request->expects($this->atLeastOnce())->method('getControllerActionName')->will($this->returnValue('controllerActionName'));

		$hiddenFields = $viewHelper->_call('renderHiddenReferrerFields');
		$expectedResult = chr(10) . '<input type="hidden" name="__referrer[@package]" value="packageKey" />' . chr(10) .
			'<input type="hidden" name="__referrer[@subpackage]" value="subpackageKey" />' . chr(10) .
			'<input type="hidden" name="__referrer[@controller]" value="controllerName" />' . chr(10) .
			'<input type="hidden" name="__referrer[@action]" value="controllerActionName" />' . chr(10) .
			'<input type="hidden" name="__referrer[arguments]" value="" />' . chr(10);
		$this->assertEquals($expectedResult, $hiddenFields);
	}

	/**
	 * @test
	 */
	public function renderHiddenReferrerFieldsAddCurrentControllerAndActionOfParentAndSubRequestAsHiddenFields() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$this->securityContext->expects($this->any())->method('isInitialized')->will($this->returnValue(FALSE));

		$mockSubRequest = $this->getMock('TYPO3\Flow\Mvc\ActionRequest', array(), array(), 'Foo', FALSE);
		$mockSubRequest->expects($this->atLeastOnce())->method('isMainRequest')->will($this->returnValue(FALSE));
		$mockSubRequest->expects($this->atLeastOnce())->method('getControllerPackageKey')->will($this->returnValue('subRequestPackageKey'));
		$mockSubRequest->expects($this->atLeastOnce())->method('getControllerSubpackageKey')->will($this->returnValue('subRequestSubpackageKey'));
		$mockSubRequest->expects($this->atLeastOnce())->method('getControllerName')->will($this->returnValue('subRequestControllerName'));
		$mockSubRequest->expects($this->atLeastOnce())->method('getControllerActionName')->will($this->returnValue('subRequestControllerActionName'));
		$mockSubRequest->expects($this->atLeastOnce())->method('getParentRequest')->will($this->returnValue($this->request));
		$mockSubRequest->expects($this->atLeastOnce())->method('getArgumentNamespace')->will($this->returnValue('subRequestArgumentNamespace'));

		$this->request->expects($this->atLeastOnce())->method('getControllerPackageKey')->will($this->returnValue('packageKey'));
		$this->request->expects($this->atLeastOnce())->method('getControllerSubpackageKey')->will($this->returnValue('subpackageKey'));
		$this->request->expects($this->atLeastOnce())->method('getControllerName')->will($this->returnValue('controllerName'));
		$this->request->expects($this->atLeastOnce())->method('getControllerActionName')->will($this->returnValue('controllerActionName'));

		$this->controllerContext = $this->getMock('TYPO3\Flow\Mvc\Controller\ControllerContext', array(), array(), '', FALSE);
		$this->controllerContext->expects($this->atLeastOnce())->method('getRequest')->will($this->returnValue($mockSubRequest));
		$this->renderingContext->setControllerContext($this->controllerContext);
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$hiddenFields = $viewHelper->_call('renderHiddenReferrerFields');
		$expectedResult = chr(10) . '<input type="hidden" name="subRequestArgumentNamespace[__referrer][@package]" value="subRequestPackageKey" />' . chr(10) .
			'<input type="hidden" name="subRequestArgumentNamespace[__referrer][@subpackage]" value="subRequestSubpackageKey" />' . chr(10) .
			'<input type="hidden" name="subRequestArgumentNamespace[__referrer][@controller]" value="subRequestControllerName" />' . chr(10) .
			'<input type="hidden" name="subRequestArgumentNamespace[__referrer][@action]" value="subRequestControllerActionName" />' . chr(10) .
			'<input type="hidden" name="subRequestArgumentNamespace[__referrer][arguments]" value="" />' . chr(10) .
			'<input type="hidden" name="__referrer[@package]" value="packageKey" />' . chr(10) .
			'<input type="hidden" name="__referrer[@subpackage]" value="subpackageKey" />' . chr(10) .
			'<input type="hidden" name="__referrer[@controller]" value="controllerName" />' . chr(10) .
			'<input type="hidden" name="__referrer[@action]" value="controllerActionName" />' . chr(10) .
			'<input type="hidden" name="__referrer[arguments]" value="" />' . chr(10);

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
		$this->securityContext->expects($this->any())->method('isInitialized')->will($this->returnValue(FALSE));

		$this->viewHelperVariableContainer->expects($this->once())->method('add')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix', $prefix);
		$this->viewHelperVariableContainer->expects($this->once())->method('remove')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix');
		$viewHelper->render('index');
	}

	/**
	 * @test
	 */
	public function renderAddsNoFieldNamePrefixToTemplateVariableContainerIfNoPrefixIsSpecified() {
		$expectedPrefix = '';

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('renderChildren', 'renderHiddenIdentityField', 'renderHiddenReferrerFields', 'addFormFieldNamesToViewHelperVariableContainer', 'removeFormFieldNamesFromViewHelperVariableContainer', 'addEmptyHiddenFieldNamesToViewHelperVariableContainer', 'removeEmptyHiddenFieldNamesFromViewHelperVariableContainer', 'renderEmptyHiddenFields', 'renderTrustedPropertiesField'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$this->securityContext->expects($this->any())->method('isInitialized')->will($this->returnValue(FALSE));

		$this->viewHelperVariableContainer->expects($this->once())->method('add')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix', $expectedPrefix);
		$this->viewHelperVariableContainer->expects($this->once())->method('remove')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix');
		$viewHelper->render('index');
	}

	/**
	 * @test
	 */
	public function renderAddsDefaultFieldNamePrefixToTemplateVariableContainerIfNoPrefixIsSpecifiedAndRequestIsASubRequest() {
		$expectedPrefix = 'someArgumentPrefix';
		$mockSubRequest = $this->getMock('TYPO3\Flow\Mvc\ActionRequest', array(), array(), '', FALSE);
		$mockSubRequest->expects($this->once())->method('getArgumentNamespace')->will($this->returnValue($expectedPrefix));

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('getFormActionUri', 'renderChildren', 'renderHiddenIdentityField', 'renderHiddenReferrerFields', 'addFormFieldNamesToViewHelperVariableContainer', 'removeFormFieldNamesFromViewHelperVariableContainer', 'addEmptyHiddenFieldNamesToViewHelperVariableContainer', 'removeEmptyHiddenFieldNamesFromViewHelperVariableContainer', 'renderEmptyHiddenFields', 'renderTrustedPropertiesField'), array(), '', FALSE);
		$this->controllerContext = $this->getMock('TYPO3\Flow\Mvc\Controller\ControllerContext', array(), array(), '', FALSE);
		$this->controllerContext->expects($this->any())->method('getRequest')->will($this->returnValue($mockSubRequest));
		$this->renderingContext->setControllerContext($this->controllerContext);
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$this->viewHelperVariableContainer->expects($this->once())->method('add')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix', $expectedPrefix);
		$this->viewHelperVariableContainer->expects($this->once())->method('remove')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix');
		$viewHelper->render('index');
	}

	/**
	 * @test
	 */
	public function renderAddsDefaultFieldNamePrefixToTemplateVariableContainerIfNoPrefixIsSpecifiedAndUseParentRequestArgumentIsSet() {
		$expectedPrefix = 'parentRequestsPrefix';
		$mockParentRequest = $this->getMock('TYPO3\Flow\Mvc\ActionRequest', array(), array(), '', FALSE);
		$mockParentRequest->expects($this->once())->method('getArgumentNamespace')->will($this->returnValue($expectedPrefix));
		$mockSubRequest = $this->getMock('TYPO3\Flow\Mvc\ActionRequest', array(), array(), '', FALSE);
		$mockSubRequest->expects($this->once())->method('getParentRequest')->will($this->returnValue($mockParentRequest));

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('getFormActionUri', 'renderChildren', 'renderHiddenIdentityField', 'renderHiddenReferrerFields', 'addFormFieldNamesToViewHelperVariableContainer', 'removeFormFieldNamesFromViewHelperVariableContainer', 'addEmptyHiddenFieldNamesToViewHelperVariableContainer', 'removeEmptyHiddenFieldNamesFromViewHelperVariableContainer', 'renderEmptyHiddenFields', 'renderTrustedPropertiesField'), array(), '', FALSE);
		$this->arguments['useParentRequest'] = TRUE;
		$this->controllerContext = $this->getMock('TYPO3\Flow\Mvc\Controller\ControllerContext', array(), array(), '', FALSE);
		$this->controllerContext->expects($this->once())->method('getRequest')->will($this->returnValue($mockSubRequest));
		$this->renderingContext->setControllerContext($this->controllerContext);
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$this->securityContext->expects($this->any())->method('isInitialized')->will($this->returnValue(FALSE));

		$this->viewHelperVariableContainer->expects($this->once())->method('add')->with('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix', $expectedPrefix);
		$viewHelper->render('index');
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

		$viewHelper->render('index');
		$this->assertNull($viewHelper->_get('formActionUri'));
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception
	 */
	public function renderThrowsExceptionIfNeitherActionNorActionUriArgumentIsSpecified() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('renderChildren'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$viewHelper->render();
	}

	/**
	 * @test
	 */
	public function renderThrowsExceptionIfUseParentRequestIsSetAndTheCurrentRequestHasNoParentRequest() {
		$this->setExpectedException('TYPO3\Fluid\Core\ViewHelper\Exception', '', 1361354942);

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('renderChildren'), array(), '', FALSE);
		$this->arguments['useParentRequest'] = TRUE;
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$viewHelper->render('index');
	}

	/**
	 * @test
	 */
	public function renderUsesParentRequestIfUseParentRequestIsSet() {
		$mockParentRequest = $this->getMock('TYPO3\Flow\Mvc\ActionRequest', array(), array(), '', FALSE);

		$mockSubRequest = $this->getMock('TYPO3\Flow\Mvc\ActionRequest', array(), array(), '', FALSE);
		$mockSubRequest->expects($this->once())->method('isMainRequest')->will($this->returnValue(FALSE));
		$mockSubRequest->expects($this->once())->method('getParentRequest')->will($this->returnValue($mockParentRequest));

		$this->uriBuilder->expects($this->once())->method('setRequest')->with($mockParentRequest);

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', array('dummy'), array(), '', FALSE);
		$this->arguments['useParentRequest'] = TRUE;

		$this->controllerContext = $this->getMock('TYPO3\Flow\Mvc\Controller\ControllerContext', array(), array(), '', FALSE);
		$this->controllerContext->expects($this->once())->method('getRequest')->will($this->returnValue($mockSubRequest));
		$this->controllerContext->expects($this->once())->method('getUriBuilder')->will($this->returnValue($this->uriBuilder));
		$this->renderingContext->setControllerContext($this->controllerContext);

		$this->injectDependenciesIntoViewHelper($viewHelper);
		$viewHelper->_call('getFormActionUri');
	}

	/**
	 * @test
	 */
	public function csrfTokenFieldIsNotRenderedIfFormMethodIsSafe() {
		$this->arguments['method'] = 'get';

		/** @var FormViewHelper|\PHPUnit_Framework_MockObject_MockObject $viewHelper */
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', NULL, array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$this->securityContext->expects($this->never())->method('getCsrfProtectionToken');

		$this->assertEquals('', $viewHelper->_call('renderCsrfTokenField'));
	}

	/**
	 * @test
	 */
	public function csrfTokenFieldIsNotRenderedIfSecurityContextIsNotInitialized() {
		/** @var FormViewHelper|\PHPUnit_Framework_MockObject_MockObject $viewHelper */
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', NULL, array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$this->securityContext->expects($this->atLeastOnce())->method('isInitialized')->will($this->returnValue(FALSE));
		$this->mockAuthenticationManager->expects($this->any())->method('isAuthenticated')->will($this->returnValue(TRUE));
		$this->securityContext->expects($this->never())->method('getCsrfProtectionToken');

		$this->assertEquals('', $viewHelper->_call('renderCsrfTokenField'));
	}

	/**
	 * @test
	 */
	public function csrfTokenFieldIsNotRenderedIfNoAccountIsAuthenticated() {
		/** @var FormViewHelper|\PHPUnit_Framework_MockObject_MockObject $viewHelper */
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', NULL, array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$this->securityContext->expects($this->any())->method('isInitialized')->will($this->returnValue(TRUE));
		$this->mockAuthenticationManager->expects($this->atLeastOnce())->method('isAuthenticated')->will($this->returnValue(FALSE));
		$this->securityContext->expects($this->never())->method('getCsrfProtectionToken');

		$this->assertEquals('', $viewHelper->_call('renderCsrfTokenField'));
	}

	/**
	 * @test
	 */
	public function csrfTokenFieldIsRenderedForUnsafeRequests() {
		/** @var FormViewHelper|\PHPUnit_Framework_MockObject_MockObject $viewHelper */
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FormViewHelper', NULL, array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$this->securityContext->expects($this->any())->method('isInitialized')->will($this->returnValue(TRUE));
		$this->mockAuthenticationManager->expects($this->any())->method('isAuthenticated')->will($this->returnValue(TRUE));

		$this->securityContext->expects($this->atLeastOnce())->method('getCsrfProtectionToken')->will($this->returnValue('CSRFTOKEN'));

		$this->assertEquals('<input type="hidden" name="__csrfToken" value="CSRFTOKEN" />' . chr(10), $viewHelper->_call('renderCsrfTokenField'));
	}

}
