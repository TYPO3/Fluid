<?php
namespace TYPO3\Fluid\Tests\Unit\Core\Widget;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Http\Component\ComponentContext;
use TYPO3\Flow\Http;
use TYPO3\Flow\Mvc\Dispatcher;
use TYPO3\Flow\Object\ObjectManagerInterface;
use TYPO3\Flow\Security\Context;
use TYPO3\Flow\Security\Cryptography\HashService;
use TYPO3\Flow\Tests\UnitTestCase;
use TYPO3\Fluid\Core\Widget\AjaxWidgetComponent;
use TYPO3\Fluid\Core\Widget\AjaxWidgetContextHolder;

/**
 * Testcase for AjaxWidgetComponent
 *
 */
class AjaxWidgetComponentTest extends UnitTestCase {

	/**
	 * @var AjaxWidgetComponent
	 */
	protected $ajaxWidgetComponent;

	/**
	 * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mockObjectManager;

	/**
	 * @var ComponentContext|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mockComponentContext;

	/**
	 * @var Http\Request|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mockHttpRequest;

	/**
	 * @var Http\Response|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mockHttpResponse;

	/**
	 * @var AjaxWidgetContextHolder|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mockAjaxWidgetContextHolder;

	/**
	 * @var HashService|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mockHashService;

	/**
	 * @var Dispatcher|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mockDispatcher;

	/**
	 * @var Context|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mockSecurityContext;

	/**
	 * @var PropertyMapper|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mockPropertyMapper;

	/**
	 * @var PropertyMappingConfiguration|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mockPropertyMappingConfiguration;

	/**
	 */
	public function setUp() {
		$this->ajaxWidgetComponent = new AjaxWidgetComponent();

		$this->mockObjectManager = $this->getMockBuilder('TYPO3\Flow\Object\ObjectManagerInterface')->getMock();
		$this->inject($this->ajaxWidgetComponent, 'objectManager', $this->mockObjectManager);

		$this->mockComponentContext = $this->getMockBuilder('TYPO3\Flow\Http\Component\ComponentContext')->disableOriginalConstructor()->getMock();

		$this->mockHttpRequest = $this->getMockBuilder('TYPO3\Flow\Http\Request')->disableOriginalConstructor()->getMock();
		$this->mockHttpRequest->expects($this->any())->method('getArguments')->will($this->returnValue(array()));
		$this->mockComponentContext->expects($this->any())->method('getHttpRequest')->will($this->returnValue($this->mockHttpRequest));

		$this->mockHttpResponse = $this->getMockBuilder('TYPO3\Flow\Http\Response')->disableOriginalConstructor()->getMock();
		$this->mockComponentContext->expects($this->any())->method('getHttpResponse')->will($this->returnValue($this->mockHttpResponse));

		$this->mockAjaxWidgetContextHolder = $this->getMockBuilder('TYPO3\Fluid\Core\Widget\AjaxWidgetContextHolder')->getMock();
		$this->inject($this->ajaxWidgetComponent, 'ajaxWidgetContextHolder', $this->mockAjaxWidgetContextHolder);

		$this->mockHashService = $this->getMockBuilder('TYPO3\Flow\Security\Cryptography\HashService')->getMock();
		$this->inject($this->ajaxWidgetComponent, 'hashService', $this->mockHashService);

		$this->mockDispatcher = $this->getMockBuilder('TYPO3\Flow\Mvc\Dispatcher')->getMock();
		$this->inject($this->ajaxWidgetComponent, 'dispatcher', $this->mockDispatcher);

		$this->mockSecurityContext = $this->getMockBuilder('TYPO3\Flow\Security\Context')->getMock();
		$this->inject($this->ajaxWidgetComponent, 'securityContext', $this->mockSecurityContext);

		$this->mockPropertyMappingConfiguration = $this->getMockBuilder('TYPO3\Flow\Property\PropertyMappingConfiguration')->disableOriginalConstructor()->getMock();
		$this->inject($this->ajaxWidgetComponent, 'propertyMappingConfiguration', $this->mockPropertyMappingConfiguration);

		$this->mockPropertyMapper = $this->getMockBuilder('TYPO3\Flow\Property\PropertyMapper')->disableOriginalConstructor()->getMock();
		$this->mockPropertyMapper->expects($this->any())->method('convert')->with('', 'array', $this->mockPropertyMappingConfiguration)->will($this->returnValue(array()));
		$this->inject($this->ajaxWidgetComponent, 'propertyMapper', $this->mockPropertyMapper);

	}

	/**
	 * @test
	 */
	public function handleDoesNotCreateActionRequestIfHttpRequestContainsNoWidgetContext() {
		$this->mockHttpRequest->expects($this->at(0))->method('hasArgument')->with('__widgetId')->will($this->returnValue(FALSE));
		$this->mockHttpRequest->expects($this->at(1))->method('hasArgument')->with('__widgetContext')->will($this->returnValue(FALSE));

		$this->mockObjectManager->expects($this->never())->method('get');

		$this->ajaxWidgetComponent->handle($this->mockComponentContext);
	}

	/**
	 * @test
	 */
	public function handleSetsWidgetContextAndControllerObjectNameIfWidgetIdIsPresent() {
		$mockWidgetId = 'SomeWidgetId';
		$mockControllerObjectName = 'SomeControllerObjectName';
		$this->mockHttpRequest->expects($this->at(0))->method('hasArgument')->with('__widgetId')->will($this->returnValue(TRUE));
		$this->mockHttpRequest->expects($this->atLeastOnce())->method('getArgument')->with('__widgetId')->will($this->returnValue($mockWidgetId));
		$mockWidgetContext = $this->getMockBuilder('TYPO3\Fluid\Core\Widget\WidgetContext')->getMock();
		$mockWidgetContext->expects($this->atLeastOnce())->method('getControllerObjectName')->will($this->returnValue($mockControllerObjectName));
		$this->mockAjaxWidgetContextHolder->expects($this->atLeastOnce())->method('get')->with($mockWidgetId)->will($this->returnValue($mockWidgetContext));
		$mockActionRequest = $this->getMockBuilder('TYPO3\Flow\Mvc\ActionRequest')->disableOriginalConstructor()->getMock();
		$this->mockObjectManager->expects($this->atLeastOnce())->method('get')->with('TYPO3\Flow\Mvc\ActionRequest')->will($this->returnValue($mockActionRequest));

		$mockActionRequest->expects($this->once())->method('setArgument')->with('__widgetContext', $mockWidgetContext);
		$mockActionRequest->expects($this->once())->method('setControllerObjectName')->with($mockControllerObjectName);

		$this->ajaxWidgetComponent->handle($this->mockComponentContext);
	}

	/**
	 * @test
	 */
	public function handleDispatchesActionRequestIfWidgetContextIsPresent() {
		$mockWidgetId = 'SomeWidgetId';
		$mockControllerObjectName = 'SomeControllerObjectName';
		$this->mockHttpRequest->expects($this->at(0))->method('hasArgument')->with('__widgetId')->will($this->returnValue(TRUE));
		$this->mockHttpRequest->expects($this->atLeastOnce())->method('getArgument')->with('__widgetId')->will($this->returnValue($mockWidgetId));
		$mockWidgetContext = $this->getMockBuilder('TYPO3\Fluid\Core\Widget\WidgetContext')->getMock();
		$mockWidgetContext->expects($this->atLeastOnce())->method('getControllerObjectName')->will($this->returnValue($mockControllerObjectName));
		$this->mockAjaxWidgetContextHolder->expects($this->atLeastOnce())->method('get')->with($mockWidgetId)->will($this->returnValue($mockWidgetContext));
		$mockActionRequest = $this->getMockBuilder('TYPO3\Flow\Mvc\ActionRequest')->disableOriginalConstructor()->getMock();
		$this->mockObjectManager->expects($this->atLeastOnce())->method('get')->with('TYPO3\Flow\Mvc\ActionRequest')->will($this->returnValue($mockActionRequest));

		$this->mockDispatcher->expects($this->once())->method('dispatch')->with($mockActionRequest, $this->mockHttpResponse);

		$this->ajaxWidgetComponent->handle($this->mockComponentContext);
	}

	/**
	 * @test
	 */
	public function handleCancelsComponentChainIfWidgetContextIsPresent() {
		$mockWidgetId = 'SomeWidgetId';
		$mockControllerObjectName = 'SomeControllerObjectName';
		$this->mockHttpRequest->expects($this->at(0))->method('hasArgument')->with('__widgetId')->will($this->returnValue(TRUE));
		$this->mockHttpRequest->expects($this->atLeastOnce())->method('getArgument')->with('__widgetId')->will($this->returnValue($mockWidgetId));
		$mockWidgetContext = $this->getMockBuilder('TYPO3\Fluid\Core\Widget\WidgetContext')->getMock();
		$mockWidgetContext->expects($this->atLeastOnce())->method('getControllerObjectName')->will($this->returnValue($mockControllerObjectName));
		$this->mockAjaxWidgetContextHolder->expects($this->atLeastOnce())->method('get')->with($mockWidgetId)->will($this->returnValue($mockWidgetContext));
		$mockActionRequest = $this->getMockBuilder('TYPO3\Flow\Mvc\ActionRequest')->disableOriginalConstructor()->getMock();
		$this->mockObjectManager->expects($this->atLeastOnce())->method('get')->with('TYPO3\Flow\Mvc\ActionRequest')->will($this->returnValue($mockActionRequest));

		$this->mockComponentContext->expects($this->once())->method('setParameter')->with('TYPO3\Flow\Http\Component\ComponentChain', 'cancel', TRUE);

		$this->ajaxWidgetComponent->handle($this->mockComponentContext);
	}

	/**
	 * @test
	 */
	public function handleInjectsActionRequestToSecurityContext() {
		$mockWidgetId = 'SomeWidgetId';
		$mockControllerObjectName = 'SomeControllerObjectName';
		$this->mockHttpRequest->expects($this->at(0))->method('hasArgument')->with('__widgetId')->will($this->returnValue(TRUE));
		$this->mockHttpRequest->expects($this->atLeastOnce())->method('getArgument')->with('__widgetId')->will($this->returnValue($mockWidgetId));
		$mockWidgetContext = $this->getMockBuilder('TYPO3\Fluid\Core\Widget\WidgetContext')->getMock();
		$mockWidgetContext->expects($this->atLeastOnce())->method('getControllerObjectName')->will($this->returnValue($mockControllerObjectName));
		$this->mockAjaxWidgetContextHolder->expects($this->atLeastOnce())->method('get')->with($mockWidgetId)->will($this->returnValue($mockWidgetContext));
		$mockActionRequest = $this->getMockBuilder('TYPO3\Flow\Mvc\ActionRequest')->disableOriginalConstructor()->getMock();
		$this->mockObjectManager->expects($this->atLeastOnce())->method('get')->with('TYPO3\Flow\Mvc\ActionRequest')->will($this->returnValue($mockActionRequest));


		$this->mockSecurityContext->expects($this->once())->method('setRequest')->with($mockActionRequest);

		$this->ajaxWidgetComponent->handle($this->mockComponentContext);
	}

	/**
	 * @test
	 */
	public function extractWidgetContextDecodesSerializedWidgetContextIfPresent() {
		$ajaxWidgetComponent = $this->getAccessibleMock('TYPO3\Fluid\Core\Widget\AjaxWidgetComponent', array('dummy'));
		$this->inject($ajaxWidgetComponent, 'hashService', $this->mockHashService);

		$mockWidgetContext = 'SomeWidgetContext';
		$mockSerializedWidgetContext = base64_encode(serialize($mockWidgetContext));
		$mockSerializedWidgetContextWithHmac = $mockSerializedWidgetContext . 'HMAC';
		$this->mockHttpRequest->expects($this->at(0))->method('hasArgument')->with('__widgetId')->will($this->returnValue(FALSE));
		$this->mockHttpRequest->expects($this->at(1))->method('hasArgument')->with('__widgetContext')->will($this->returnValue(TRUE));
		$this->mockHttpRequest->expects($this->atLeastOnce())->method('getArgument')->with('__widgetContext')->will($this->returnValue($mockSerializedWidgetContextWithHmac));
		$this->mockHashService->expects($this->atLeastOnce())->method('validateAndStripHmac')->with($mockSerializedWidgetContextWithHmac)->will($this->returnValue($mockSerializedWidgetContext));

		$actualResult = $ajaxWidgetComponent->_call('extractWidgetContext', $this->mockHttpRequest);
		$this->assertEquals($mockWidgetContext, $actualResult);
	}

}
?>