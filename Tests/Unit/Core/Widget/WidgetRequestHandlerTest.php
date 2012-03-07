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
	 * @var \TYPO3\Fluid\Core\Widget\WidgetRequestHandler
	 */
	protected $widgetRequestHandler;

	/**
	 * @var \TYPO3\FLOW3\Utility\Environment
	 */
	protected $mockEnvironment;

	/**
	 * Backup for $_GET
	 *
	 * @var array
	 */
	protected $getBackup;

	/**
	 * Backup for $_POST
	 *
	 * @var array
	 */
	protected $postBackup;

	/**
	 */
	public function setUp() {
		$this->getBackup = $_GET;
		$this->postBackup = $_POST;
		$this->mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);

		$this->widgetRequestHandler = $this->getAccessibleMock('TYPO3\Fluid\Core\Widget\WidgetRequestHandler', array('dummy'), array(), '', FALSE);
		$this->widgetRequestHandler->_set('environment', $this->mockEnvironment);
	}

	/**
	 */
	public function tearDown() {
		$_GET = $this->getBackup;
		$_POST = $this->postBackup;
	}

	/**
	 * @test
	 */
	public function canHandleRequestReturnsTrueIfCorrectGetParameterIsSet() {
		$_GET = array('__widgetId' => '123');
		$this->assertTrue($this->widgetRequestHandler->canHandleRequest());
	}

	/**
	 * @test
	 */
	public function canHandleRequestReturnsTrueIfCorrectPostParameterIsSet() {
		$_GET = array('some-other-id' => '123');
		$_POST = array('__widgetId' => '123');
		$this->assertTrue($this->widgetRequestHandler->canHandleRequest());
	}

	/**
	 * @test
	 */
	public function canHandleRequestReturnsFalsefGetParameterIsNotSet() {
		$_GET = array('some-other-id' => '123');
		$this->assertFalse($this->widgetRequestHandler->canHandleRequest());
	}

	/**
	 * @test
	 */
	public function canHandleRequestReturnsFalsefPostParameterIsNotSet() {
		$_POST = array('some-other-id' => '123');
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