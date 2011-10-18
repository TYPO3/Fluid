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
 * Testcase for AbstractWidgetViewHelper
 *
 */
class AbstractWidgetViewHelperTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @var TYPO3\Fluid\Core\Widget\AbstractWidgetViewHelper
	 */
	protected $viewHelper;

	/**
	 * @var TYPO3\Fluid\Core\Widget\AjaxWidgetContextHolder
	 */
	protected $ajaxWidgetContextHolder;

	/**
	 * @var TYPO3\Fluid\Core\Widget\WidgetContext
	 */
	protected $widgetContext;

	/**
	 * @var TYPO3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var TYPO3\FLOW3\MVC\Controller\ControllerContext
	 */
	protected $controllerContext;

	/**
	 * @var TYPO3\FLOW3\MVC\Web\Request
	 */
	protected $request;

	/**
	 */
	public function setUp() {
		$this->viewHelper = $this->getAccessibleMock('TYPO3\Fluid\Core\Widget\AbstractWidgetViewHelper', array('validateArguments', 'initialize', 'callRenderMethod', 'getWidgetConfiguration', 'getRenderingContext'));

		$this->ajaxWidgetContextHolder = $this->getMock('TYPO3\Fluid\Core\Widget\AjaxWidgetContextHolder');
		$this->viewHelper->injectAjaxWidgetContextHolder($this->ajaxWidgetContextHolder);

		$this->widgetContext = $this->getMock('TYPO3\Fluid\Core\Widget\WidgetContext');
		$this->viewHelper->injectWidgetContext($this->widgetContext);

		$this->objectManager = $this->getMock('TYPO3\FLOW3\Object\ObjectManagerInterface');
		$this->viewHelper->injectObjectManager($this->objectManager);

		$this->controllerContext = $this->getMock('TYPO3\FLOW3\MVC\Controller\ControllerContext', array(), array(), '', FALSE);
		$this->viewHelper->_set('controllerContext', $this->controllerContext);

		$this->request = $this->getMock('TYPO3\FLOW3\MVC\Web\Request');
	}

	/**
	 * @test
	 */
	public function initializeArgumentsAndRenderCallsTheRightSequenceOfMethods() {
		$this->callViewHelper();
	}

	/**
	 * @test
	 */
	public function initializeArgumentsAndRenderStoresTheWidgetContextIfInAjaxMode() {
		$this->viewHelper->_set('ajaxWidget', TRUE);
		$this->ajaxWidgetContextHolder->expects($this->once())->method('store')->with($this->widgetContext);

		$this->callViewHelper();
	}

	/**
	 * Calls the ViewHelper, and emulates a rendering.
	 *
	 * @return void
	 */
	public function callViewHelper() {
		$viewHelperVariableContainer = $this->getMock('TYPO3\Fluid\Core\ViewHelper\ViewHelperVariableContainer');
		$renderingContext = new \TYPO3\Fluid\Core\Rendering\RenderingContext();
		$renderingContext->injectViewHelperVariableContainer($viewHelperVariableContainer);
		$this->viewHelper->setRenderingContext($renderingContext);

		$this->viewHelper->expects($this->any())->method('getWidgetConfiguration')->will($this->returnValue('Some Widget Configuration'));
		$this->widgetContext->expects($this->once())->method('setNonAjaxWidgetConfiguration')->with('Some Widget Configuration');

		$this->widgetContext->expects($this->once())->method('setWidgetIdentifier')->with('@widget_0');

		$this->viewHelper->_set('controller', new \stdClass());
		$this->widgetContext->expects($this->once())->method('setControllerObjectName')->with('stdClass');

		$this->viewHelper->expects($this->once())->method('validateArguments');
		$this->viewHelper->expects($this->once())->method('initialize');
		$this->viewHelper->expects($this->once())->method('callRenderMethod')->will($this->returnValue('renderedResult'));
		$output = $this->viewHelper->initializeArgumentsAndRender(array('arg1' => 'val1'));
		$this->assertEquals('renderedResult', $output);
	}

	/**
	 * @test
	 */
	public function setChildNodesAddsChildNodesToWidgetContext() {
		$node1 = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\AbstractNode');
		$node2 = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode', array(), array(), '', FALSE);
		$node3 = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\AbstractNode');

		$rootNode = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode');
		$rootNode->expects($this->at(0))->method('addChildNode')->with($node1);
		$rootNode->expects($this->at(1))->method('addChildNode')->with($node2);
		$rootNode->expects($this->at(2))->method('addChildNode')->with($node3);

		$this->objectManager->expects($this->once())->method('create')->with('TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode')->will($this->returnValue($rootNode));

		$renderingContext = $this->getMock('TYPO3\Fluid\Core\Rendering\RenderingContextInterface');
		$this->viewHelper->_set('renderingContext', $renderingContext);

		$this->widgetContext->expects($this->once())->method('setViewHelperChildNodes')->with($rootNode, $renderingContext);
		$this->viewHelper->setChildNodes(array($node1, $node2, $node3));
	}

	/**
	 * @test
	 * @expectedException TYPO3\Fluid\Core\Widget\Exception\MissingControllerException
	 */
	public function initiateSubRequestThrowsExceptionIfControllerIsNoWidgetController() {
		$controller = $this->getMock('TYPO3\FLOW3\MVC\Controller\ControllerInterface');
		$this->viewHelper->_set('controller', $controller);

		$this->viewHelper->_call('initiateSubRequest');
	}

	/**
	 * @test
	 */
	public function initiateSubRequestBuildsRequestProperly() {
		$controller = $this->getMock('TYPO3\Fluid\Core\Widget\AbstractWidgetController', array(), array(), '', FALSE);
		$this->viewHelper->_set('controller', $controller);

		// Initial Setup
		$widgetRequest = $this->getMock('TYPO3\FLOW3\MVC\Web\SubRequest', array('setControllerObjectName', 'setArguments', 'setArgument', 'setControllerActionName'), array($this->getMock('TYPO3\FLOW3\MVC\Web\Request')));
		$response = $this->getMock('TYPO3\FLOW3\MVC\Web\Response');
		$this->objectManager->expects($this->at(0))->method('create')->with('TYPO3\FLOW3\MVC\Web\SubRequest')->will($this->returnValue($widgetRequest));
		$this->objectManager->expects($this->at(1))->method('create')->with('TYPO3\FLOW3\MVC\Web\Response')->will($this->returnValue($response));

		// Widget Context is set
		$widgetRequest->expects($this->once())->method('setArgument')->with('__widgetContext', $this->widgetContext);

		// The namespaced arguments are passed to the sub-request
		// and the action name is exctracted from the namespace.
		$this->controllerContext->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));
		$this->widgetContext->expects($this->any())->method('getWidgetIdentifier')->will($this->returnValue('widget-1'));
		$this->request->expects($this->once())->method('getArguments')->will($this->returnValue(array(
			'k1' => 'k2',
			'widget-1' => array(
				'arg1' => 'val1',
				'arg2' => 'val2',
				'action' => 'myAction'
			)
		)));
		$widgetRequest->expects($this->once())->method('setArguments')->with(array(
			'arg1' => 'val1',
			'arg2' => 'val2'
		));
		$widgetRequest->expects($this->once())->method('setControllerActionName')->with('myAction');

		// Controller is called
		$controller->expects($this->once())->method('processRequest')->with($widgetRequest, $response);
		$output = $this->viewHelper->_call('initiateSubRequest');

		// SubResponse is returned
		$this->assertSame($response, $output);
	}

	/**
	 * @test
	 */
	public function initiateSubRequestSetsIndexActionIfNoActionSet() {
		$controller = $this->getMock('TYPO3\Fluid\Core\Widget\AbstractWidgetController', array(), array(), '', FALSE);
		$this->viewHelper->_set('controller', $controller);

		// Initial Setup
		$widgetRequest = $this->getMock('TYPO3\FLOW3\MVC\Web\SubRequest', array('setControllerObjectName', 'setArguments', 'setArgument', 'setControllerActionName'), array($this->getMock('TYPO3\FLOW3\MVC\Web\Request')));
		$response = $this->getMock('TYPO3\FLOW3\MVC\Web\Response');
		$this->objectManager->expects($this->at(0))->method('create')->with('TYPO3\FLOW3\MVC\Web\SubRequest')->will($this->returnValue($widgetRequest));
		$this->objectManager->expects($this->at(1))->method('create')->with('TYPO3\FLOW3\MVC\Web\Response')->will($this->returnValue($response));

		// Widget Context is set
		$widgetRequest->expects($this->once())->method('setArgument')->with('__widgetContext', $this->widgetContext);

		// The namespaced arguments are passed to the sub-request
		// and the action name is exctracted from the namespace.
		$this->controllerContext->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));
		$this->widgetContext->expects($this->any())->method('getWidgetIdentifier')->will($this->returnValue('widget-1'));
		$this->request->expects($this->once())->method('getArguments')->will($this->returnValue(array(
			'k1' => 'k2',
			'widget-1' => array(
				'arg1' => 'val1',
				'arg2' => 'val2',
			)
		)));
		$widgetRequest->expects($this->once())->method('setControllerActionName')->with('index');

		$this->viewHelper->_call('initiateSubRequest');
	}
}
?>