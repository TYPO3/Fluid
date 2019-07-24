<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithToString;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for BooleanNode
 */
class BooleanNodeTest extends UnitTestCase
{
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
    public function convertToBooleanProperlyConvertsValuesOfTypeBoolean(): void
    {
        $this->assertFalse(BooleanNode::convertToBoolean(false, $this->renderingContext));
        $this->assertTrue(BooleanNode::convertToBoolean(true, $this->renderingContext));
    }

    /**
     * @test
     */
    public function convertToBooleanProperlyConvertsValuesOfTypeString(): void
    {
        $this->assertFalse(BooleanNode::convertToBoolean('', $this->renderingContext));
        $this->assertFalse(BooleanNode::convertToBoolean('false', $this->renderingContext));
        $this->assertFalse(BooleanNode::convertToBoolean('FALSE', $this->renderingContext));

        $this->assertTrue(BooleanNode::convertToBoolean('true', $this->renderingContext));
        $this->assertTrue(BooleanNode::convertToBoolean('TRUE', $this->renderingContext));
    }

    /**
     * @param mixed $number
     * @param boolean $expected
     * @test
     * @dataProvider getNumericBooleanTestValues
     */
    public function convertToBooleanProperlyConvertsNumericValues($number, bool $expected): void
    {
        $this->assertEquals($expected, BooleanNode::convertToBoolean($number, $this->renderingContext));
    }

    /**
     * @return array
     */
    public function getNumericBooleanTestValues(): array
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
    public function convertToBooleanProperlyConvertsValuesOfTypeArray(): void
    {
        $this->assertFalse(BooleanNode::convertToBoolean([], $this->renderingContext));

        $this->assertTrue(BooleanNode::convertToBoolean(['foo'], $this->renderingContext));
        $this->assertTrue(BooleanNode::convertToBoolean(['foo' => 'bar'], $this->renderingContext));
    }

    /**
     * @test
     */
    public function convertToBooleanProperlyConvertsObjects(): void
    {
        $this->assertFalse(BooleanNode::convertToBoolean(null, $this->renderingContext));

        $this->assertTrue(BooleanNode::convertToBoolean(new \stdClass(), $this->renderingContext));
    }

    /**
     * @return array
     */
    public function getEvaluateComparatorTestValues(): array
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
     * @param ComponentInterface $node
     * @param boolean $expected
     */
    public function testCreateFromNodeAndEvaluate(ComponentInterface $node, bool $expected): void
    {
        $result = (new BooleanNode($node))->execute($this->renderingContext);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getCreateFromNodeAndEvaluateTestValues(): array
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
    public function comparingNestedComparisonsWorks(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('('));
        $rootNode->addChild(new ArrayNode(['foo' => 'bar']));
        $rootNode->addChild(new TextNode('=='));
        $rootNode->addChild(new ArrayNode(['foo' => 'bar']));
        $rootNode->addChild(new TextNode(')'));
        $rootNode->addChild(new TextNode('&&'));
        $rootNode->addChild(new TextNode('1'));
        $this->assertTrue((new BooleanNode($rootNode))->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function comparingEqualNumbersReturnsTrue(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('5'));
        $rootNode->addChild(new TextNode('=='));
        $rootNode->addChild(new TextNode('5'));

        $booleanNode = new BooleanNode($rootNode);
        $this->assertTrue($booleanNode->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function comparingUnequalNumbersReturnsFalse(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('5'));
        $rootNode->addChild(new TextNode('=='));
        $rootNode->addChild(new TextNode('3'));

        $booleanNode = new BooleanNode($rootNode);
        $this->assertFalse($booleanNode->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function comparingEqualIdentityReturnsTrue(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('5'));
        $rootNode->addChild(new TextNode('==='));
        $rootNode->addChild(new TextNode('5'));

        $booleanNode = new BooleanNode($rootNode);
        $this->assertTrue($booleanNode->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function comparingUnequalIdentityReturnsFalse(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('0'));
        $rootNode->addChild(new TextNode('==='));
        $rootNode->addChild(new BooleanNode(false));

        $booleanNode = new BooleanNode($rootNode);
        $this->assertFalse($booleanNode->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function notEqualReturnsFalseIfNumbersAreEqual(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('5'));
        $rootNode->addChild(new TextNode('!='));
        $rootNode->addChild(new TextNode('5'));

        $booleanNode = new BooleanNode($rootNode);
        $this->assertFalse($booleanNode->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function notEqualReturnsTrueIfNumbersAreNotEqual(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('5'));
        $rootNode->addChild(new TextNode('!='));
        $rootNode->addChild(new TextNode('3'));

        $booleanNode = new BooleanNode($rootNode);
        $this->assertTrue($booleanNode->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function oddNumberModulo2ReturnsTrue(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('43'));
        $rootNode->addChild(new TextNode('%'));
        $rootNode->addChild(new TextNode('2'));

        $booleanNode = new BooleanNode($rootNode);
        $this->assertTrue($booleanNode->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function evenNumberModulo2ReturnsFalse(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('42'));
        $rootNode->addChild(new TextNode('%'));
        $rootNode->addChild(new TextNode('2'));

        $booleanNode = new BooleanNode($rootNode);
        $this->assertFalse($booleanNode->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function greaterThanReturnsTrueIfNumberIsReallyGreater(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('10'));
        $rootNode->addChild(new TextNode('>'));
        $rootNode->addChild(new TextNode('9'));

        $booleanNode = new BooleanNode($rootNode);
        $this->assertTrue($booleanNode->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function greaterThanReturnsFalseIfNumberIsEqual(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('10'));
        $rootNode->addChild(new TextNode('>'));
        $rootNode->addChild(new TextNode('10'));

        $booleanNode = new BooleanNode($rootNode);
        $this->assertFalse($booleanNode->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function greaterOrEqualsReturnsTrueIfNumberIsReallyGreater(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('10'));
        $rootNode->addChild(new TextNode('>='));
        $rootNode->addChild(new TextNode('9'));

        $booleanNode = new BooleanNode($rootNode);
        $this->assertTrue($booleanNode->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function greaterOrEqualsReturnsTrueIfNumberIsEqual(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('10'));
        $rootNode->addChild(new TextNode('>='));
        $rootNode->addChild(new TextNode('10'));
        $this->assertTrue((new BooleanNode($rootNode))->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function greaterOrEqualsReturnFalseIfNumberIsSmaller(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('10'));
        $rootNode->addChild(new TextNode('>='));
        $rootNode->addChild(new TextNode('11'));
        $this->assertFalse((new BooleanNode($rootNode))->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function lessThanReturnsTrueIfNumberIsReallyless(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('9'));
        $rootNode->addChild(new TextNode('<'));
        $rootNode->addChild(new TextNode('10'));
        $this->assertTrue((new BooleanNode($rootNode))->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function lessThanReturnsFalseIfNumberIsEqual(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('10'));
        $rootNode->addChild(new TextNode('<'));
        $rootNode->addChild(new TextNode('10'));
        $this->assertFalse((new BooleanNode($rootNode))->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function lessOrEqualsReturnsTrueIfNumberIsReallyLess(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('9'));
        $rootNode->addChild(new TextNode('<='));
        $rootNode->addChild(new TextNode('10'));
        $this->assertTrue((new BooleanNode($rootNode))->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function lessOrEqualsReturnsTrueIfNumberIsEqual(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('10'));
        $rootNode->addChild(new TextNode('<='));
        $rootNode->addChild(new TextNode('10'));
        $this->assertTrue((new BooleanNode($rootNode))->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function lessOrEqualsReturnFalseIfNumberIsBigger(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('11'));
        $rootNode->addChild(new TextNode('<='));
        $rootNode->addChild(new TextNode('10'));
        $this->assertFalse((new BooleanNode($rootNode))->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function lessOrEqualsReturnFalseIfComparingWithANegativeNumber(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('11 <= -2.1'));
        $this->assertFalse((new BooleanNode($rootNode))->execute($this->renderingContext));
    }

    /**
     * @param array $variables
     * @return RenderingContext
     */
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
    public function comparingVariableWithMatchedQuotedString(): void
    {
        $renderingContext = $this->getDummyRenderingContextWithVariables(['test' => 'somevalue']);
        $rootNode = new RootNode();
        $rootNode->addChild(new ObjectAccessorNode('test'));
        $rootNode->addChild(new TextNode(' == \'somevalue\''));
        $this->assertTrue((new BooleanNode($rootNode))->execute($renderingContext));
    }

    /**
     * @test
     */
    public function comparingVariableWithUnmatchedQuotedString(): void
    {
        $renderingContext = $this->getDummyRenderingContextWithVariables(['test' => 'somevalue']);
        $rootNode = new RootNode();
        $rootNode->addChild(new ObjectAccessorNode('test'));
        $rootNode->addChild(new TextNode(' != '));
        $rootNode->addChild(new TextNode('\'othervalue\''));
        $this->assertTrue((new BooleanNode($rootNode))->execute($renderingContext));
    }

    /**
     * @test
     */
    public function comparingNotEqualsVariableWithMatchedQuotedString(): void
    {
        $renderingContext = $this->getDummyRenderingContextWithVariables(['test' => 'somevalue']);
        $rootNode = new RootNode();
        $rootNode->addChild(new ObjectAccessorNode('test'));
        $rootNode->addChild(new TextNode(' != '));
        $rootNode->addChild(new TextNode('\'somevalue\''));
        $this->assertFalse((new BooleanNode($rootNode))->execute($renderingContext));
    }

    /**
     * @test
     */
    public function comparingNotEqualsVariableWithUnmatchedQuotedString(): void
    {
        $renderingContext = $this->getDummyRenderingContextWithVariables(['test' => 'somevalue']);
        $rootNode = new RootNode();
        $rootNode->addChild(new ObjectAccessorNode('test'));
        $rootNode->addChild(new TextNode(' != '));
        $rootNode->addChild(new TextNode('\'somevalue\''));
        $this->assertFalse((new BooleanNode($rootNode))->execute($renderingContext));
    }

    /**
     * @test
     */
    public function comparingEqualsVariableWithMatchedQuotedStringInSingleTextNode(): void
    {
        $renderingContext = $this->getDummyRenderingContextWithVariables(['test' => 'somevalue']);
        $rootNode = new RootNode();
        $rootNode->addChild(new ObjectAccessorNode('test'));
        $rootNode->addChild(new TextNode(' != \'somevalue\''));
        $this->assertFalse((new BooleanNode($rootNode))->execute($renderingContext));
    }

    /**
     * @test
     */
    public function notEqualReturnsFalseIfComparingMatchingStrings(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('\'stringA\' != "stringA"'));
        $this->assertFalse((new BooleanNode($rootNode))->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function notEqualReturnsTrueIfComparingNonMatchingStrings(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('\'stringA\' != \'stringB\''));
        $this->assertTrue((new BooleanNode($rootNode))->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function equalsReturnsFalseIfComparingNonMatchingStrings(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('\'stringA\' == \'stringB\''));
        $this->assertFalse((new BooleanNode($rootNode))->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function equalsReturnsTrueIfComparingMatchingStrings(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('\'stringA\' == "stringA"'));
        $this->assertTrue((new BooleanNode($rootNode))->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function equalsReturnsTrueIfComparingMatchingStringsWithEscapedQuotes(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('\'\\\'stringA\\\'\' == \'\\\'stringA\\\'\''));
        $this->assertTrue((new BooleanNode($rootNode))->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function equalsReturnsFalseIfComparingStringWithNonZero(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('\'stringA\' == 42'));
        $this->assertFalse((new BooleanNode($rootNode))->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function equalsReturnsTrueIfComparingStringWithZero(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('\'stringA\' == 0'));
        $this->assertTrue((new BooleanNode($rootNode))->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function equalsReturnsFalseIfComparingStringZeroWithZero(): void
    {
        $rootNode = new RootNode();
        $rootNode->addChild(new TextNode('\'0\' == 0'));
        $this->assertTrue((new BooleanNode($rootNode))->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function objectsAreComparedStrictly(): void
    {
        $object1 = new \stdClass();
        $object2 = new \stdClass();

        $rootNode = new RootNode();

        $object1Node = $this->getMock(ObjectAccessorNode::class, ['execute'], ['foo']);
        $object1Node->expects($this->any())->method('execute')->will($this->returnValue($object1));

        $object2Node = $this->getMock(ObjectAccessorNode::class, ['execute'], ['foo']);
        $object2Node->expects($this->any())->method('execute')->will($this->returnValue($object2));

        $rootNode->addChild($object1Node);
        $rootNode->addChild(new TextNode('=='));
        $rootNode->addChild($object2Node);

        $booleanNode = new BooleanNode($rootNode);
        $this->assertFalse($booleanNode->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function objectsAreComparedStrictlyInUnequalComparison(): void
    {
        $object1 = new \stdClass();
        $object2 = new \stdClass();

        $rootNode = new RootNode();

        $object1Node = $this->getMock(ObjectAccessorNode::class, ['execute'], ['foo']);
        $object1Node->expects($this->any())->method('execute')->will($this->returnValue($object1));

        $object2Node = $this->getMock(ObjectAccessorNode::class, ['execute'], ['foo']);
        $object2Node->expects($this->any())->method('execute')->will($this->returnValue($object2));

        $rootNode->addChild($object1Node);
        $rootNode->addChild(new TextNode('!='));
        $rootNode->addChild($object2Node);

        $booleanNode = new BooleanNode($rootNode);
        $this->assertTrue($booleanNode->execute($this->renderingContext));
    }

    /**
     * @param mixed $input
     * @param boolean $expected
     * @test
     * @dataProvider getStandardInputTypes
     */
    public function acceptsStandardTypesAsInput($input, bool $expected): void
    {
        $node = new BooleanNode($input);
        $this->assertEquals($expected, $node->execute($this->renderingContext));
    }

    /**
     * @return array
     */
    public function getStandardInputTypes(): array
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
