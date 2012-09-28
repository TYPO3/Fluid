<?php
namespace TYPO3\Fluid\Tests\Unit\Core\Widget;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


/**
 * Testcase for AbstractWidgetController
 */
class AbstractWidgetControllerTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 * @expectedException TYPO3\Fluid\Core\Widget\Exception\WidgetContextNotFoundException
	 */
	public function processRequestShouldThrowExceptionIfWidgetContextNotFound() {
		$request = new \TYPO3\Flow\Mvc\ActionRequest(new \TYPO3\Flow\Mvc\ActionRequest(\TYPO3\Flow\Http\Request::create(new \TYPO3\Flow\Http\Uri('http://localhost/foo'))));
		$response = new \TYPO3\Flow\Http\Response();

		$abstractWidgetController = $this->getMock('TYPO3\Fluid\Core\Widget\AbstractWidgetController', array('dummy'), array(), '', FALSE);
		$abstractWidgetController->processRequest($request, $response);
	}

	/**
	 * @test
	 */
	public function processRequestShouldSetWidgetConfiguration() {
		$request = new \TYPO3\Flow\Mvc\ActionRequest(new \TYPO3\Flow\Mvc\ActionRequest(\TYPO3\Flow\Http\Request::create(new \TYPO3\Flow\Http\Uri('http://localhost/foo'))));
		$response = new \TYPO3\Flow\Http\Response();

		$widgetContext = $this->getMock('TYPO3\Fluid\Core\Widget\WidgetContext', array('getWidgetConfiguration'));
		$widgetContext->expects($this->once())->method('getWidgetConfiguration')->will($this->returnValue('myConfiguration'));

		$request->setArgument('__widgetContext', $widgetContext);

		$abstractWidgetController = $this->getAccessibleMock('TYPO3\Fluid\Core\Widget\AbstractWidgetController', array('resolveActionMethodName', 'initializeActionMethodArguments', 'initializeActionMethodValidators', 'mapRequestArgumentsToControllerArguments', 'detectFormat', 'resolveView', 'callActionMethod'));
		$abstractWidgetController->_set('argumentsMappingResults', new \TYPO3\Flow\Error\Result());
		$abstractWidgetController->_set('flashMessageContainer', new \TYPO3\Flow\Mvc\FlashMessageContainer());
		$abstractWidgetController->_set('mvcPropertyMappingConfigurationService', $this->getMock('TYPO3\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService'));

		$abstractWidgetController->processRequest($request, $response);

		$widgetConfiguration = $abstractWidgetController->_get('widgetConfiguration');
		$this->assertEquals('myConfiguration', $widgetConfiguration);
	}
}
?>