<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NumericNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithToString;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * Testcase for BooleanNode
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
		$this->renderingContext = new RenderingContextFixture();
	}

	/**
	 * @test
	 */
	public function testEvaluateThrowsExceptionOnInvalidComparator() {
		$this->setExpectedException(Exception::class);
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
		$user1 = new UserWithToString('foobar');
		$user2 = new UserWithToString('foobar');
		return array(
			array('===', $user1, $user1, TRUE),
			array('===', $user1, $user2, FALSE),
			array('===', $user1, 'foobar', FALSE),

			array('==', $user1, $user1, TRUE),
			array('==', $user1, $user2, FALSE),
			array('==', $user1, 'foobar', FALSE),
			array('==', 'foobar', $user1, FALSE),
			array('==', 1, 0, FALSE),
			array('==', 1, 1, TRUE),
			array('==', array('foobar'), array('foobar'), TRUE),
			array('==', array('foobar'), array('baz'), FALSE),

			array('>', 1, 0, TRUE),
			array('>', 1, FALSE, TRUE),
			array('>', FALSE, 0, FALSE),

			array('<', 1, 0, FALSE),

			// edge cases as per https://github.com/TYPO3Fluid/Fluid/issues/7
			array('==', 'foo', 0, TRUE),
			array('>=', 1.1, 'foo', TRUE),
			array('>', 'foo', 0, FALSE),

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
		$rootNode->addChildNode(new NumericNode('0'));
		$rootNode->addChildNode(new TextNode('==='));
		$rootNode->addChildNode(new BooleanNode(FALSE));

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
		$context = $this->renderingContext;
		$context->setVariableProvider(new StandardVariableProvider($variables));
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
	public function equalsReturnsTrueIfComparingStringWithZero() {
		$rootNode = new RootNode();
		$rootNode->addChildNode(new TextNode('\'stringA\' == 0'));
		$this->assertTrue(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
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

		$object1Node = $this->getMock(ObjectAccessorNode::class, array('evaluate'), array('foo'));
		$object1Node->expects($this->any())->method('evaluate')->will($this->returnValue($object1));

		$object2Node = $this->getMock(ObjectAccessorNode::class, array('evaluate'), array('foo'));
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

		$object1Node = $this->getMock(ObjectAccessorNode::class, array('evaluate'), array('foo'));
		$object1Node->expects($this->any())->method('evaluate')->will($this->returnValue($object1));

		$object2Node = $this->getMock(ObjectAccessorNode::class, array('evaluate'), array('foo'));
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

	/**
	 * @param mixed $input
	 * @param boolean $expected
	 * @test
	 * @dataProvider getStandardInputTypes
	 */
	public function acceptsStandardTypesAsInput($input, $expected) {
		$node = new BooleanNode($input);
		$this->assertEquals($expected, $node->evaluate($this->renderingContext));
	}

	/**
	 * @return array
	 */
	public function getStandardInputTypes() {
		return array(
			array(0, FALSE),
			array(1, TRUE),
			array(FALSE, FALSE),
			array(TRUE, TRUE),
			array(NULL, FALSE),
			array('', FALSE),
			array('0', FALSE),
			array('1', TRUE),
			array(array(1), TRUE),
			array(array(0), FALSE),
		);
	}
}
