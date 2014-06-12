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

require_once(__DIR__ . '/ViewHelperBaseTestcase.php');

/**
 * Testcase for FlashMessagesViewHelper
 */
class FlashMessagesViewHelperTest extends \TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * @var \TYPO3\Fluid\ViewHelpers\FlashMessagesViewHelper
	 */
	protected $viewHelper;

	/**
	 * @var \TYPO3\Flow\Mvc\FlashMessageContainer
	 */
	protected $mockFlashMessageContainer;

	/**
	 * @var \TYPO3\Fluid\Core\ViewHelper\TagBuilder
	 */
	protected $mockTagBuilder;

	/**
	 * Sets up this test case
	 *
	 * @return void
	 */
	public function setUp() {
		$this->mockFlashMessageContainer = $this->getMock('TYPO3\Flow\Mvc\FlashMessageContainer');
		$mockControllerContext = $this->getMock('TYPO3\Flow\Mvc\Controller\ControllerContext', array(), array(), '', FALSE);
		$mockControllerContext->expects($this->any())->method('getFlashMessageContainer')->will($this->returnValue($this->mockFlashMessageContainer));

		$this->mockTagBuilder = $this->getMock('TYPO3\Fluid\Core\ViewHelper\TagBuilder');
		$this->viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\FlashMessagesViewHelper', array('dummy'));
		$this->viewHelper->_set('controllerContext', $mockControllerContext);
		$this->viewHelper->_set('tag', $this->mockTagBuilder);
		$this->viewHelper->initialize();
	}

	/**
	 * @test
	 */
	public function renderReturnsEmptyStringIfNoFlashMessagesAreInQueue() {
		$this->assertEmpty($this->viewHelper->render());
	}

	/**
	 * Data provider for renderTests()
	 */
	public function renderDataProvider() {
		return array(
			array(
				'<li class="flashmessages-ok">Some Flash Message</li>',
				array(new \TYPO3\Flow\Error\Message('Some Flash Message'))
			),
			array(
				'<li class="flashmessages-error">Error &quot;dynamic&quot; Flash Message</li>',
				array(new \TYPO3\Flow\Error\Error('Error %s Flash Message', NULL, array('"dynamic"')))
			),
			array(
				'<li class="flashmessages-error">Error Flash &quot;Message&quot;</li><li class="flashmessages-notice">Notice Flash Message</li>',
				array(new \TYPO3\Flow\Error\Error('Error Flash "Message"'), new \TYPO3\Flow\Error\Notice('Notice Flash Message'))
			),
			array(
				'<li class="flashmessages-warning"><h3>Some &quot;Warning&quot;</h3>Warning message body</li><li class="flashmessages-notice">Notice Flash Message</li>',
				array(new \TYPO3\Flow\Error\Warning('Warning message body', NULL, array(), 'Some "Warning"'), new \TYPO3\Flow\Error\Notice('Notice Flash Message'))
			),
			array(
				'<li class="customClass-ok">Message 01</li><li class="customClass-notice">Message 02</li>',
				array(new \TYPO3\Flow\Error\Message('Message 01'), new \TYPO3\Flow\Error\Notice('Message 02')),
				'customClass'
			),
		);
	}

	/**
	 * @test
	 * @dataProvider renderDataProvider()
	 * @param string $expectedResult
	 * @param array $flashMessages
	 * @param string $class
	 * @return void
	 */
	public function renderTests($expectedResult, array $flashMessages = array(), $class = NULL) {
		$this->mockFlashMessageContainer->expects($this->once())->method('getMessagesAndFlush')->will($this->returnValue($flashMessages));
		$this->mockTagBuilder->expects($this->once())->method('setContent')->with($expectedResult);
		if ($class !== NULL) {
			$this->viewHelper->_set('arguments', array('class' => $class));
		}
		$this->viewHelper->render();
	}
}
