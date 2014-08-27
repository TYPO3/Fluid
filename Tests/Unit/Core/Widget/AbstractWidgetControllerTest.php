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
use TYPO3\Flow\Http\Request;
use TYPO3\Flow\Http\Response;
use TYPO3\Flow\Http\Uri;
use TYPO3\Flow\Tests\UnitTestCase;
use TYPO3\Fluid\Core\Widget\WidgetContext;

/**
 * Test case for AbstractWidgetController
 */
class AbstractWidgetControllerTest extends UnitTestCase {

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\Widget\Exception\WidgetContextNotFoundException
	 */
	public function processRequestShouldThrowExceptionIfWidgetContextNotFound() {
		$mockActionRequest = $this->getMockBuilder('TYPO3\Flow\Mvc\ActionRequest')->disableOriginalConstructor()->getMock();
		$mockActionRequest->expects($this->atLeastOnce())->method('getInternalArgument')->with('__widgetContext')->will($this->returnValue(NULL));
		$response = new Response();

		$abstractWidgetController = $this->getMock('TYPO3\Fluid\Core\Widget\AbstractWidgetController', array('dummy'), array(), '', FALSE);
		$abstractWidgetController->processRequest($mockActionRequest, $response);
	}

	/**
	 * @test
	 */
	public function processRequestShouldSetWidgetConfiguration() {
		$mockActionRequest = $this->getMockBuilder('TYPO3\Flow\Mvc\ActionRequest')->disableOriginalConstructor()->getMock();
		$mockResponse = $this->getMock('TYPO3\Flow\Http\Response');

		$httpRequest = Request::create(new Uri('http://localhost'));
		$mockActionRequest->expects($this->any())->method('getHttpRequest')->will($this->returnValue($httpRequest));

		$expectedWidgetConfiguration = array('foo' => uniqid());

		$widgetContext = new WidgetContext();
		$widgetContext->setAjaxWidgetConfiguration($expectedWidgetConfiguration);

		$mockActionRequest->expects($this->atLeastOnce())->method('getInternalArgument')->with('__widgetContext')->will($this->returnValue($widgetContext));

		$abstractWidgetController = $this->getAccessibleMock('TYPO3\Fluid\Core\Widget\AbstractWidgetController', array('resolveActionMethodName', 'initializeActionMethodArguments', 'initializeActionMethodValidators', 'mapRequestArgumentsToControllerArguments', 'detectFormat', 'resolveView', 'callActionMethod'));
		$abstractWidgetController->_set('mvcPropertyMappingConfigurationService', $this->getMock('TYPO3\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService'));

		$abstractWidgetController->processRequest($mockActionRequest, $mockResponse);

		$actualWidgetConfiguration = $abstractWidgetController->_get('widgetConfiguration');
		$this->assertEquals($expectedWidgetConfiguration, $actualWidgetConfiguration);
	}
}
