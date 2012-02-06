<?php
namespace TYPO3\Fluid\Tests\Unit\Core\Widget;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Testcase for WidgetRouter
 */
class WidgetRouterTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @var \TYPO3\Fluid\Core\Widget\WidgetRouter
	 */
	protected $router;

	/**
	 * @var \TYPO3\Fluid\Core\Widget\AjaxWidgetContextHolder
	 */
	protected $mockAjaxWidgetContextHolder;

	/**
	 * @var \TYPO3\Fluid\Core\Widget\WidgetContext
	 */
	protected $mockWidgetContext;

	/**
	 * @var \TYPO3\FLOW3\Security\Cryptography\HashService
	 */
	protected $mockHashService;

	/**
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $mockObjectManager;

	/**
	 */
	public function setUp() {
		$this->router = new \TYPO3\Fluid\Core\Widget\WidgetRouter();

		$this->mockWidgetContext = $this->getMock('TYPO3\Fluid\Core\Widget\WidgetContext');
		$this->mockWidgetContext->expects($this->any())->method('getControllerObjectName')->will($this->returnValue('Foo\Bar\Widget\Controller\FooController'));

		$this->mockAjaxWidgetContextHolder = $this->getMock('TYPO3\Fluid\Core\Widget\AjaxWidgetContextHolder');
		$this->mockAjaxWidgetContextHolder->expects($this->any())->method('get')->will($this->returnValue($this->mockWidgetContext));
		$this->inject($this->router, 'ajaxWidgetContextHolder', $this->mockAjaxWidgetContextHolder);

		$this->mockHashService = $this->getMock('TYPO3\FLOW3\Security\Cryptography\HashService');
		$this->inject($this->router, 'hashService', $this->mockHashService);

		$this->mockObjectManager = $this->getMock('TYPO3\FLOW3\Object\ObjectManagerInterface', array(), array(), '', FALSE);
		$this->mockObjectManager->expects($this->any())->method('getCaseSensitiveObjectName')->will($this->returnArgument(0));
	}

	/**
	 * @test
	 */
	public function routeReturnsActionRequestWhichIsDerivedFromTheHttpRequest() {
		$httpRequest = $this->getMock('TYPO3\FLOW3\Http\Request', array('createActionRequest'), array(), '', FALSE);
		$expectedActionRequest = new \TYPO3\FLOW3\Mvc\ActionRequest($httpRequest);
		$this->inject($expectedActionRequest, 'objectManager', $this->mockObjectManager);
		$httpRequest->expects($this->once())->method('createActionRequest')->will($this->returnValue($expectedActionRequest));

		$expectedActionRequest->setArgument('__widgetId', 'SomeWidgetId');
		$actualActionRequest = $this->router->route($httpRequest);

		$this->assertSame($expectedActionRequest, $actualActionRequest);
	}

	/**
	 * @test
	 */
	public function routeGetsWidgetContextFromAjaxWidgetContextHolderIfWidgetIdIsSpecified() {
		$httpRequest = $this->getMock('TYPO3\FLOW3\Http\Request', array('createActionRequest'), array(), '', FALSE);
		$actionRequest = new \TYPO3\FLOW3\Mvc\ActionRequest($httpRequest);
		$this->inject($actionRequest, 'objectManager', $this->mockObjectManager);
		$httpRequest->expects($this->once())->method('createActionRequest')->will($this->returnValue($actionRequest));

		$actionRequest->setArgument('__widgetId', 'SomeWidgetId');

		$mockAjaxWidgetContextHolder = $this->getMock('TYPO3\Fluid\Core\Widget\AjaxWidgetContextHolder');
		$mockAjaxWidgetContextHolder->expects($this->once())->method('get')->with('SomeWidgetId')->will($this->returnValue($this->mockWidgetContext));
		$this->inject($this->router, 'ajaxWidgetContextHolder', $mockAjaxWidgetContextHolder);

		$this->router->route($httpRequest);
	}

	/**
	 * @test
	 */
	public function buildGetsWidgetContextFromRequestArgumentsIfWidgetIdIsNotSpecified() {
		$httpRequest = $this->getMock('TYPO3\FLOW3\Http\Request', array('createActionRequest'), array(), '', FALSE);
		$actionRequest = new \TYPO3\FLOW3\Mvc\ActionRequest($httpRequest);
		$this->inject($actionRequest, 'objectManager', $this->mockObjectManager);
		$httpRequest->expects($this->once())->method('createActionRequest')->will($this->returnValue($actionRequest));

		$widgetContext = new \TYPO3\Fluid\Core\Widget\WidgetContext();
		$widgetContext->setControllerObjectName('Foo\Bar\Widget\Controller\FooController');

		$serializedWidgetContext = serialize($widgetContext);
		$serializedWidgetContextWithHmac = $serializedWidgetContext . 'TheHmac';
		$actionRequest->setArgument('__widgetContext', $serializedWidgetContextWithHmac);

		$this->mockAjaxWidgetContextHolder->expects($this->never())->method('get');
		$this->mockHashService->expects($this->once())->method('validateAndStripHmac')->with($serializedWidgetContextWithHmac)->will($this->returnValue($serializedWidgetContext));

		$this->router->route($httpRequest);
	}
}
?>
