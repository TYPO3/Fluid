<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\Fixtures\ChildNodeAccessFacetViewHelper;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * Testcase for \TYPO3Fluid\CMS\Fluid\Core\Parser\SyntaxTree\ViewHelperNode
 */
class ViewHelperNodeTest extends UnitTestCase {

	/**
	 * @var RenderingContext
	 */
	protected $renderingContext;

	/**
	 * @var TemplateVariableContainer|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $templateVariableContainer;

	/**
	 * @var ViewHelperVariableContainer|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mockViewHelperVariableContainer;

	/**
	 * Setup fixture
	 */
	public function setUp() {
		$view = new TemplateView();
		$this->renderingContext = new RenderingContext($view);

		$this->templateVariableContainer = $this->getMockBuilder(StandardVariableProvider::class)
			->disableOriginalConstructor()->getMock();
		$this->inject($this->renderingContext, 'variableProvider', $this->templateVariableContainer);

		$this->mockViewHelperVariableContainer = $this->getMock(ViewHelperVariableContainer::class);
		$this->inject($this->renderingContext, 'viewHelperVariableContainer', $this->mockViewHelperVariableContainer);
	}

	/**
	 * @test
	 */
	public function constructorSetsViewHelperAndArguments() {
		$viewHelper = $this->getMock(AbstractViewHelper::class);
		$arguments = array('then' => 'test');
		/** @var ViewHelperNode|\PHPUnit_Framework_MockObject_MockObject $viewHelperNode */
		$viewHelperNode = $this->getAccessibleMock(
			ViewHelperNode::class,
			array('dummy'),
			array($this->renderingContext, 'f', 'if', $arguments, new ParsingState())
		);

		$this->assertEquals($arguments, $viewHelperNode->_get('arguments'));
	}

	/**
	 * @test
	 */
	public function testEvaluateCallsInvoker() {
		$resolver = $this->getMock(ViewHelperResolver::class, array('resolveViewHelperInvoker'));
		$invoker = $this->getMock(ViewHelperInvoker::class, array('invoke'));
		$invoker->expects($this->once())->method('invoke')->willReturn('test');
		$this->renderingContext->setViewHelperResolver($resolver);
		$this->renderingContext->setViewHelperInvoker($invoker);
		$node = new ViewHelperNode($this->renderingContext, 'f', 'count', array(), new ParsingState());
		$result = $node->evaluate($this->renderingContext);
		$this->assertEquals('test', $result);
	}

}
