<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for ParsingState
 */
class ParsingStateTest extends UnitTestCase
{

    /**
     * Parsing state
     *
     * @var ParsingState
     */
    protected $parsingState;

    public function setUp(): void
    {
        $this->parsingState = new ParsingState();
    }

    public function tearDown(): void
    {
        unset($this->parsingState);
    }

    /**
     * @test
     */
    public function testSetIdentifierSetsProperty(): void
    {
        $instance = $this->getMockForAbstractClass(ParsingState::class, [], '', false, false, false, ['dummy']);
        $instance->setIdentifier('test');
        $this->assertAttributeEquals('test', 'identifier', $instance);
    }

    /**
     * @test
     */
    public function testGetIdentifierReturnsProperty(): void
    {
        $instance = $this->getAccessibleMockForAbstractClass(ParsingState::class, [], '', false, false, false);
        $instance->_set('identifier', 'test');
        $this->assertEquals('test', $instance->getIdentifier());
    }

    /**
     * @test
     */
    public function setRootNodeCanBeReadOutAgain(): void
    {
        $rootNode = new RootNode();
        $this->parsingState->setRootNode($rootNode);
        $this->assertSame($this->parsingState->getRootNode(), $rootNode, 'Root node could not be read out again.');
    }

    /**
     * @test
     */
    public function pushAndGetFromStackWorks(): void
    {
        $rootNode = new RootNode();
        $this->parsingState->pushNodeToStack($rootNode);
        $this->assertSame($rootNode, $this->parsingState->getNodeFromStack(), 'Node returned from stack was not the right one.');
        $this->assertSame($rootNode, $this->parsingState->popNodeFromStack(), 'Node popped from stack was not the right one.');
    }

    /**
     * @test
     */
    public function renderCallsTheRightMethodsOnTheRootNode(): void
    {
        $renderingContext = new RenderingContextFixture();
        $rootNode = $this->getMock(RootNode::class);
        $rootNode->expects($this->once())->method('evaluate')->with($renderingContext)->will($this->returnValue('T3DD09 Rock!'));
        $this->parsingState->setRootNode($rootNode);
        $renderedValue = $this->parsingState->render($renderingContext);
        $this->assertEquals($renderedValue, 'T3DD09 Rock!', 'The rendered value of the Root Node is not returned by the ParsingState.');
    }

    /**
     * @test
     */
    public function testGetLayoutName(): void
    {
        $this->parsingState->setVariableProvider(new StandardVariableProvider(['layoutName' => 'test']));
        $result = $this->parsingState->getLayoutName(new RenderingContextFixture());
        $this->assertEquals('test', $result);
    }

    /**
     * @test
     */
    public function testSetCompilableSetsProperty(): void
    {
        $this->parsingState->setCompilable(false);
        $this->assertAttributeEquals(false, 'compilable', $this->parsingState);
    }
}
