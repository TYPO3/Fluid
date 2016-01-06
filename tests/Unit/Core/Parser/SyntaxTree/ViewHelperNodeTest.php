<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\Exception as ParserException;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Fixtures\TestViewHelper;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\Fixtures\ChildNodeAccessFacetViewHelper;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
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
	 * @var ViewHelperResolver|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mockViewHelperResolver;

	/**
	 * Setup fixture
	 */
	public function setUp() {
		$this->renderingContext = new RenderingContextFixture();
		$this->mockViewHelperResolver = $this->getMock(ViewHelperResolver::class, array('resolveViewHelperClassName', 'createViewHelperInstanceFromClassName', 'getArgumentDefinitionsForViewHelper'));
		$this->mockViewHelperResolver->expects($this->once())->method('resolveViewHelperClassName')->with('f', 'vh')->willReturn(TestViewHelper::class);
		$this->mockViewHelperResolver->expects($this->once())->method('createViewHelperInstanceFromClassName')->with(TestViewHelper::class)->willReturn(new TestViewHelper());
		$this->mockViewHelperResolver->expects($this->once())->method('getArgumentDefinitionsForViewHelper')->willReturn(array(
			'foo' => new ArgumentDefinition('foo', 'string', 'Dummy required argument', TRUE)
		));
		$this->renderingContext->setViewHelperResolver($this->mockViewHelperResolver);
	}

	/**
	 * @test
	 */
	public function constructorSetsViewHelperAndArguments() {
		$arguments = array('foo' => 'bar');
		/** @var ViewHelperNode|\PHPUnit_Framework_MockObject_MockObject $viewHelperNode */
		$viewHelperNode = new ViewHelperNode($this->renderingContext, 'f', 'vh', $arguments, new ParsingState());

		$this->assertAttributeEquals($arguments, 'arguments', $viewHelperNode);
	}

	/**
	 * @test
	 */
	public function testEvaluateCallsInvoker() {
		$invoker = $this->getMock(ViewHelperInvoker::class, array('invoke'));
		$invoker->expects($this->once())->method('invoke')->willReturn('test');
		$this->renderingContext->setViewHelperInvoker($invoker);
		$node = new ViewHelperNode($this->renderingContext, 'f', 'vh', array('foo' => 'bar'), new ParsingState());
		$result = $node->evaluate($this->renderingContext);
		$this->assertEquals('test', $result);
	}

	/**
	 * @test
	 */
	public function testThrowsExceptionOnMissingRequiredArgument() {
		$this->setExpectedException(ParserException::class);
		new ViewHelperNode($this->renderingContext, 'f', 'vh', array('notfoo' => FALSE), new ParsingState());
	}

}
