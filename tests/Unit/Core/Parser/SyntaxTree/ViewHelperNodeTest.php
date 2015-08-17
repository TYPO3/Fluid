<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\Fixtures\ChildNodeAccessFacetViewHelper;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;

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
		$this->renderingContext = new RenderingContext();

		$this->templateVariableContainer = $this->getMockBuilder('TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider')
			->disableOriginalConstructor()->getMock();
		$this->inject($this->renderingContext, 'variableProvider', $this->templateVariableContainer);

		$this->mockViewHelperVariableContainer = $this->getMock('TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer');
		$this->inject($this->renderingContext, 'viewHelperVariableContainer', $this->mockViewHelperVariableContainer);
	}

	/**
	 * @test
	 */
	public function constructorSetsViewHelperAndArguments() {
		$viewHelper = $this->getMock('TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper');
		$arguments = array('foo' => 'bar');
		$resolver = new ViewHelperResolver();
		/** @var ViewHelperNode|\PHPUnit_Framework_MockObject_MockObject $viewHelperNode */
		$viewHelperNode = $this->getAccessibleMock(
			'TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode',
			array('dummy'),
			array($resolver, 'f', 'if', $arguments, new ParsingState())
		);

		$this->assertEquals($arguments, $viewHelperNode->_get('arguments'));
	}

	/**
	 * @test
	 */
	public function testEvaluateCallsInvoker() {
		$resolver = $this->getMock('TYPO3Fluid\\Fluid\\Core\\ViewHelper\\ViewHelperResolver', array('resolveViewHelperInvoker'));
		$invoker = $this->getMock('TYPO3Fluid\\Fluid\\Core\\ViewHelper\\ViewHelperInvoker', array('invoke'), array($resolver));
		$resolver->expects($this->once())->method('resolveViewHelperInvoker')->willReturn($invoker);
		$invoker->expects($this->once())->method('invoke')->willReturn('test');
		$node = new ViewHelperNode($resolver, 'f', 'count', array(), new ParsingState());
		$context = new RenderingContext();
		$context->setViewHelperResolver($resolver);
		$result = $node->evaluate($context);
		$this->assertEquals('test', $result);
	}

}
