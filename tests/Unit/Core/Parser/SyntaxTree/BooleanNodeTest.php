<?php
namespace NamelessCoder\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\NumericNode;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\RootNode;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\TextNode;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use NamelessCoder\Fluid\Core\Rendering\RenderingContext;
use NamelessCoder\Fluid\Core\Rendering\RenderingContextInterface;
use NamelessCoder\Fluid\Core\Variables\StandardVariableProvider;
use NamelessCoder\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithToString;
use NamelessCoder\Fluid\Tests\UnitTestCase;

/**
 * Testcase for ViewHelperNode's evaluateBooleanExpression()
 */
class BooleanNodeTest extends UnitTestCase {

	/**
	 * @var ViewHelperNode
	 */
	protected $viewHelperNode;

	/**
	 * @var RenderingContextInterface
	 */
	protected $renderingContext;

	/**
	 * Setup fixture
	 */
	public function setUp() {
		$this->renderingContext = $this->getMock('NamelessCoder\Fluid\Core\Rendering\RenderingContextInterface');
	}

	/**
	 * @test
	 */
	public function testEvaluateThrowsExceptionOnInvalidComparator() {
		$this->setExpectedException('NamelessCoder\\Fluid\\Core\\Parser\\Exception');
		BooleanNode::evaluateComparator('<>', 1, 2);
	}

	/**
	 * @test
	 * @dataProvider getEvaluateComparatorTestValues
	 * @param string $comparator
	 * @param mixed $left
	 * @param mixed $right
	 * @param boolean $expected
	 */
	public function testEvaluateComparator($comparator, $left, $right, $expected) {
		$result = BooleanNode::evaluateComparator($comparator, $left, $right);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return array
	 */
	public function getEvaluateComparatorTestValues() {
		$user = new UserWithToString('foobar');
		return array(
			array('==', $user, $user, TRUE),
			array('==', $user, new UserWithToString('foobar'), FALSE),
			array('==', $user, 'foobar', TRUE),
			array('==', 'foobar', new UserWithToString('foobar'), TRUE),
			array('==', 1, 0, FALSE),
			array('==', 1, 1, TRUE),
			array('==', array('foobar'), array('foobar'), TRUE),
			array('==', array('foobar'), array('baz'), FALSE),
			array('>', 1, 0, TRUE),
			array('<', 1, 0, FALSE),
			array('>', 1, FALSE, FALSE),
			array('>', FALSE, 0, FALSE),
		);
	}

	/**
	 * @dataProvider getCreateFromNodeAndEvaluateTestValues
	 * @param NodeInterface $node
	 * @param boolean $expected
	 */
	public function testCreateFromNodeAndEvaluate(NodeInterface $node, $expected) {
		$result = BooleanNode::createFromNodeAndEvaluate($node, $this->renderingContext);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return array
	 */
	public function getCreateFromNodeAndEvaluateTestValues() {
		return array(
			'1 && 1' => array(new TextNode('1 && 1'), TRUE),
			'1 && 0' => array(new TextNode('1 && 0'), FALSE),
			'(1 && 1) && 1' => array(new TextNode('(1 && 1) && 1'), TRUE),
			'(1 && 0) || 1' => array(new TextNode('(1 && 0) || 1'), TRUE),
			'(\'yes\' == \'yes\') || 1 >= 0' => array(new TextNode('(\'yes\' == \'yes\') || 1 >= 0'), TRUE),
			'1 <= 0' => array(new TextNode('1 <= 0'), FALSE),
			'1 > 4' => array(new TextNode('1 > 4'), FALSE),
			'1 < 4' => array(new TextNode('1 < 4'), TRUE),
			'4 % 4' => array(new TextNode('4 % 4'), FALSE),
			'\'yes\' % 2' => array(new TextNode('\'yes\' % 2'), FALSE),
			'{0: 1, 1: 2} % 2' => array(new TextNode('{0: 1, 1: 2} % 2'), FALSE),
			'2 % 4' => array(new TextNode('2 % 4'), TRUE),
			'0 && 1' => array(new TextNode('0 && 1'), FALSE),
		);
	}

	/**
	 * @test
	 */
	public function comparingNestedComparisonsWorks() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('('));
		$rootNode->addChildNode(new ArrayNode(array('foo' => 'bar')));
		$rootNode->addChildNode(new TextNode('=='));
		$rootNode->addChildNode(new ArrayNode(array('foo' => 'bar')));
		$rootNode->addChildNode(new TextNode(')'));
		$rootNode->addChildNode(new TextNode('&&'));
		$rootNode->addChildNode(new ObjectAccessorNode('true'));
		$this->assertTrue(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 */
	public function comparingEqualNumbersReturnsTrue() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('5'));
		$rootNode->addChildNode(new TextNode('=='));
		$rootNode->addChildNode(new TextNode('5'));

		$booleanNode = new BooleanNode($rootNode);
		$this->assertTrue($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 */
	public function comparingUnequalNumbersReturnsFalse() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('5'));
		$rootNode->addChildNode(new TextNode('=='));
		$rootNode->addChildNode(new TextNode('3'));

		$booleanNode = new BooleanNode($rootNode);
		$this->assertFalse($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 */
	public function comparingEqualIdentityReturnsTrue() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('5'));
		$rootNode->addChildNode(new TextNode('==='));
		$rootNode->addChildNode(new TextNode('5'));

		$booleanNode = new BooleanNode($rootNode);
		$this->assertTrue($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 */
	public function comparingUnequalIdentityReturnsFalse() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('5'));
		$rootNode->addChildNode(new TextNode('==='));
		$rootNode->addChildNode(new NumericNode(5));

		$booleanNode = new BooleanNode($rootNode);
		$this->assertFalse($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 */
	public function notEqualReturnsFalseIfNumbersAreEqual() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('5'));
		$rootNode->addChildNode(new TextNode('!='));
		$rootNode->addChildNode(new TextNode('5'));

		$booleanNode = new BooleanNode($rootNode);
		$this->assertFalse($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 */
	public function notEqualReturnsTrueIfNumbersAreNotEqual() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('5'));
		$rootNode->addChildNode(new TextNode('!='));
		$rootNode->addChildNode(new TextNode('3'));

		$booleanNode = new BooleanNode($rootNode);
		$this->assertTrue($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 */
	public function oddNumberModulo2ReturnsTrue() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('43'));
		$rootNode->addChildNode(new TextNode('%'));
		$rootNode->addChildNode(new TextNode('2'));

		$booleanNode = new BooleanNode($rootNode);
		$this->assertTrue($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 */
	public function evenNumberModulo2ReturnsFalse() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('42'));
		$rootNode->addChildNode(new TextNode('%'));
		$rootNode->addChildNode(new TextNode('2'));

		$booleanNode = new BooleanNode($rootNode);
		$this->assertFalse($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 */
	public function greaterThanReturnsTrueIfNumberIsReallyGreater() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('10'));
		$rootNode->addChildNode(new TextNode('>'));
		$rootNode->addChildNode(new TextNode('9'));

		$booleanNode = new BooleanNode($rootNode);
		$this->assertTrue($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 */
	public function greaterThanReturnsFalseIfNumberIsEqual() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('10'));
		$rootNode->addChildNode(new TextNode('>'));
		$rootNode->addChildNode(new TextNode('10'));

		$booleanNode = new BooleanNode($rootNode);
		$this->assertFalse($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 */
	public function greaterOrEqualsReturnsTrueIfNumberIsReallyGreater() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('10'));
		$rootNode->addChildNode(new TextNode('>='));
		$rootNode->addChildNode(new TextNode('9'));

		$booleanNode = new BooleanNode($rootNode);
		$this->assertTrue($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 */
	public function greaterOrEqualsReturnsTrueIfNumberIsEqual() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('10'));
		$rootNode->addChildNode(new TextNode('>='));
		$rootNode->addChildNode(new TextNode('10'));
		$this->assertTrue(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 */
	public function greaterOrEqualsReturnFalseIfNumberIsSmaller() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('10'));
		$rootNode->addChildNode(new TextNode('>='));
		$rootNode->addChildNode(new TextNode('11'));
		$this->assertFalse(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 */
	public function lessThanReturnsTrueIfNumberIsReallyless() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('9'));
		$rootNode->addChildNode(new TextNode('<'));
		$rootNode->addChildNode(new TextNode('10'));
		$this->assertTrue(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 */
	public function lessThanReturnsFalseIfNumberIsEqual() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('10'));
		$rootNode->addChildNode(new TextNode('<'));
		$rootNode->addChildNode(new TextNode('10'));
		$this->assertFalse(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 */
	public function lessOrEqualsReturnsTrueIfNumberIsReallyLess() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('9'));
		$rootNode->addChildNode(new TextNode('<='));
		$rootNode->addChildNode(new TextNode('10'));
		$this->assertTrue(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 */
	public function lessOrEqualsReturnsTrueIfNumberIsEqual() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('10'));
		$rootNode->addChildNode(new TextNode('<='));
		$rootNode->addChildNode(new TextNode('10'));
		$this->assertTrue(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 */
	public function lessOrEqualsReturnFalseIfNumberIsBigger() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('11'));
		$rootNode->addChildNode(new TextNode('<='));
		$rootNode->addChildNode(new TextNode('10'));
		$this->assertFalse(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 */
	public function lessOrEqualsReturnFalseIfComparingWithANegativeNumber() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('11 <= -2.1'));
		$this->assertFalse(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
	}

	/**
	 * @param array $variables
	 * @return RenderingContext
	 */
	protected function getDummyRenderingContextWithVariables(array $variables) {
		$context = new RenderingContext();
		$context->getVariableProvider()->setSource($variables);
		return $context;
	}

	/**
	 * @test
	 */
	public function comparingVariableWithMatchedQuotedString() {
		$renderingContext = $this->getDummyRenderingContextWithVariables(array('test' => 'somevalue'));
		$rootNode = new RootNode();
		$rootNode->addChildNode(new ObjectAccessorNode('test'));
		$rootNode->addChildNode(new TextNode(' == '));
		$rootNode->addChildNode(new TextNode('\'somevalue\''));
		$this->assertTrue(BooleanNode::createFromNodeAndEvaluate($rootNode, $renderingContext));
	}

	/**
	 * @test
	 */
	public function comparingVariableWithUnmatchedQuotedString() {
		$renderingContext = $this->getDummyRenderingContextWithVariables(array('test' => 'somevalue'));
		$rootNode = new RootNode();
		$rootNode->addChildNode(new ObjectAccessorNode('test'));
		$rootNode->addChildNode(new TextNode(' != '));
		$rootNode->addChildNode(new TextNode('\'othervalue\''));
		$this->assertTrue(BooleanNode::createFromNodeAndEvaluate($rootNode, $renderingContext));
	}

	/**
	 * @test
	 */
	public function comparingNotEqualsVariableWithMatchedQuotedString() {
		$renderingContext = $this->getDummyRenderingContextWithVariables(array('test' => 'somevalue'));
		$rootNode = new RootNode();
		$rootNode->addChildNode(new ObjectAccessorNode('test'));
		$rootNode->addChildNode(new TextNode(' != '));
		$rootNode->addChildNode(new TextNode('\'somevalue\''));
		$this->assertFalse(BooleanNode::createFromNodeAndEvaluate($rootNode, $renderingContext));
	}

	/**
	 * @test
	 */
	public function comparingNotEqualsVariableWithUnmatchedQuotedString() {
		$renderingContext = $this->getDummyRenderingContextWithVariables(array('test' => 'somevalue'));
		$rootNode = new RootNode();
		$rootNode->addChildNode(new ObjectAccessorNode('test'));
		$rootNode->addChildNode(new TextNode(' != '));
		$rootNode->addChildNode(new TextNode('\'somevalue\''));
		$this->assertFalse(BooleanNode::createFromNodeAndEvaluate($rootNode, $renderingContext));
	}

	/**
	 * @test
	 */
	public function comparingEqualsVariableWithMatchedQuotedStringInSingleTextNode() {
		$renderingContext = $this->getDummyRenderingContextWithVariables(array('test' => 'somevalue'));
		$rootNode = new RootNode();
		$rootNode->addChildNode(new ObjectAccessorNode('test'));
		$rootNode->addChildNode(new TextNode(' != \'somevalue\''));
		$this->assertFalse(BooleanNode::createFromNodeAndEvaluate($rootNode, $renderingContext));
	}

	/**
	 * @test
	 */
	public function notEqualReturnsFalseIfComparingMatchingStrings() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('\'stringA\' != "stringA"'));
		$this->assertFalse(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 */
	public function notEqualReturnsTrueIfComparingNonMatchingStrings() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('\'stringA\' != \'stringB\''));
		$this->assertTrue(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 */
	public function equalsReturnsFalseIfComparingNonMatchingStrings() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('\'stringA\' == \'stringB\''));
		$this->assertFalse(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 */
	public function equalsReturnsTrueIfComparingMatchingStrings() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('\'stringA\' == "stringA"'));
		$this->assertTrue(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 */
	public function equalsReturnsTrueIfComparingMatchingStringsWithEscapedQuotes() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('\'\\\'stringA\\\'\' == \'\\\'stringA\\\'\''));
		$this->assertTrue(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 */
	public function equalsReturnsFalseIfComparingStringWithNonZero() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('\'stringA\' == 42'));
		$this->assertFalse(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 */
	public function equalsReturnsFalseIfComparingStringWithZero() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('\'stringA\' == 0'));
		$this->assertFalse(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 */
	public function equalsReturnsFalseIfComparingStringZeroWithZero() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('\'0\' == 0'));
		$this->assertTrue(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 */
	public function objectsAreComparedStrictly() {
		$object1 = new \stdClass();
		$object2 = new \stdClass();

		$rootNode = new RootNode();

		$object1Node = $this->getMock('NamelessCoder\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode', array('evaluate'), array('foo'));
		$object1Node->expects($this->any())->method('evaluate')->will($this->returnValue($object1));

		$object2Node = $this->getMock('NamelessCoder\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode', array('evaluate'), array('foo'));
		$object2Node->expects($this->any())->method('evaluate')->will($this->returnValue($object2));

		$rootNode->addChildNode($object1Node);
		$rootNode->addChildNode(new TextNode('=='));
		$rootNode->addChildNode($object2Node);

		$booleanNode = new BooleanNode($rootNode);
		$this->assertFalse($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 */
	public function objectsAreComparedStrictlyInUnequalComparison() {
		$object1 = new \stdClass();
		$object2 = new \stdClass();

		$rootNode = new RootNode();

		$object1Node = $this->getMock('NamelessCoder\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode', array('evaluate'), array('foo'));
		$object1Node->expects($this->any())->method('evaluate')->will($this->returnValue($object1));

		$object2Node = $this->getMock('NamelessCoder\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode', array('evaluate'), array('foo'));
		$object2Node->expects($this->any())->method('evaluate')->will($this->returnValue($object2));

		$rootNode->addChildNode($object1Node);
		$rootNode->addChildNode(new TextNode('!='));
		$rootNode->addChildNode($object2Node);

		$booleanNode = new BooleanNode($rootNode);
		$this->assertTrue($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 */
	public function convertToBooleanProperlyConvertsValuesOfTypeBoolean() {
		$this->assertFalse(BooleanNode::convertToBoolean(FALSE));
		$this->assertTrue(BooleanNode::convertToBoolean(TRUE));
	}

	/**
	 * @test
	 */
	public function convertToBooleanProperlyConvertsValuesOfTypeString() {
		$this->assertFalse(BooleanNode::convertToBoolean(''));
		$this->assertFalse(BooleanNode::convertToBoolean('false'));
		$this->assertFalse(BooleanNode::convertToBoolean('FALSE'));

		$this->assertTrue(BooleanNode::convertToBoolean('true'));
		$this->assertTrue(BooleanNode::convertToBoolean('TRUE'));
	}

	/**
	 * @param float $number
	 * @param boolean $expected
	 * @test
	 * @dataProvider getNumericBooleanTestValues
	 */
	public function convertToBooleanProperlyConvertsNumericValues($number, $expected) {
		$this->assertEquals($expected, BooleanNode::convertToBoolean($number));
	}

	/**
	 * @return array
	 */
	public function getNumericBooleanTestValues() {
		return array(
			array(0, FALSE),
			array(-1, FALSE),
			array('-1', FALSE),
			array(-.5, FALSE),
			array(1, TRUE),
			array(.5, TRUE),
		);
	}

	/**
	 * @test
	 */
	public function convertToBooleanProperlyConvertsValuesOfTypeArray() {
		$this->assertFalse(BooleanNode::convertToBoolean(array()));

		$this->assertTrue(BooleanNode::convertToBoolean(array('foo')));
		$this->assertTrue(BooleanNode::convertToBoolean(array('foo' => 'bar')));
	}

	/**
	 * @test
	 */
	public function convertToBooleanProperlyConvertsObjects() {
		$this->assertFalse(BooleanNode::convertToBoolean(NULL));

		$this->assertTrue(BooleanNode::convertToBoolean(new \stdClass()));
	}
}
