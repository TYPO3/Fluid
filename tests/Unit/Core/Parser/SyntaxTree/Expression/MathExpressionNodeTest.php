<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\MathExpressionNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * Class MathExpressionNodeTest
 */
class MathExpressionNodeTest extends UnitTestCase {

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
		$result = MathExpressionNode::evaluateExpression($renderingContext, $expression, array());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return array
	 */
	public function getEvaluateExpressionTestValues() {
		return array(
			array('1 gabbagabbahey 1', array(), 0),
			array('1 + 1', array(), 2),
			array('2 - 1', array(), 1),
			array('2 % 4', array(), 2),
			array('2 * 4', array(), 8),
			array('4 / 2', array(), 2),
			array('4 ^ 2', array(), 16),
			array('a + 1', array('a' => 1), 2),
			array('1 + b', array('b' => 1), 2),
			array('a + b', array('a' => 1, 'b' => 1), 2),
		);
	}

}
