<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\TernaryExpressionNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * Class TernaryExpressionNodeTest
 */
class TernaryExpressionNodeTest extends UnitTestCase {

	/**
	 * @dataProvider getTernaryExpressionDetection
	 * @param string $expression
	 * @param mixed $expected
	 */
	public function testTernaryExpressionDetection($expression, $expected) {
		$result = preg_match_all(TernaryExpressionNode::$detectionExpression, $expression, $matches, PREG_SET_ORDER);
		$this->assertEquals($expected, count($matches) > 0);
	}

	/**
	 * @return array
	 */
	public function getTernaryExpressionDetection() {
		return array(
			array('{true ? foo : bar}', TRUE),
			array('{true ? 1 : 0}', TRUE),
			array('{true ? foo : \'no\'}', TRUE),
			array('{(true) ? \'yes\' : \'no\'}', TRUE),
			array('{!(true) ? \'yes\' : \'no\'}', TRUE),
			array('{(true || false) ? \'yes\' : \'no\'}', TRUE),
			array('{(true && 1) ? \'yes\' : \'no\'}', TRUE),
			array('{(\'foo\' == \'foo\') ? \'yes\' : \'no\'}', TRUE),
			array('{(1 > 0) ? \'yes\' : \'no\'}', TRUE),
			array('{(1 < 0) ? \'yes\' : \'no\'}', TRUE),
			array('{(1 >= 0) ? \'yes\' : \'no\'}', TRUE),
			array('{(1 <= 0) ? \'yes\' : \'no\'}', TRUE),
			array('{(1 % 0) ? \'yes\' : \'no\'}', TRUE),
			array('{(true || (\'foo\' == \'bar\')) ? \'yes\' : \'no\'}', TRUE),
			array('{(foo || 1 && 1 && !(false) || (1 % 2) || (1 > 0) || (\'foo\' == \'bar\')) ? \'yes\' : \'no\'}', TRUE),
			array('{{f:if(condition: 1, then: 1, else: 0)} ? \'yes\' : \'no\'}', TRUE),
		);
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
		$result = TernaryExpressionNode::evaluateExpression($renderingContext, $expression, array());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return array
	 */
	public function getEvaluateExpressionTestValues() {
		return array(
			array('1 ? 2 : 3', array(), 2),
			array('0 ? 2 : 3', array(), 3),
		);
	}

}
