<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\CastingExpressionNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithToArray;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * Class CastingExpressionNodeTest
 */
class CastingExpressionNodeTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function testEvaluateDelegatesToEvaluteExpression() {
		$subject = $this->getMock(
			CastingExpressionNode::class,
			array('dummy'),
			array('{test as string}', array('test as string'))
		);
		$view = new TemplateView();
		$context = new RenderingContext($view);
		$context->setVariableProvider(new StandardVariableProvider(array('test' => 10)));
		$result = $subject->evaluate($context);
		$this->assertSame('10', $result);
	}

	/**
	 * @test
	 */
	public function testEvaluateInvalidExpressionThrowsException() {
		$view = new TemplateView();
		$renderingContext = new RenderingContext($view);
		$renderingContext->setVariableProvider(new StandardVariableProvider());
		$this->setExpectedException(Exception::class);
		$result = CastingExpressionNode::evaluateExpression($renderingContext, 'suchaninvalidexpression as 1', array());
	}

	/**
	 * @dataProvider getEvaluateExpressionTestValues
	 * @param string $expression
	 * @param array $variables
	 * @param mixed $expected
	 */
	public function testEvaluateExpression($expression, array $variables, $expected) {
		$view = new TemplateView();
		$renderingContext = new RenderingContext($view);
		$renderingContext->setVariableProvider(new StandardVariableProvider($variables));
		$result = CastingExpressionNode::evaluateExpression($renderingContext, $expression, array());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return array
	 */
	public function getEvaluateExpressionTestValues() {
		$arrayIterator = new \ArrayIterator(array('foo', 'bar'));
		$toArrayObject = new UserWithToArray('foobar');
		return array(
			array('123 as string', array(), '123'),
			array('1 as boolean', array(), TRUE),
			array('0 as boolean', array(), FALSE),
			array('0 as array', array(), array(0)),
			array('1 as array', array(), array(1)),
			array('mystring as float', array('mystring' => '1.23'), 1.23),
			array('myvariable as integer', array('myvariable' => 321), 321),
			array('myinteger as string', array('myinteger' => 111), '111'),
			array('mydate as DateTime', array('mydate' => 90000), \DateTime::createFromFormat('U', 90000)),
			array('mydate as DateTime', array('mydate' => 'January'), new \DateTime('January')),
			array('1 as namestoredinvariables', array('namestoredinvariables' => 'boolean'), TRUE),
			array('mystring as array', array('mystring' => 'foo,bar'), array('foo', 'bar')),
			array('mystring as array', array('mystring' => 'foo , bar'), array('foo', 'bar')),
			array('myiterator as array', array('myiterator' => $arrayIterator), array('foo', 'bar')),
			array('myarray as array', array('myarray' => array('foo', 'bar')), array('foo', 'bar')),
			array('myboolean as array', array('myboolean' => TRUE), array()),
			array('myboolean as array', array('myboolean' => FALSE), array()),
			array('myobject as array', array('myobject' => $toArrayObject), array('name' => 'foobar')),
		);
	}

}
