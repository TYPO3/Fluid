<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper\Traits;

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
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\ConditionViewHelperTrait;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase;
use TYPO3Fluid\Fluid\ViewHelpers\IfViewHelper;

/**
 * Testcase for Condition ViewHelper Trait
 */
class ConditionViewHelperTraitTest extends ViewHelperBaseTestcase {

	/**
	 * @var ConditionViewHelperTrait|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $viewHelper;

	/**
	 * @var RenderingContextInterface|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $renderingContext;

	public function setUp() {
		parent::setUp();
		$this->renderingContext = $this->getMockBuilder('TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface')
			->getMockForAbstractClass();
		$this->viewHelper = $this->getMockBuilder('TYPO3Fluid\Fluid\Core\ViewHelper\Traits\ConditionViewHelperTrait')
			->setMethods(array('getRenderingContext', 'renderChildren', 'evaluateCondition'))
			->getMockForTrait();
		$property = new \ReflectionProperty($this->viewHelper, 'renderingContext');
		$property->setAccessible(TRUE);
		$property->setValue($this->viewHelper, $this->renderingContext);
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
			'TYPO3Fluid\\Fluid\\Core\\Compiler\\TemplateCompiler',
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
		$result = IfViewHelper::renderStatic($arguments, function() { return ''; }, $this->renderingContext);
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

		$method = new \ReflectionMethod($this->viewHelper, 'renderThenChild');
		$method->setAccessible(TRUE);
		$actualResult = $method->invoke($this->viewHelper);
		$this->assertEquals('foo', $actualResult);
	}

	/**
	 * @test
	 */
	public function renderThenChildReturnsThenViewHelperChildIfConditionIsTrueAndThenViewHelperChildExists() {
		$mockThenViewHelperNode = $this->getMock('TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('getViewHelperClassName', 'evaluate'), array(), '', FALSE);
		$mockThenViewHelperNode->expects($this->at(0))->method('getViewHelperClassName')->will($this->returnValue('TYPO3Fluid\Fluid\ViewHelpers\ThenViewHelper'));
		$mockThenViewHelperNode->expects($this->at(1))->method('evaluate')->with($this->renderingContext)->will($this->returnValue('ThenViewHelperResults'));

		$this->viewHelper->setChildNodes(array($mockThenViewHelperNode));
		$method = new \ReflectionMethod($this->viewHelper, 'renderThenChild');
		$method->setAccessible(TRUE);
		$actualResult = $method->invoke($this->viewHelper);
		$this->assertEquals('ThenViewHelperResults', $actualResult);
	}

	/**
	 * @test
	 */
	public function renderThenChildReturnsValueOfThenArgumentIfItIsSpecified() {
		$this->arguments['then'] = 'ThenArgument';
		$this->viewHelper->setArguments($this->arguments);

		$method = new \ReflectionMethod($this->viewHelper, 'renderThenChild');
		$method->setAccessible(TRUE);
		$actualResult = $method->invoke($this->viewHelper);
		$this->assertEquals('ThenArgument', $actualResult);
	}

	/**
	 * @test
	 */
	public function renderThenChildReturnsEmptyStringIfChildNodesOnlyContainElseViewHelper() {
		$mockElseViewHelperNode = $this->getMock('TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('getViewHelperClassName', 'evaluate'), array(), '', FALSE);
		$mockElseViewHelperNode->expects($this->any())->method('getViewHelperClassName')->will($this->returnValue('TYPO3Fluid\Fluid\ViewHelpers\ElseViewHelper'));
		$this->viewHelper->setChildNodes(array($mockElseViewHelperNode));
		$this->viewHelper->expects($this->never())->method('renderChildren')->will($this->returnValue('Child nodes'));

		$method = new \ReflectionMethod($this->viewHelper, 'renderThenChild');
		$method->setAccessible(TRUE);
		$actualResult = $method->invoke($this->viewHelper);
		$this->assertEquals('', $actualResult);
	}

	/**
	 * @test
	 */
	public function renderElseChildReturnsEmptyStringIfConditionIsFalseAndNoElseViewHelperChildExists() {
		$this->viewHelper->setChildNodes(array());
		$method = new \ReflectionMethod($this->viewHelper, 'renderElseChild');
		$method->setAccessible(TRUE);
		$actualResult = $method->invoke($this->viewHelper);
		$this->assertEquals('', $actualResult);
	}

	/**
	 * @test
	 */
	public function renderElseChildRendersElseViewHelperChildIfConditionIsFalseAndNoThenViewHelperChildExists() {
		$mockElseViewHelperNode = $this->getMock('TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('getViewHelperClassName', 'evaluate', 'setRenderingContext'), array(), '', FALSE);
		$mockElseViewHelperNode->expects($this->at(0))->method('getViewHelperClassName')->will($this->returnValue('TYPO3Fluid\Fluid\ViewHelpers\ElseViewHelper'));
		$mockElseViewHelperNode->expects($this->at(1))->method('evaluate')->with($this->renderingContext)->will($this->returnValue('ElseViewHelperResults'));

		$this->viewHelper->setChildNodes(array($mockElseViewHelperNode));
		$method = new \ReflectionMethod($this->viewHelper, 'renderElseChild');
		$method->setAccessible(TRUE);
		$actualResult = $method->invoke($this->viewHelper);
		$this->assertEquals('ElseViewHelperResults', $actualResult);
	}

	/**
	 * @test
	 */
	public function thenArgumentHasPriorityOverChildNodesIfConditionIsTrue() {
		$mockThenViewHelperNode = $this->getMock('TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('getViewHelperClassName', 'evaluate', 'setRenderingContext'), array(), '', FALSE);
		$mockThenViewHelperNode->expects($this->never())->method('evaluate');

		$this->viewHelper->setChildNodes(array($mockThenViewHelperNode));

		$this->arguments['then'] = 'ThenArgument';
		$this->viewHelper->setArguments($this->arguments);

		$method = new \ReflectionMethod($this->viewHelper, 'renderThenChild');
		$method->setAccessible(TRUE);
		$actualResult = $method->invoke($this->viewHelper);
		$this->assertEquals('ThenArgument', $actualResult);
	}

	/**
	 * @test
	 */
	public function renderReturnsValueOfElseArgumentIfConditionIsFalse() {
		$this->arguments['else'] = 'ElseArgument';
		$this->viewHelper->setArguments($this->arguments);

		$method = new \ReflectionMethod($this->viewHelper, 'renderElseChild');
		$method->setAccessible(TRUE);
		$actualResult = $method->invoke($this->viewHelper);
		$this->assertEquals('ElseArgument', $actualResult);
	}

	/**
	 * @test
	 */
	public function elseArgumentHasPriorityOverChildNodesIfConditionIsFalse() {
		$mockElseViewHelperNode = $this->getMock('TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('getViewHelperClassName', 'evaluate', 'setRenderingContext'), array(), '', FALSE);
		$mockElseViewHelperNode->expects($this->any())->method('getViewHelperClassName')->will($this->returnValue('TYPO3Fluid\Fluid\ViewHelpers\ElseViewHelper'));
		$mockElseViewHelperNode->expects($this->never())->method('evaluate');

		$this->viewHelper->setChildNodes(array($mockElseViewHelperNode));

		$this->arguments['else'] = 'ElseArgument';
		$this->viewHelper->setArguments($this->arguments);

		$method = new \ReflectionMethod($this->viewHelper, 'renderElseChild');
		$method->setAccessible(TRUE);
		$actualResult = $method->invoke($this->viewHelper);
		$this->assertEquals('ElseArgument', $actualResult);
	}
}
