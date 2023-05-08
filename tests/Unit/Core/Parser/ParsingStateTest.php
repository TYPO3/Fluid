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
    public function getIdentifierReturnsPreviouslySetIdentifier(): void
    {
        $subject = new ParsingState();
        $subject->setIdentifier('foo');
        self::assertSame('foo', $subject->getIdentifier());
    }

    /**
     * @test
     */
    public function setRootNodeCanBeReadOutAgain(): void
    {
        $subject = new ParsingState();
        $rootNode = new RootNode();
        $subject->setRootNode($rootNode);
        self::assertSame($rootNode, $subject->getRootNode());
    }

    /**
     * @test
     */
    public function pushAndGetFromStackWorks(): void
    {
        $subject = new ParsingState();
        $rootNode = new RootNode();
        $subject->pushNodeToStack($rootNode);
        self::assertSame($rootNode, $subject->getNodeFromStack());
        self::assertSame($rootNode, $subject->popNodeFromStack());
    }

    /**
     * @test
     */
    public function renderCallsTheRightMethodsOnTheRootNode(): void
    {
        $subject = new ParsingState();
        $renderingContext = new RenderingContextFixture();
        $rootNode = $this->createMock(RootNode::class);
        $rootNode->expects(self::once())->method('evaluate')->with($renderingContext)->willReturn('T3DD09 Rock!');
        $subject->setRootNode($rootNode);
        $renderedValue = $subject->render($renderingContext);
        self::assertSame('T3DD09 Rock!', $renderedValue);
    }

    /**
     * @test
     */
    public function getLayoutNameReturnsLayoutNameFromVariableProvider(): void
    {
        $subject = new ParsingState();
        $subject->setVariableProvider(new StandardVariableProvider(['layoutName' => 'test']));
        self::assertEquals('test', $subject->getLayoutName(new RenderingContextFixture()));
    }

    /**
     * @test
     */
    public function isCompilableReturnsPreviouslySetCompilableState(): void
    {
        $subject = new ParsingState();
        self::assertTrue($subject->isCompilable());
        $subject->setCompilable(false);
        self::assertFalse($subject->isCompilable());
        $subject->setCompilable(true);
        self::assertTrue($subject->isCompilable());
    }
}
