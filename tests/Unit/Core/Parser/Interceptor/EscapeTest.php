<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\Interceptor;

use TYPO3Fluid\Fluid\Core\Parser\Interceptor\Escape;
use TYPO3Fluid\Fluid\Core\Parser\InterceptorInterface;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\EscapingNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

class EscapeTest extends UnitTestCase
{
    /**
     * @test
     */
    public function processDoesNotDisableEscapingInterceptorByDefault(): void
    {
        $viewHelperMock = $this->createMock(AbstractViewHelper::class);
        $viewHelperMock->expects(self::once())->method('isChildrenEscapingEnabled')->willReturn(true);
        $viewHelperNodeMock = $this->createMock(ViewHelperNode::class);
        $viewHelperNodeMock->expects(self::once())->method('getUninitializedViewHelper')->willReturn($viewHelperMock);
        $subject = new Escape();
        $property = new \ReflectionProperty($subject, 'viewHelperNodesWhichDisableTheInterceptor');
        self::assertSame(0, $property->getValue($subject));
        $subject->process($viewHelperNodeMock, InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER, new ParsingState());
        self::assertSame(0, $property->getValue($subject));
    }

    /**
     * @test
     */
    public function processDisablesEscapingInterceptorIfViewHelperDisablesIt(): void
    {
        $viewHelperMock = $this->createMock(AbstractViewHelper::class);
        $viewHelperMock->expects(self::once())->method('isChildrenEscapingEnabled')->willReturn(false);
        $viewHelperNodeMock = $this->createMock(ViewHelperNode::class);
        $viewHelperNodeMock->expects(self::once())->method('getUninitializedViewHelper')->willReturn($viewHelperMock);
        $subject = new Escape();
        $property = new \ReflectionProperty($subject, 'viewHelperNodesWhichDisableTheInterceptor');
        self::assertSame(0, $property->getValue($subject));
        $subject->process($viewHelperNodeMock, InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER, new ParsingState());
        self::assertSame(1, $property->getValue($subject));
    }

    /**
     * @test
     */
    public function processReenablesEscapingInterceptorOnClosingViewHelperTagIfItWasDisabledBefore(): void
    {
        $viewHelperMock = $this->createMock(AbstractViewHelper::class);
        $viewHelperMock->expects(self::once())->method('isOutputEscapingEnabled')->willReturn(false);
        $viewHelperNodeMock = $this->createMock(ViewHelperNode::class);
        $viewHelperNodeMock->expects(self::any())->method('getUninitializedViewHelper')->willReturn($viewHelperMock);
        $subject = new Escape();
        $property = new \ReflectionProperty($subject, 'viewHelperNodesWhichDisableTheInterceptor');
        $property->setValue($subject, 1);
        $subject->process($viewHelperNodeMock, InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER, new ParsingState());
        self::assertSame(0, $property->getValue($subject));
    }

    /**
     * @test
     */
    public function processWrapsCurrentViewHelperInEscapeNode(): void
    {
        $mockNode = $this->createMock(ObjectAccessorNode::class);
        $subject = new Escape();
        self::assertInstanceOf(EscapingNode::class, $subject->process($mockNode, InterceptorInterface::INTERCEPT_OBJECTACCESSOR, new ParsingState()));
    }
}
