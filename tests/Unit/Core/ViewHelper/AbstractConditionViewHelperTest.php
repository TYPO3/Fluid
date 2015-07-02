<?php
namespace NamelessCoder\Fluid\Tests\Unit\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\Core\Compiler\TemplateCompiler;
use NamelessCoder\Fluid\Core\Parser\ParsingState;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\RootNode;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use NamelessCoder\Fluid\Core\Rendering\RenderingContext;
use NamelessCoder\Fluid\Core\Rendering\RenderingContextInterface;
use NamelessCoder\Fluid\Core\ViewHelper\AbstractConditionViewHelper;
use NamelessCoder\Fluid\Core\ViewHelper\ViewHelperInterface;
use NamelessCoder\Fluid\Core\ViewHelper\ViewHelperResolver;
use NamelessCoder\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase;

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
		$this->viewHelper = $this->getAccessibleMock('NamelessCoder\Fluid\Core\ViewHelper\AbstractConditionViewHelper', array('getRenderingContext', 'renderChildren', 'hasArgument'));
		$this->viewHelper->expects($this->any())->method('getRenderingContext')->will($this->returnValue($this->renderingContext));
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
	}

	/**
	 * @test
	 * @dataProvider getCompileTestValues
	 * @param array $childNodes
	 * @param $expected
	 */
	public function testCompileReturnsAndAssignsExpectedVariables(array $childNodes, $expected) {
		$node = new ViewHelperNode(new ViewHelperResolver(), 'f', 'if', array(), new ParsingState());
		foreach ($childNodes as $childNode) {
			$node->addChildNode($childNode);
		}
		$compiler = $this->getMock(
			'NamelessCoder\\Fluid\\Core\\Compiler\\TemplateCompiler',
			array('wrapChildNodesInClosure', 'wrapViewHelperNodeArgumentEvaluationInClosure')
		);
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
		$resolver = new ViewHelperResolver();
		$state = new ParsingState();
		return array(
			array(
				array(),
				'foobar-args[\'__thenClosure\'] = foobar-closure;' . PHP_EOL
			),
			array(
				array(new ViewHelperNode($resolver, 'f', 'then', array(), $state)),
				'foobar-args[\'__thenClosure\'] = closure;' . PHP_EOL
			),
			array(
				array(new ViewHelperNode($resolver, 'f', 'else', array(), $state)),
				'foobar-args[\'__elseClosures\'][] = closure;' . PHP_EOL
			),
			array(
				array(
					new ViewHelperNode($resolver, 'f', 'then', array(), $state),
					new ViewHelperNode($resolver, 'f', 'else', array(), $state)
				),
				'foobar-args[\'__thenClosure\'] = closure;' . PHP_EOL .
				'foobar-args[\'__elseClosures\'][] = closure;' . PHP_EOL
			),
			array(
				array(
					new ViewHelperNode($resolver, 'f', 'then', array(), $state),
					new ViewHelperNode($resolver, 'f', 'else', array('if' => new BooleanNode(new RootNode())), $state),
					new ViewHelperNode($resolver, 'f', 'else', array(), $state)
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
		$viewHelper = $this->getAccessibleMock('NamelessCoder\Fluid\Core\ViewHelper\AbstractConditionViewHelper', array('dummy'));
		$viewHelper->setArguments($arguments);
		$viewHelper->setViewHelperNode(new ViewHelperNode(new ViewHelperResolver(), 'f', 'if', array(), new ParsingState()));
		$result = AbstractConditionViewHelper::renderStatic($arguments, function() { return ''; }, new RenderingContext());
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
		$mockThenViewHelperNode = $this->getMock('NamelessCoder\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('getViewHelperClassName', 'evaluate'), array(), '', FALSE);
		$mockThenViewHelperNode->expects($this->at(0))->method('getViewHelperClassName')->will($this->returnValue('NamelessCoder\Fluid\ViewHelpers\ThenViewHelper'));
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
		$mockElseViewHelperNode = $this->getMock('NamelessCoder\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('getViewHelperClassName', 'evaluate'), array(), '', FALSE);
		$mockElseViewHelperNode->expects($this->any())->method('getViewHelperClassName')->will($this->returnValue('NamelessCoder\Fluid\ViewHelpers\ElseViewHelper'));
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
		$mockElseViewHelperNode = $this->getMock('NamelessCoder\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('getViewHelperClassName', 'evaluate', 'setRenderingContext'), array(), '', FALSE);
		$mockElseViewHelperNode->expects($this->at(0))->method('getViewHelperClassName')->will($this->returnValue('NamelessCoder\Fluid\ViewHelpers\ElseViewHelper'));
		$mockElseViewHelperNode->expects($this->at(1))->method('evaluate')->with($this->renderingContext)->will($this->returnValue('ElseViewHelperResults'));

		$this->viewHelper->setChildNodes(array($mockElseViewHelperNode));
		$actualResult = $this->viewHelper->_call('renderElseChild');
		$this->assertEquals('ElseViewHelperResults', $actualResult);
	}

	/**
	 * @test
	 */
	public function thenArgumentHasPriorityOverChildNodesIfConditionIsTrue() {
		$mockThenViewHelperNode = $this->getMock('NamelessCoder\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('getViewHelperClassName', 'evaluate', 'setRenderingContext'), array(), '', FALSE);
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
		$mockElseViewHelperNode = $this->getMock('NamelessCoder\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('getViewHelperClassName', 'evaluate', 'setRenderingContext'), array(), '', FALSE);
		$mockElseViewHelperNode->expects($this->any())->method('getViewHelperClassName')->will($this->returnValue('NamelessCoder\Fluid\ViewHelpers\ElseViewHelper'));
		$mockElseViewHelperNode->expects($this->never())->method('evaluate');

		$this->viewHelper->setChildNodes(array($mockElseViewHelperNode));

		$this->viewHelper->expects($this->atLeastOnce())->method('hasArgument')->with('else')->will($this->returnValue(TRUE));
		$this->arguments['else'] = 'ElseArgument';
		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$actualResult = $this->viewHelper->_call('renderElseChild');
		$this->assertEquals('ElseArgument', $actualResult);
	}
}
