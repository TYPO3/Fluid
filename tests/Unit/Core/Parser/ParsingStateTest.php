<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
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

    public static function getLayoutNameDataProvider(): iterable
    {
        return [
            ['MyLayout', 'MyLayout'],
            [new TextNode('MyLayout'), 'MyLayout'],
        ];
    }

    #[Test]
    #[DataProvider('getLayoutNameDataProvider')]
    public function getLayoutNameReturnsLayoutFromProperty(string|NodeInterface $layoutName, string $expected): void
    {
        $subject = new ParsingState();
        $subject->setLayoutName($layoutName);
        self::assertTrue($subject->hasLayout());
        self::assertEquals($expected, $subject->getLayoutName(new RenderingContext()));
    }

    #[Test]
    public function hasLayoutReturnsFalseIfNoLayoutExists(): void
    {
        $subject = new ParsingState();
        $subject->setVariableProvider(new StandardVariableProvider());
        self::assertFalse($subject->hasLayout());
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
