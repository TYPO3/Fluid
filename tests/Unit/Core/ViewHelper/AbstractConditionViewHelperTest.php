<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase;
use TYPO3Fluid\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\ViewHelpers\ElseViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\ThenViewHelper;

/**
 * Testcase for Condition ViewHelper
 */
class AbstractConditionViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @var AbstractConditionViewHelper|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $viewHelper;

	public function setUp() {
		parent::setUp();
		$this->viewHelper = $this->getAccessibleMock(AbstractConditionViewHelper::class, array('renderChildren', 'hasArgument'));
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
	}

	/**
	 * @test
	 * @dataProvider getCompileTestValues
	 * @param array $childNodes
	 * @param string $expected
	 */
	public function testCompileReturnsAndAssignsExpectedVariables(array $childNodes, $expected) {
		$node = new ViewHelperNode($this->renderingContext, 'f', 'if', array(), new ParsingState());
		foreach ($childNodes as $childNode) {
			$node->addChildNode($childNode);
		}
		$compiler = $this->getMock(
			TemplateCompiler::class,
			array('wrapChildNodesInClosure', 'wrapViewHelperNodeArgumentEvaluationInClosure')
		);
		$compiler->setRenderingContext($this->renderingContext);
		$compiler->expects($this->any())->method('wrapChildNodesInClosure')->willReturn('closure');
		$compiler->expects($this->any())->method('wrapViewHelperNodeArgumentEvaluationInClosure')->willReturn('arg-closure');
		$init = '';
		$this->viewHelper->compile('foobar-args', 'foobar-closure', $init, $node, $compiler);
		$this->assertEquals($expected, $init);
	}

	/**
	 * @return array
	 */
	public function getCompileTestValues() {
		$state = new ParsingState();
		$context = new RenderingContextFixture();
		return array(
			array(
				array(),
				'foobar-args[\'__thenClosure\'] = foobar-closure;' . PHP_EOL
			),
			array(
				array(new ViewHelperNode($context, 'f', 'then', array(), $state)),
				'foobar-args[\'__thenClosure\'] = closure;' . PHP_EOL
			),
			array(
				array(new ViewHelperNode($context, 'f', 'else', array(), $state)),
				'foobar-args[\'__elseClosures\'][] = closure;' . PHP_EOL
			),
			array(
				array(
					new ViewHelperNode($context, 'f', 'then', array(), $state),
					new ViewHelperNode($context, 'f', 'else', array(), $state)
				),
				'foobar-args[\'__thenClosure\'] = closure;' . PHP_EOL .
				'foobar-args[\'__elseClosures\'][] = closure;' . PHP_EOL
			),
			array(
				array(
					new ViewHelperNode($context, 'f', 'then', array(), $state),
					new ViewHelperNode($context, 'f', 'else', array('if' => new BooleanNode(new RootNode())), $state),
					new ViewHelperNode($context, 'f', 'else', array(), $state)
				),
				'foobar-args[\'__thenClosure\'] = closure;' . PHP_EOL .
				'foobar-args[\'__elseClosures\'][] = closure;' . PHP_EOL .
				'foobar-args[\'__elseifClosures\'][] = arg-closure;' . PHP_EOL .
				'foobar-args[\'__elseClosures\'][] = closure;' . PHP_EOL
			),
		);
	}

	/**
	 * @test
	 * @dataProvider getRenderFromArgumentsTestValues
	 * @param array $arguments
	 * @param $expected
	 */
	public function testRenderFromArgumentsReturnsExpectedValue(array $arguments, $expected) {
		$viewHelper = $this->getAccessibleMock(AbstractConditionViewHelper::class, array('dummy'));
		$viewHelper->setArguments($arguments);
		$viewHelper->setViewHelperNode(new ViewHelperNode($this->renderingContext, 'f', 'if', array(), new ParsingState()));
		$result = AbstractConditionViewHelper::renderStatic($arguments, function() { return ''; }, $this->renderingContext);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return array
	 */
	public function getRenderFromArgumentsTestValues() {
		return array(
			array(array('condition' => FALSE), NULL),
			array(array('condition' => TRUE, '__thenClosure' => function() { return 'foobar'; }), 'foobar'),
			array(array('condition' => TRUE, '__elseClosures' => array(function() { return 'foobar'; })), ''),
			array(array('condition' => TRUE), ''),
			array(array('condition' => TRUE), NULL),
			array(array('condition' => FALSE, '__elseClosures' => array(function() { return 'foobar'; })), 'foobar'),
			array(array('condition' => FALSE, '__elseifClosures' => array(
				function() { return FALSE; },
				function() { return TRUE; }
			), '__elseClosures' => array(
				function() { return 'baz'; },
				function() { return 'foobar'; }
			)), 'foobar'),
			array(array('condition' => FALSE, '__elseifClosures' => array(
				function() { return FALSE; },
				function() { return FALSE; }
			), '__elseClosures' => array(
				function() { return 'baz'; },
				function() { return 'foobar'; },
				function() { return 'barbar'; }
			)), 'barbar'),
			array(array('condition' => FALSE, '__thenClosure' => function() { return 'foobar'; }), ''),
			array(array('condition' => FALSE), ''),
		);
	}

	/**
	 * @test
	 */
	public function renderThenChildReturnsAllChildrenIfNoThenViewHelperChildExists() {
		$this->viewHelper->expects($this->any())->method('renderChildren')->will($this->returnValue('foo'));

		$actualResult = $this->viewHelper->_call('renderThenChild');
		$this->assertEquals('foo', $actualResult);
	}

	/**
	 * @test
	 */
	public function renderThenChildReturnsThenViewHelperChildIfConditionIsTrueAndThenViewHelperChildExists() {
		$mockThenViewHelperNode = $this->getMock(ViewHelperNode::class, array('getViewHelperClassName', 'evaluate'), array(), '', FALSE);
		$mockThenViewHelperNode->expects($this->at(0))->method('getViewHelperClassName')->will($this->returnValue(ThenViewHelper::class));
		$mockThenViewHelperNode->expects($this->at(1))->method('evaluate')->with($this->renderingContext)->will($this->returnValue('ThenViewHelperResults'));

		$this->viewHelper->setChildNodes(array($mockThenViewHelperNode));
		$actualResult = $this->viewHelper->_call('renderThenChild');
		$this->assertEquals('ThenViewHelperResults', $actualResult);
	}

	/**
	 * @test
	 */
	public function renderThenChildReturnsValueOfThenArgumentIfItIsSpecified() {
		$this->viewHelper->expects($this->atLeastOnce())->method('hasArgument')->with('then')->will($this->returnValue(TRUE));
		$this->arguments['then'] = 'ThenArgument';
		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$actualResult = $this->viewHelper->_call('renderThenChild');
		$this->assertEquals('ThenArgument', $actualResult);
	}

	/**
	 * @test
	 */
	public function renderThenChildReturnsEmptyStringIfChildNodesOnlyContainElseViewHelper() {
		$mockElseViewHelperNode = $this->getMock(ViewHelperNode::class, array('getViewHelperClassName', 'evaluate'), array(), '', FALSE);
		$mockElseViewHelperNode->expects($this->any())->method('getViewHelperClassName')->will($this->returnValue(ElseViewHelper::class));
		$this->viewHelper->setChildNodes(array($mockElseViewHelperNode));
		$this->viewHelper->expects($this->never())->method('renderChildren')->will($this->returnValue('Child nodes'));

		$actualResult = $this->viewHelper->_call('renderThenChild');
		$this->assertEquals('', $actualResult);
	}

	/**
	 * @test
	 */
	public function renderElseChildReturnsEmptyStringIfConditionIsFalseAndNoElseViewHelperChildExists() {
		$actualResult = $this->viewHelper->_call('renderElseChild');
		$this->assertEquals('', $actualResult);
	}

	/**
	 * @test
	 */
	public function renderElseChildRendersElseViewHelperChildIfConditionIsFalseAndNoThenViewHelperChildExists() {
		$mockElseViewHelperNode = $this->getMock(ViewHelperNode::class, array('getViewHelperClassName', 'evaluate', 'setRenderingContext'), array(), '', FALSE);
		$mockElseViewHelperNode->expects($this->at(0))->method('getViewHelperClassName')->will($this->returnValue(ElseViewHelper::class));
		$mockElseViewHelperNode->expects($this->at(1))->method('evaluate')->with($this->renderingContext)->will($this->returnValue('ElseViewHelperResults'));

		$this->viewHelper->setChildNodes(array($mockElseViewHelperNode));
		$actualResult = $this->viewHelper->_call('renderElseChild');
		$this->assertEquals('ElseViewHelperResults', $actualResult);
	}

	/**
	 * @test
	 */
	public function thenArgumentHasPriorityOverChildNodesIfConditionIsTrue() {
		$mockThenViewHelperNode = $this->getMock(ViewHelperNode::class, array('getViewHelperClassName', 'evaluate', 'setRenderingContext'), array(), '', FALSE);
		$mockThenViewHelperNode->expects($this->never())->method('evaluate');

		$this->viewHelper->setChildNodes(array($mockThenViewHelperNode));

		$this->viewHelper->expects($this->atLeastOnce())->method('hasArgument')->with('then')->will($this->returnValue(TRUE));
		$this->arguments['then'] = 'ThenArgument';

		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$actualResult = $this->viewHelper->_call('renderThenChild');
		$this->assertEquals('ThenArgument', $actualResult);
	}

	/**
	 * @test
	 */
	public function renderReturnsValueOfElseArgumentIfConditionIsFalse() {
		$this->viewHelper->expects($this->atLeastOnce())->method('hasArgument')->with('else')->will($this->returnValue(TRUE));
		$this->arguments['else'] = 'ElseArgument';
		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$actualResult = $this->viewHelper->_call('renderElseChild');
		$this->assertEquals('ElseArgument', $actualResult);
	}

	/**
	 * @test
	 */
	public function elseArgumentHasPriorityOverChildNodesIfConditionIsFalse() {
		$mockElseViewHelperNode = $this->getMock(ViewHelperNode::class, array('getViewHelperClassName', 'evaluate', 'setRenderingContext'), array(), '', FALSE);
		$mockElseViewHelperNode->expects($this->any())->method('getViewHelperClassName')->will($this->returnValue(ElseViewHelper::class));
		$mockElseViewHelperNode->expects($this->never())->method('evaluate');

		$this->viewHelper->setChildNodes(array($mockElseViewHelperNode));

		$this->viewHelper->expects($this->atLeastOnce())->method('hasArgument')->with('else')->will($this->returnValue(TRUE));
		$this->arguments['else'] = 'ElseArgument';
		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$actualResult = $this->viewHelper->_call('renderElseChild');
		$this->assertEquals('ElseArgument', $actualResult);
	}
}
