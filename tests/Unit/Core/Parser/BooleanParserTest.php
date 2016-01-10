<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\BooleanParser;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;

/**
 * Testcase for BooleanNode
 */
class BooleanParserTest extends UnitTestCase {
	/**
	 * @var RenderingContextInterface
	 */
	protected $renderingContext;

	/**
	 * Setup fixture
	 */
	public function setUp() {
		$this->renderingContext = new RenderingContextFixture();
	}

	/**
	 * @test
	 * @dataProvider getSomeEvaluationTestValues
	 * @param string $comparison
	 * @param boolean $expected
	 */
	public function testSomeEvaluations($comparison, $expected, $variables = array()) {
		$parser = new BooleanParser();
		$this->assertEquals($expected, BooleanNode::convertToBoolean($parser->evaluate($comparison, $variables), $this->renderingContext), 'Expression: ' . $comparison);

		$compiledEvaluation = $parser->compile($comparison);
		$functionName = 'expression_' . md5($comparison . rand(0, 100000));
		eval('function ' . $functionName . '($context) {return ' . $compiledEvaluation . ';}');
		$this->assertEquals($expected, BooleanNode::convertToBoolean($functionName($variables), $this->renderingContext), 'compiled Expression: ' . $compiledEvaluation);
	}

	/**
	 * @return array
	 */
	public function getSomeEvaluationTestValues() {
		return array(
			array('(1 && false) || false || \'foobar\' == \'foobar\'', TRUE),

			array('0', FALSE),
			array('!(1)', FALSE),
			array('!1', FALSE),
			array('', FALSE),
			array('false', FALSE),
			array('FALSE', FALSE),
			array('fAlSe', FALSE),
			array('   false   ', FALSE),
			array('   FALSE   ', FALSE),
			array('     ', FALSE),
			array('\'foo\' == \'bar\'', FALSE),
			array('\'foo\' != \'foo\'', FALSE),

			array('1', TRUE),
			array('true', true),
			array('TRUE', true),
			array('tRuE', true),
			array('   true   ', true),
			array('   TRUE   ', true),
			array('\' FALSE \'', true),
			array('\' \\\'FALSE \'', true),
			array('\' \\"FALSE \'', true),
			array('foo', true),
			array('\'foo\' == \'foo\'', TRUE),
			array('\'foo\' != \'bar\'', TRUE),
			array('(1 && false) || false || \'foobar\' == \'foobar\'', TRUE),

			array('0 == \'0\'', TRUE, array()),
			array('0 == "0"', TRUE, array()),
			array('0 === \'0\'', FALSE, array()),

			array('1 == 1', TRUE),
			array('1 == 0', FALSE),
			array('1 >= 1', TRUE),
			array('1 <= 1', TRUE),
			array('1 >= 2', FALSE),
			array('2 <= 1', FALSE),

			array('1 > FALSE',  TRUE),
			array('FALSE > 0',  FALSE),

			array('2 % 2', FALSE),
			array('1 % 2', TRUE),

			array('0 && 1', FALSE),
			array('1 && 1', TRUE),
			array('0 || 0', FALSE),
			array('0 || 1', TRUE),
			array('(0 && 1) || 1', TRUE),
			array('(0 && 0) || 0', TRUE),
			array('(1 && 1) || 0', TRUE),

			// edge cases as per https://github.com/TYPO3Fluid/Fluid/issues/7
			array('\'foo\' == 0', TRUE),
			array('1.1 >= foo', TRUE),
			array('\'foo\' > 0', FALSE),

			array('{foo}', TRUE, array('foo' => TRUE)),
			array('{foo} == FALSE', TRUE, array('foo' => FALSE))
		);
	}

}
