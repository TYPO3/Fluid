<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NumericNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithToString;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

class BooleanNodeTest extends UnitTestCase
{

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
    public function setUp(): void
    {
        $this->renderingContext = new RenderingContextFixture();
    }

    /**
     * @test
     */
    public function convertToBooleanProperlyConvertsValuesOfTypeBoolean()
    {
        self::assertFalse(BooleanNode::convertToBoolean(false, $this->renderingContext));
        self::assertTrue(BooleanNode::convertToBoolean(true, $this->renderingContext));
    }

    /**
     * @test
     */
    public function convertToBooleanProperlyConvertsValuesOfTypeString()
    {
        self::assertFalse(BooleanNode::convertToBoolean('', $this->renderingContext));
        self::assertFalse(BooleanNode::convertToBoolean('false', $this->renderingContext));
        self::assertFalse(BooleanNode::convertToBoolean('FALSE', $this->renderingContext));

        self::assertTrue(BooleanNode::convertToBoolean('true', $this->renderingContext));
        self::assertTrue(BooleanNode::convertToBoolean('TRUE', $this->renderingContext));
    }

    /**
     * @param float $number
     * @param bool $expected
     * @test
     * @dataProvider getNumericBooleanTestValues
     */
    public function convertToBooleanProperlyConvertsNumericValues($number, $expected)
    {
        self::assertEquals($expected, BooleanNode::convertToBoolean($number, $this->renderingContext));
    }

    /**
     * @return array
     */
    public function getNumericBooleanTestValues()
    {
        return [
            [0, false],
            [-1, true],
            ['-1', true],
            [-.5, true],
            [1, true],
            [.5, true],
        ];
    }

    /**
     * @test
     */
    public function convertToBooleanProperlyConvertsValuesOfTypeArray()
    {
        self::assertFalse(BooleanNode::convertToBoolean([], $this->renderingContext));

        self::assertTrue(BooleanNode::convertToBoolean(['foo'], $this->renderingContext));
        self::assertTrue(BooleanNode::convertToBoolean(['foo' => 'bar'], $this->renderingContext));
    }

    /**
     * @test
     */
    public function convertToBooleanProperlyConvertsObjects()
    {
        self::assertFalse(BooleanNode::convertToBoolean(null, $this->renderingContext));

        self::assertTrue(BooleanNode::convertToBoolean(new \stdClass(), $this->renderingContext));
    }

    /**
     * @return array
     */
    public function getEvaluateComparatorTestValues()
    {
        $user1 = new UserWithToString('foobar');
        $user2 = new UserWithToString('foobar');
        return [
            ['===', $user1, $user1, true],
            ['===', $user1, $user2, false],
            ['===', $user1, 'foobar', false],

            ['==', $user1, $user1, true],
            ['==', $user1, $user2, false],
            ['==', $user1, 'foobar', false],
            ['==', 'foobar', $user1, false],
            ['==', 1, 0, false],
            ['==', 1, 1, true],
            ['==', ['foobar'], ['foobar'], true],
            ['==', ['foobar'], ['baz'], false],

            ['>', 1, 0, true],
            ['>', 1, false, true],
            ['>', false, 0, false],

            ['<', 1, 0, false],

            // edge cases as per https://github.com/TYPO3Fluid/Fluid/issues/7
            ['==', 'foo', 0, true],
            ['>=', 1.1, 'foo', true],
            ['>', 'foo', 0, false],

        ];
    }

    /**
     * @dataProvider getCreateFromNodeAndEvaluateTestValues
     * @param NodeInterface $node
     * @param bool $expected
     */
    public function testCreateFromNodeAndEvaluate(NodeInterface $node, $expected)
    {
        $result = BooleanNode::createFromNodeAndEvaluate($node, $this->renderingContext);
        self::assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getCreateFromNodeAndEvaluateTestValues()
    {
        return [
            '1 && 1' => [new TextNode('1 && 1'), true],
            '1 && 0' => [new TextNode('1 && 0'), false],
            '(1 && 1) && 1' => [new TextNode('(1 && 1) && 1'), true],
            '(1 && 0) || 1' => [new TextNode('(1 && 0) || 1'), true],
            '(\'text\' == \'text\') || 1 >= 0' => [new TextNode('(\'text\' == \'text\') || 1 >= 0'), true],
            '1 <= 0' => [new TextNode('1 <= 0'), false],
            '1 > 4' => [new TextNode('1 > 4'), false],
            '1 < 4' => [new TextNode('1 < 4'), true],
            '4 % 4' => [new TextNode('4 % 4'), false],
            '2 % 4' => [new TextNode('2 % 4'), true],
            '0 && 1' => [new TextNode('0 && 1'), false],
        ];
    }

    /**
     * @test
     */
    public function comparingNestedComparisonsWorks()
    {
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('('));
        $rootNode->addChildNode(new ArrayNode(['foo' => 'bar']));
        $rootNode->addChildNode(new TextNode('=='));
        $rootNode->addChildNode(new ArrayNode(['foo' => 'bar']));
        $rootNode->addChildNode(new TextNode(')'));
        $rootNode->addChildNode(new TextNode('&&'));
        $rootNode->addChildNode(new TextNode('1'));
        self::assertTrue(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
    }

    /**
     * @test
     */
    public function comparingEqualNumbersReturnsTrue()
    {
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('5'));
        $rootNode->addChildNode(new TextNode('=='));
        $rootNode->addChildNode(new TextNode('5'));

        $booleanNode = new BooleanNode($rootNode);
        self::assertTrue($booleanNode->evaluate($this->renderingContext));
    }

    /**
     * @test
     */
    public function comparingUnequalNumbersReturnsFalse()
    {
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('5'));
        $rootNode->addChildNode(new TextNode('=='));
        $rootNode->addChildNode(new TextNode('3'));

        $booleanNode = new BooleanNode($rootNode);
        self::assertFalse($booleanNode->evaluate($this->renderingContext));
    }

    /**
     * @test
     */
    public function comparingEqualIdentityReturnsTrue()
    {
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('5'));
        $rootNode->addChildNode(new TextNode('==='));
        $rootNode->addChildNode(new TextNode('5'));

        $booleanNode = new BooleanNode($rootNode);
        self::assertTrue($booleanNode->evaluate($this->renderingContext));
    }

    /**
     * @test
     */
    public function comparingUnequalIdentityReturnsFalse()
    {
        $rootNode = new RootNode();
        $rootNode->addChildNode(new NumericNode('0'));
        $rootNode->addChildNode(new TextNode('==='));
        $rootNode->addChildNode(new BooleanNode(false));

        $booleanNode = new BooleanNode($rootNode);
        self::assertFalse($booleanNode->evaluate($this->renderingContext));
    }

    /**
     * @test
     */
    public function notEqualReturnsFalseIfNumbersAreEqual()
    {
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('5'));
        $rootNode->addChildNode(new TextNode('!='));
        $rootNode->addChildNode(new TextNode('5'));

        $booleanNode = new BooleanNode($rootNode);
        self::assertFalse($booleanNode->evaluate($this->renderingContext));
    }

    /**
     * @test
     */
    public function notEqualReturnsTrueIfNumbersAreNotEqual()
    {
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('5'));
        $rootNode->addChildNode(new TextNode('!='));
        $rootNode->addChildNode(new TextNode('3'));

        $booleanNode = new BooleanNode($rootNode);
        self::assertTrue($booleanNode->evaluate($this->renderingContext));
    }

    /**
     * @test
     */
    public function oddNumberModulo2ReturnsTrue()
    {
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('43'));
        $rootNode->addChildNode(new TextNode('%'));
        $rootNode->addChildNode(new TextNode('2'));

        $booleanNode = new BooleanNode($rootNode);
        self::assertTrue($booleanNode->evaluate($this->renderingContext));
    }

    /**
     * @test
     */
    public function evenNumberModulo2ReturnsFalse()
    {
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('42'));
        $rootNode->addChildNode(new TextNode('%'));
        $rootNode->addChildNode(new TextNode('2'));

        $booleanNode = new BooleanNode($rootNode);
        self::assertFalse($booleanNode->evaluate($this->renderingContext));
    }

    /**
     * @test
     */
    public function greaterThanReturnsTrueIfNumberIsReallyGreater()
    {
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('10'));
        $rootNode->addChildNode(new TextNode('>'));
        $rootNode->addChildNode(new TextNode('9'));

        $booleanNode = new BooleanNode($rootNode);
        self::assertTrue($booleanNode->evaluate($this->renderingContext));
    }

    /**
     * @test
     */
    public function greaterThanReturnsFalseIfNumberIsEqual()
    {
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('10'));
        $rootNode->addChildNode(new TextNode('>'));
        $rootNode->addChildNode(new TextNode('10'));

        $booleanNode = new BooleanNode($rootNode);
        self::assertFalse($booleanNode->evaluate($this->renderingContext));
    }

    /**
     * @test
     */
    public function greaterOrEqualsReturnsTrueIfNumberIsReallyGreater()
    {
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('10'));
        $rootNode->addChildNode(new TextNode('>='));
        $rootNode->addChildNode(new TextNode('9'));

        $booleanNode = new BooleanNode($rootNode);
        self::assertTrue($booleanNode->evaluate($this->renderingContext));
    }

    /**
     * @test
     */
    public function greaterOrEqualsReturnsTrueIfNumberIsEqual()
    {
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('10'));
        $rootNode->addChildNode(new TextNode('>='));
        $rootNode->addChildNode(new TextNode('10'));
        self::assertTrue(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
    }

    /**
     * @test
     */
    public function greaterOrEqualsReturnFalseIfNumberIsSmaller()
    {
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('10'));
        $rootNode->addChildNode(new TextNode('>='));
        $rootNode->addChildNode(new TextNode('11'));
        self::assertFalse(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
    }

    /**
     * @test
     */
    public function lessThanReturnsTrueIfNumberIsReallyless()
    {
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('9'));
        $rootNode->addChildNode(new TextNode('<'));
        $rootNode->addChildNode(new TextNode('10'));
        self::assertTrue(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
    }

    /**
     * @test
     */
    public function lessThanReturnsFalseIfNumberIsEqual()
    {
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('10'));
        $rootNode->addChildNode(new TextNode('<'));
        $rootNode->addChildNode(new TextNode('10'));
        self::assertFalse(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
    }

    /**
     * @test
     */
    public function lessOrEqualsReturnsTrueIfNumberIsReallyLess()
    {
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('9'));
        $rootNode->addChildNode(new TextNode('<='));
        $rootNode->addChildNode(new TextNode('10'));
        self::assertTrue(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
    }

    /**
     * @test
     */
    public function lessOrEqualsReturnsTrueIfNumberIsEqual()
    {
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('10'));
        $rootNode->addChildNode(new TextNode('<='));
        $rootNode->addChildNode(new TextNode('10'));
        self::assertTrue(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
    }

    /**
     * @test
     */
    public function lessOrEqualsReturnFalseIfNumberIsBigger()
    {
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('11'));
        $rootNode->addChildNode(new TextNode('<='));
        $rootNode->addChildNode(new TextNode('10'));
        self::assertFalse(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
    }

    /**
     * @test
     */
    public function lessOrEqualsReturnFalseIfComparingWithANegativeNumber()
    {
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('11 <= -2.1'));
        self::assertFalse(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
    }

    protected function getDummyRenderingContextWithVariables(array $variables): RenderingContextInterface
    {
        $context = $this->renderingContext;
        $context->setVariableProvider(new StandardVariableProvider($variables));
        $context->getVariableProvider()->setSource($variables);
        return $context;
    }

    /**
     * @test
     */
    public function comparingVariableWithMatchedQuotedString()
    {
        $renderingContext = $this->getDummyRenderingContextWithVariables(['test' => 'somevalue']);
        $rootNode = new RootNode();
        $rootNode->addChildNode(new ObjectAccessorNode('test'));
        $rootNode->addChildNode(new TextNode(' == '));
        $rootNode->addChildNode(new TextNode('\'somevalue\''));
        self::assertTrue(BooleanNode::createFromNodeAndEvaluate($rootNode, $renderingContext));
    }

    /**
     * @test
     */
    public function comparingVariableWithUnmatchedQuotedString()
    {
        $renderingContext = $this->getDummyRenderingContextWithVariables(['test' => 'somevalue']);
        $rootNode = new RootNode();
        $rootNode->addChildNode(new ObjectAccessorNode('test'));
        $rootNode->addChildNode(new TextNode(' != '));
        $rootNode->addChildNode(new TextNode('\'othervalue\''));
        self::assertTrue(BooleanNode::createFromNodeAndEvaluate($rootNode, $renderingContext));
    }

    /**
     * @test
     */
    public function comparingNotEqualsVariableWithMatchedQuotedString()
    {
        $renderingContext = $this->getDummyRenderingContextWithVariables(['test' => 'somevalue']);
        $rootNode = new RootNode();
        $rootNode->addChildNode(new ObjectAccessorNode('test'));
        $rootNode->addChildNode(new TextNode(' != '));
        $rootNode->addChildNode(new TextNode('\'somevalue\''));
        self::assertFalse(BooleanNode::createFromNodeAndEvaluate($rootNode, $renderingContext));
    }

    /**
     * @test
     */
    public function comparingNotEqualsVariableWithUnmatchedQuotedString()
    {
        $renderingContext = $this->getDummyRenderingContextWithVariables(['test' => 'somevalue']);
        $rootNode = new RootNode();
        $rootNode->addChildNode(new ObjectAccessorNode('test'));
        $rootNode->addChildNode(new TextNode(' != '));
        $rootNode->addChildNode(new TextNode('\'somevalue\''));
        self::assertFalse(BooleanNode::createFromNodeAndEvaluate($rootNode, $renderingContext));
    }

    /**
     * @test
     */
    public function comparingEqualsVariableWithMatchedQuotedStringInSingleTextNode()
    {
        $renderingContext = $this->getDummyRenderingContextWithVariables(['test' => 'somevalue']);
        $rootNode = new RootNode();
        $rootNode->addChildNode(new ObjectAccessorNode('test'));
        $rootNode->addChildNode(new TextNode(' != \'somevalue\''));
        self::assertFalse(BooleanNode::createFromNodeAndEvaluate($rootNode, $renderingContext));
    }

    /**
     * @test
     */
    public function notEqualReturnsFalseIfComparingMatchingStrings()
    {
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('\'stringA\' != "stringA"'));
        self::assertFalse(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
    }

    /**
     * @test
     */
    public function notEqualReturnsTrueIfComparingNonMatchingStrings()
    {
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('\'stringA\' != \'stringB\''));
        self::assertTrue(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
    }

    /**
     * @test
     */
    public function equalsReturnsFalseIfComparingNonMatchingStrings()
    {
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('\'stringA\' == \'stringB\''));
        self::assertFalse(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
    }

    /**
     * @test
     */
    public function equalsReturnsTrueIfComparingMatchingStrings()
    {
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('\'stringA\' == "stringA"'));
        self::assertTrue(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
    }

    /**
     * @test
     */
    public function equalsReturnsTrueIfComparingMatchingStringsWithEscapedQuotes()
    {
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('\'\\\'stringA\\\'\' == \'\\\'stringA\\\'\''));
        self::assertTrue(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
    }

    /**
     * @test
     */
    public function equalsReturnsFalseIfComparingStringWithNonZero()
    {
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('\'stringA\' == 42'));
        self::assertFalse(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
    }

    /**
     * @test
     */
    public function equalsReturnsTrueIfComparingStringWithZero()
    {
        // expected value based on php versions behaviour
        $expected = (PHP_VERSION_ID < 80000 ? true : false);

        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('\'stringA\' == 0'));
        self::assertSame($expected, BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
    }

    /**
     * @test
     */
    public function equalsReturnsFalseIfComparingStringZeroWithZero()
    {
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('\'0\' == 0'));
        self::assertTrue(BooleanNode::createFromNodeAndEvaluate($rootNode, $this->renderingContext));
    }

    /**
     * @test
     */
    public function objectsAreComparedStrictly()
    {
        $object1 = new \stdClass();
        $object2 = new \stdClass();

        $rootNode = new RootNode();

        $object1Node = $this->getMock(ObjectAccessorNode::class, ['evaluate'], ['foo']);
        $object1Node->expects(self::any())->method('evaluate')->willReturn($object1);

        $object2Node = $this->getMock(ObjectAccessorNode::class, ['evaluate'], ['foo']);
        $object2Node->expects(self::any())->method('evaluate')->willReturn($object2);

        $rootNode->addChildNode($object1Node);
        $rootNode->addChildNode(new TextNode('=='));
        $rootNode->addChildNode($object2Node);

        $booleanNode = new BooleanNode($rootNode);
        self::assertFalse($booleanNode->evaluate($this->renderingContext));
    }

    /**
     * @test
     */
    public function objectsAreComparedStrictlyInUnequalComparison()
    {
        $object1 = new \stdClass();
        $object2 = new \stdClass();

        $rootNode = new RootNode();

        $object1Node = $this->getMock(ObjectAccessorNode::class, ['evaluate'], ['foo']);
        $object1Node->expects(self::any())->method('evaluate')->willReturn($object1);

        $object2Node = $this->getMock(ObjectAccessorNode::class, ['evaluate'], ['foo']);
        $object2Node->expects(self::any())->method('evaluate')->willReturn($object2);

        $rootNode->addChildNode($object1Node);
        $rootNode->addChildNode(new TextNode('!='));
        $rootNode->addChildNode($object2Node);

        $booleanNode = new BooleanNode($rootNode);
        self::assertTrue($booleanNode->evaluate($this->renderingContext));
    }

    /**
     * @param mixed $input
     * @param bool $expected
     * @test
     * @dataProvider getStandardInputTypes
     */
    public function acceptsStandardTypesAsInput($input, $expected)
    {
        $node = new BooleanNode($input);
        self::assertEquals($expected, $node->evaluate($this->renderingContext));
    }

    /**
     * @return array
     */
    public function getStandardInputTypes()
    {
        return [
            [0, false],
            [1, true],
            [false, false],
            [true, true],
            [null, false],
            ['', false],
            ['0', false],
            ['1', true],
            [[1], true],
            [[0], false],
        ];
    }
}
