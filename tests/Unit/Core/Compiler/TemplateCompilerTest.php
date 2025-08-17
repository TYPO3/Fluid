<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Compiler;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3Fluid\Fluid\Core\Cache\SimpleFileCache;
use TYPO3Fluid\Fluid\Core\Compiler\StopCompilingException;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

final class TemplateCompilerTest extends TestCase
{
    #[Test]
    public function getRenderingContextReturnsPreviouslySetRenderingContext(): void
    {
        $subject = new TemplateCompiler();
        $renderingContextMock = $this->createMock(RenderingContextInterface::class);
        $subject->setRenderingContext($renderingContextMock);
        self::assertSame($renderingContextMock, $subject->getRenderingContext());
    }

    #[Test]
    public function hasReturnsFalseWithoutCache(): void
    {
        $renderingContextMock = $this->createMock(RenderingContextInterface::class);
        $renderingContextMock->expects(self::never())->method('getCache');
        $renderingContextMock->expects(self::once())->method('isCacheEnabled')->willReturn(false);
        $subject = new TemplateCompiler();
        $subject->setRenderingContext($renderingContextMock);
        self::assertFalse($subject->has('test'));
    }

    #[Test]
    public function hasReturnsTrueWithCache(): void
    {
        $cacheMock = $this->createMock(SimpleFileCache::class);
        $cacheMock->expects(self::once())->method('get')->with('test')->willReturn(true);
        $renderingContextMock = $this->createMock(RenderingContextInterface::class);
        $renderingContextMock->expects(self::once())->method('getCache')->willReturn($cacheMock);
        $renderingContextMock->expects(self::once())->method('isCacheEnabled')->willReturn(true);
        $subject = new TemplateCompiler();
        $subject->setRenderingContext($renderingContextMock);
        self::assertTrue($subject->has('test'));
    }

    #[Test]
    public function storeReturnsNullIfDisabled(): void
    {
        $renderingContextMock = $this->createMock(RenderingContextInterface::class);
        $renderingContextMock->expects(self::once())->method('isCacheEnabled')->willReturn(false);
        $subject = new TemplateCompiler();
        $subject->setRenderingContext($renderingContextMock);
        self::assertNull($subject->store('foobar', new ParsingState()));
    }

    #[Test]
    public function testStoreSavesUncompilableState(): void
    {
        $cacheMock = $this->createMock(SimpleFileCache::class);
        $cacheMock->expects(self::once())->method('set')->with('fakeidentifier', self::anything());
        $renderingContextMock = $this->createMock(RenderingContextInterface::class);
        $renderingContextMock->expects(self::once())->method('getCache')->willReturn($cacheMock);
        $renderingContextMock->expects(self::once())->method('isCacheEnabled')->willReturn(true);
        $parsingStateMock = $this->createMock(ParsingState::class);
        $parsingStateMock->expects(self::once())->method('isCompilable')->willReturn(false);
        $subject = new TemplateCompiler();
        $subject->setRenderingContext($renderingContextMock);
        $subject->store('fakeidentifier', $parsingStateMock);
    }

    #[Test]
    public function disableThrowsException(): void
    {
        $this->expectException(StopCompilingException::class);
        (new TemplateCompiler())->disable();
    }

    #[Test]
    public function getRenderingContextGetsPreviouslySetRenderingContext(): void
    {
        $renderingContextMock = $this->createMock(RenderingContextInterface::class);
        $subject = new TemplateCompiler();
        $subject->setRenderingContext($renderingContextMock);
        self::assertSame($renderingContextMock, $subject->getRenderingContext());
    }

    #[Test]
    public function variableNameReturnsIncrementedName(): void
    {
        $subject = new TemplateCompiler();
        self::assertSame('$test0', $subject->variableName('test'));
        self::assertSame('$test1', $subject->variableName('test'));
    }
}
