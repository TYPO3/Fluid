<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Fixtures\TestViewHelper;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

final class ViewHelperNodeTest extends UnitTestCase
{
    /**
     * @test
     */
    public function getArgumentsReturnsArgumentsSetByConstructor(): void
    {
        $renderingContextMock = $this->createMock(RenderingContextInterface::class);
        $viewHelperResolverMock = $this->createMock(ViewHelperResolver::class);
        $renderingContextMock->expects(self::once())->method('getViewHelperResolver')->willReturn($viewHelperResolverMock);
        $viewHelperResolverMock->expects(self::any())->method('resolveViewHelperClassName')->with('f', 'vh')->willReturn(TestViewHelper::class);
        $viewHelperResolverMock->expects(self::any())->method('createViewHelperInstanceFromClassName')->with(TestViewHelper::class)->willReturn(new TestViewHelper());
        $viewHelperResolverMock->expects(self::any())->method('getArgumentDefinitionsForViewHelper')->willReturn([]);
        $arguments = [$this->createMock(NodeInterface::class)];
        $subject = new ViewHelperNode($renderingContextMock, 'f', 'vh', $arguments);
        self::assertSame($arguments, $subject->getArguments());
    }

    /**
     * @test
     */
    public function evaluateCallsViewHelperInvoker(): void
    {
        $renderingContextMock = $this->createMock(RenderingContextInterface::class);
        $viewHelperResolverMock = $this->createMock(ViewHelperResolver::class);
        $renderingContextMock->expects(self::once())->method('getViewHelperResolver')->willReturn($viewHelperResolverMock);
        $viewHelperResolverMock->expects(self::any())->method('resolveViewHelperClassName')->with('f', 'vh')->willReturn(TestViewHelper::class);
        $viewHelperResolverMock->expects(self::any())->method('createViewHelperInstanceFromClassName')->with(TestViewHelper::class)->willReturn(new TestViewHelper());
        $viewHelperResolverMock->expects(self::any())->method('getArgumentDefinitionsForViewHelper')->willReturn([]);
        $viewHelperInvokerMock = $this->createMock(ViewHelperInvoker::class);
        $renderingContextMock->expects(self::once())->method('getViewHelperInvoker')->willReturn($viewHelperInvokerMock);
        $viewHelperInvokerMock->expects(self::once())->method('invoke')->willReturn('test');
        $subject = new ViewHelperNode($renderingContextMock, 'f', 'vh', [$this->createMock(NodeInterface::class)]);
        $result = $subject->evaluate($renderingContextMock);
        self::assertSame('test', $result);
    }
}
