<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser;

use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

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
    public function testSetIdentifierSetsProperty()
    {
        $instance = $this->getMockForAbstractClass(ParsingState::class, [], '', false, false, false, ['dummy']);
        $instance->setIdentifier('test');
        self::assertAttributeEquals('test', 'identifier', $instance);
    }

    /**
     * @test
     */
    public function testGetIdentifierReturnsProperty()
    {
        $instance = $this->getAccessibleMockForAbstractClass(ParsingState::class, [], '', false, false, false);
        $instance->_set('identifier', 'test');
        self::assertEquals('test', $instance->getIdentifier());
    }

    /**
     * @test
     */
    public function setRootNodeCanBeReadOutAgain()
    {
        $rootNode = new RootNode();
        $this->parsingState->setRootNode($rootNode);
        self::assertSame($this->parsingState->getRootNode(), $rootNode, 'Root node could not be read out again.');
    }

    /**
     * @test
     */
    public function pushAndGetFromStackWorks()
    {
        $rootNode = new RootNode();
        $this->parsingState->pushNodeToStack($rootNode);
        self::assertSame($rootNode, $this->parsingState->getNodeFromStack());
        self::assertSame($rootNode, $this->parsingState->popNodeFromStack());
    }

    /**
     * @test
     */
    public function renderCallsTheRightMethodsOnTheRootNode()
    {
        $renderingContext = new RenderingContextFixture();
        $rootNode = $this->getMock(RootNode::class);
        $rootNode->expects(self::once())->method('evaluate')->with($renderingContext)->willReturn('T3DD09 Rock!');
        $this->parsingState->setRootNode($rootNode);
        $renderedValue = $this->parsingState->render($renderingContext);
        self::assertEquals($renderedValue, 'T3DD09 Rock!', 'The rendered value of the Root Node is not returned by the ParsingState.');
    }

    /**
     * @test
     */
    public function testGetLayoutName()
    {
        $this->parsingState->setVariableProvider(new StandardVariableProvider(['layoutName' => 'test']));
        $result = $this->parsingState->getLayoutName(new RenderingContextFixture());
        self::assertEquals('test', $result);
    }

    /**
     * @test
     */
    public function testSetCompilableSetsProperty()
    {
        $this->parsingState->setCompilable(false);
        self::assertAttributeEquals(false, 'compilable', $this->parsingState);
    }
}
