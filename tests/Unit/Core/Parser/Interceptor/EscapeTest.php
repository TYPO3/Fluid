<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\Interceptor;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3Fluid\Fluid\Core\Parser\Interceptor\Escape;
use TYPO3Fluid\Fluid\Core\Parser\InterceptorInterface;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\EscapingNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class EscapeTest extends TestCase
{
    #[Test]
    public function processDoesNotDisableEscapingInterceptorByDefault(): void
    {
        $viewHelperMock = $this->createMock(AbstractViewHelper::class);
        $viewHelperMock->expects(self::once())->method('isChildrenEscapingEnabled')->willReturn(true);
        $viewHelperNodeMock = $this->createMock(ViewHelperNode::class);
        $viewHelperNodeMock->expects(self::once())->method('getUninitializedViewHelper')->willReturn($viewHelperMock);
        $subject = new Escape();
        $property = new \ReflectionProperty($subject, 'childrenEscapingEnabled');
        self::assertTrue($property->getValue($subject));
        $subject->process($viewHelperNodeMock, InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER, new ParsingState());
        self::assertTrue($property->getValue($subject));
    }

    #[Test]
    public function processDisablesEscapingInterceptorIfViewHelperDisablesIt(): void
    {
        $viewHelperMock = $this->createMock(AbstractViewHelper::class);
        $viewHelperMock->expects(self::once())->method('isChildrenEscapingEnabled')->willReturn(false);
        $viewHelperNodeMock = $this->createMock(ViewHelperNode::class);
        $viewHelperNodeMock->expects(self::once())->method('getUninitializedViewHelper')->willReturn($viewHelperMock);
        $subject = new Escape();
        $property = new \ReflectionProperty($subject, 'childrenEscapingEnabled');
        self::assertTrue($property->getValue($subject));
        $subject->process($viewHelperNodeMock, InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER, new ParsingState());
        self::assertFalse($property->getValue($subject));
    }

    #[Test]
    public function processWrapsCurrentViewHelperInEscapeNode(): void
    {
        $mockNode = $this->createMock(ObjectAccessorNode::class);
        $subject = new Escape();
        self::assertInstanceOf(EscapingNode::class, $subject->process($mockNode, InterceptorInterface::INTERCEPT_OBJECTACCESSOR, new ParsingState()));
    }
}
