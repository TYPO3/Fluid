<?php

declare(strict_types=1);

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
     * @test
     */
    public function testSetIdentifierSetsProperty(): void
    {
        $instance = $this->getMockForAbstractClass(ParsingState::class, [], '', false, false, false, []);
        $instance->setIdentifier('test');
        self::assertAttributeEquals('test', 'identifier', $instance);
    }

    /**
     * @test
     */
    public function testGetIdentifierReturnsProperty(): void
    {
        $instance = $this->getAccessibleMockForAbstractClass(ParsingState::class, [], '', false, false, false);
        $instance->_set('identifier', 'test');
        self::assertEquals('test', $instance->getIdentifier());
    }

    /**
     * @test
     */
    public function setRootNodeCanBeReadOutAgain(): void
    {
        $parsingState = new ParsingState();
        $rootNode = new RootNode();
        $parsingState->setRootNode($rootNode);
        self::assertSame($parsingState->getRootNode(), $rootNode, 'Root node could not be read out again.');
    }

    /**
     * @test
     */
    public function pushAndGetFromStackWorks(): void
    {
        $parsingState = new ParsingState();
        $rootNode = new RootNode();
        $parsingState->pushNodeToStack($rootNode);
        self::assertSame($rootNode, $parsingState->getNodeFromStack());
        self::assertSame($rootNode, $parsingState->popNodeFromStack());
    }

    /**
     * @test
     */
    public function renderCallsTheRightMethodsOnTheRootNode(): void
    {
        $parsingState = new ParsingState();
        $renderingContext = new RenderingContextFixture();
        $rootNode = $this->getMock(RootNode::class);
        $rootNode->expects(self::once())->method('evaluate')->with($renderingContext)->willReturn('T3DD09 Rock!');
        $parsingState->setRootNode($rootNode);
        $renderedValue = $parsingState->render($renderingContext);
        self::assertEquals($renderedValue, 'T3DD09 Rock!', 'The rendered value of the Root Node is not returned by the ParsingState.');
    }

    /**
     * @test
     */
    public function testGetLayoutName(): void
    {
        $parsingState = new ParsingState();
        $parsingState->setVariableProvider(new StandardVariableProvider(['layoutName' => 'test']));
        $result = $parsingState->getLayoutName(new RenderingContextFixture());
        self::assertEquals('test', $result);
    }

    /**
     * @test
     */
    public function testSetCompilableSetsProperty(): void
    {
        $parsingState = new ParsingState();
        $parsingState->setCompilable(false);
        self::assertAttributeEquals(false, 'compilable', $parsingState);
    }
}
