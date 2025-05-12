<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;

final class ParsingStateTest extends TestCase
{
    #[Test]
    public function getIdentifierReturnsPreviouslySetIdentifier(): void
    {
        $subject = new ParsingState();
        $subject->setIdentifier('foo');
        self::assertSame('foo', $subject->getIdentifier());
    }

    #[Test]
    public function setRootNodeCanBeReadOutAgain(): void
    {
        $subject = new ParsingState();
        $rootNode = new RootNode();
        $subject->setRootNode($rootNode);
        self::assertSame($rootNode, $subject->getRootNode());
    }

    #[Test]
    public function pushAndGetFromStackWorks(): void
    {
        $subject = new ParsingState();
        $rootNode = new RootNode();
        $subject->pushNodeToStack($rootNode);
        self::assertSame($rootNode, $subject->getNodeFromStack());
        self::assertSame($rootNode, $subject->popNodeFromStack());
    }

    #[Test]
    public function renderCallsTheRightMethodsOnTheRootNode(): void
    {
        $subject = new ParsingState();
        $renderingContext = new RenderingContext();
        $rootNode = $this->createMock(RootNode::class);
        $rootNode->expects(self::once())->method('evaluate')->with($renderingContext)->willReturn('T3DD09 Rock!');
        $subject->setRootNode($rootNode);
        $renderedValue = $subject->render($renderingContext);
        self::assertSame('T3DD09 Rock!', $renderedValue);
    }

    #[Test]
    public function getLayoutNameReturnsLayoutNameFromVariableProvider(): void
    {
        $subject = new ParsingState();
        $subject->setVariableProvider(new StandardVariableProvider([TemplateCompiler::LAYOUT_VARIABLE => 'test']));
        self::assertEquals('test', $subject->getLayoutName(new RenderingContext()));
    }

    #[Test]
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
