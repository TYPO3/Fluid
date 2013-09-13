<?php
namespace TYPO3\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Mvc\Controller\ControllerContext;
use TYPO3\Flow\Object\ObjectManagerInterface;
use TYPO3\Flow\Tests\UnitTestCase;
use TYPO3\Fluid\Core\Parser\Fixtures\ChildNodeAccessFacetViewHelper;
use TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3\Fluid\Core\Rendering\RenderingContext;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer;
use TYPO3\Fluid\Core\ViewHelper\ViewHelperVariableContainer;

require_once(__DIR__ . '/../Fixtures/ChildNodeAccessFacetViewHelper.php');
require_once(__DIR__ . '/../../Fixtures/TestViewHelper.php');

/**
 * Testcase for \TYPO3\CMS\Fluid\Core\Parser\SyntaxTree\ViewHelperNode
 */
class ViewHelperNodeTest extends UnitTestCase {

	/**
	 * @var RenderingContext
	 */
	protected $renderingContext;

	/**
	 * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mockObjectManager;

	/**
	 * @var TemplateVariableContainer|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $templateVariableContainer;

	/**
	 * @var ControllerContext|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mockControllerContext;

	/**
	 * @var ViewHelperVariableContainer|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mockViewHelperVariableContainer;

	/**
	 * Setup fixture
	 */
	public function setUp() {
		$this->renderingContext = new RenderingContext();

		$this->mockObjectManager = $this->getMockBuilder('TYPO3\Flow\Object\ObjectManagerInterface')->getMock();
		$this->inject($this->renderingContext, 'objectManager', $this->mockObjectManager);

		$this->templateVariableContainer = $this->getMockBuilder('TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer')->disableOriginalConstructor()->getMock();
		$this->inject($this->renderingContext, 'templateVariableContainer', $this->templateVariableContainer);

		$this->mockControllerContext = $this->getMockBuilder('TYPO3\Flow\Mvc\Controller\ControllerContext')->disableOriginalConstructor()->getMock();
		$this->renderingContext->setControllerContext($this->mockControllerContext);

		$this->mockViewHelperVariableContainer = $this->getMock('TYPO3\Fluid\Core\ViewHelper\ViewHelperVariableContainer');
		$this->inject($this->renderingContext, 'viewHelperVariableContainer', $this->mockViewHelperVariableContainer);
	}

	/**
	 * @test
	 */
	public function constructorSetsViewHelperAndArguments() {
		$viewHelper = $this->getMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper');
		$arguments = array('foo' => 'bar');
		/** @var ViewHelperNode|\PHPUnit_Framework_MockObject_MockObject $viewHelperNode */
		$viewHelperNode = $this->getAccessibleMock('TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('dummy'), array($viewHelper, $arguments));

		$this->assertEquals(get_class($viewHelper), $viewHelperNode->getViewHelperClassName());
		$this->assertEquals($arguments, $viewHelperNode->_get('arguments'));
	}

	/**
	 * @test
	 */
	public function childNodeAccessFacetWorksAsExpected() {
		/** @var TextNode|\PHPUnit_Framework_MockObject_MockObject $childNode */
		$childNode = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode', array(), array('foo'));

		/** @var ChildNodeAccessFacetViewHelper|\PHPUnit_Framework_MockObject_MockObject $mockViewHelper */
		$mockViewHelper = $this->getMock('TYPO3\Fluid\Core\Parser\Fixtures\ChildNodeAccessFacetViewHelper', array('setChildNodes', 'initializeArguments', 'render', 'prepareArguments'));

		$viewHelperNode = new ViewHelperNode($mockViewHelper, array());
		$viewHelperNode->addChildNode($childNode);

		$mockViewHelper->expects($this->once())->method('setChildNodes')->with($this->equalTo(array($childNode)));

		$viewHelperNode->evaluate($this->renderingContext);
	}

	/**
	 * @test
	 */
	public function initializeArgumentsAndRenderIsCalledByViewHelperNode() {
		/** @var AbstractViewHelper|\PHPUnit_Framework_MockObject_MockObject $mockViewHelper */
		$mockViewHelper = $this->getMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('initializeArgumentsAndRender', 'prepareArguments'));
		$mockViewHelper->expects($this->once())->method('initializeArgumentsAndRender');

		$viewHelperNode = new ViewHelperNode($mockViewHelper, array());

		$viewHelperNode->evaluate($this->renderingContext);
	}

	/**
	 * @test
	 */
	public function initializeArgumentsAndRenderIsCalledWithCorrectArguments() {
		$arguments = array(
			'param0' => new ArgumentDefinition('param1', 'string', 'Hallo', TRUE, NULL, FALSE),
			'param1' => new ArgumentDefinition('param1', 'string', 'Hallo', TRUE, NULL, TRUE),
			'param2' => new ArgumentDefinition('param2', 'string', 'Hallo', TRUE, NULL, TRUE)
		);

		/** @var AbstractViewHelper|\PHPUnit_Framework_MockObject_MockObject $mockViewHelper */
		$mockViewHelper = $this->getMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('initializeArgumentsAndRender', 'prepareArguments'));
		$mockViewHelper->expects($this->any())->method('prepareArguments')->will($this->returnValue($arguments));
		$mockViewHelper->expects($this->once())->method('initializeArgumentsAndRender');

		$viewHelperNode = new ViewHelperNode($mockViewHelper, array(
			'param2' => new TextNode('b'),
			'param1' => new TextNode('a')
		));

		$viewHelperNode->evaluate($this->renderingContext);
	}

	/**
	 * @test
	 */
	public function evaluateMethodPassesRenderingContextToViewHelper() {
		/** @var AbstractViewHelper|\PHPUnit_Framework_MockObject_MockObject $mockViewHelper */
		$mockViewHelper = $this->getMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'validateArguments', 'prepareArguments', 'setRenderingContext'));
		$mockViewHelper->expects($this->once())->method('setRenderingContext')->with($this->renderingContext);

		$viewHelperNode = new ViewHelperNode($mockViewHelper, array());

		$viewHelperNode->evaluate($this->renderingContext);
	}

	/**
	 * @test
	 */
	public function multipleEvaluateCallsShareTheSameViewHelperInstance() {
		/** @var AbstractViewHelper|\PHPUnit_Framework_MockObject_MockObject $mockViewHelper */
		$mockViewHelper = $this->getMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'validateArguments', 'prepareArguments', 'setViewHelperVariableContainer'));
		$mockViewHelper->expects($this->exactly(2))->method('render')->will($this->returnValue('String'));

		$viewHelperNode = new ViewHelperNode($mockViewHelper, array());

		$viewHelperNode->evaluate($this->renderingContext);
		$viewHelperNode->evaluate($this->renderingContext);

		// dummy assertion to avoid "risky test" warning
		$this->assertTrue(TRUE);
	}
}
