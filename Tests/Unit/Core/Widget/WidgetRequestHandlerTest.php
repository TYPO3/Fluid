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
 * Testcase for WidgetRequestHandler
 *
 */
class WidgetRequestHandlerTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @var TYPO3\Fluid\Core\Widget\WidgetRequestHandler
	 */
	protected $widgetRequestHandler;

	/**
	 * @var TYPO3\FLOW3\Utility\Environment
	 */
	protected $mockEnvironment;

	/**
	 */
	public function setUp() {
		$this->mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);

		$this->widgetRequestHandler = $this->getAccessibleMock('TYPO3\Fluid\Core\Widget\WidgetRequestHandler', array('dummy'), array(), '', FALSE);
		$this->widgetRequestHandler->_set('environment', $this->mockEnvironment);
	}

	/**
	 * @test
	 */
	public function canHandleRequestReturnsTrueIfCorrectGetParameterIsSet() {
		$this->mockEnvironment->expects($this->once())->method('getRawGetArguments')->will($this->returnValue(array('typo3-fluid-widget-id' => '123')));
		$this->assertTrue($this->widgetRequestHandler->canHandleRequest());
	}

	/**
	 * @test
	 */
	public function canHandleRequestReturnsFalsefGetParameterIsNotSet() {
		$this->mockEnvironment->expects($this->once())->method('getRawGetArguments')->will($this->returnValue(array('some-other-id' => '123')));
		$this->assertFalse($this->widgetRequestHandler->canHandleRequest());
	}

	/**
	 * @test
	 */
	public function priorityIsHigherThanDefaultRequestHandler() {
		$defaultWebRequestHandler = $this->getMock('TYPO3\FLOW3\MVC\Web\RequestHandler', array('dummy'), array(), '', FALSE);
		$this->assertTrue($this->widgetRequestHandler->getPriority() > $defaultWebRequestHandler->getPriority());
	}
}
?>