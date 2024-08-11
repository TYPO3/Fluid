<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\AbstractNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\UserWithToString;

final class AbstractNodeTest extends TestCase
{
    #[Test]
    public function evaluateChildNodesPassesRenderingContextToChildNodes(): void
    {
        $renderingContextMock = $this->createMock(RenderingContextInterface::class);
        $childNode = $this->createMock(NodeInterface::class);
        $childNode->expects(self::once())->method('evaluate')->with($renderingContextMock);
        $subject = $this->getMockBuilder(AbstractNode::class)->onlyMethods(['evaluate'])->getMock();
        $subject->addChildNode($childNode);
        $subject->evaluateChildNodes($renderingContextMock);
    }

    #[Test]
    public function evaluateChildNodesReturnsNullIfNoChildNodesExist(): void
    {
        $renderingContextMock = $this->createMock(RenderingContextInterface::class);
        $subject = $this->createMock(AbstractNode::class);
        self::assertNull($subject->evaluateChildNodes($renderingContextMock));
    }

    #[DataProvider('getChildNodeThrowsExceptionFiChildNodeCannotBeCastToStringTestValues')]
    #[Test]
    public function evaluateChildNodeThrowsExceptionIfChildNodeCannotBeCastToString(mixed $value, string $exceptionClass, int $exceptionCode, string $exceptionMessage): void
    {
        $this->expectException($exceptionClass);
        $this->expectExceptionCode($exceptionCode);
        $this->expectExceptionMessage($exceptionMessage);

        $renderingContextMock = $this->createMock(RenderingContextInterface::class);
        $childNode = $this->createMock(NodeInterface::class);
        $childNode->expects(self::once())->method('evaluate')->with($renderingContextMock)->willReturn($value);
        $subject = $this->getMockBuilder(AbstractNode::class)->onlyMethods(['evaluate'])->getMock();
        $subject->addChildNode($childNode);
        $method = new \ReflectionMethod($subject, 'evaluateChildNode');
        $method->invoke($subject, $childNode, $renderingContextMock, true);
    }

    public static function getChildNodeThrowsExceptionFiChildNodeCannotBeCastToStringTestValues(): array
    {
        return [
            [new \DateTime('now'), Exception::class, 1273753083, 'Cannot cast object of type "' . \DateTime::class . '" to string.'],
            [['some' => 'value'], Exception::class, 1698750868, 'Cannot cast an array to string.'],
        ];
    }

    #[Test]
    public function evaluateChildNodeCanCastToString(): void
    {
        $renderingContextMock = $this->createMock(RenderingContextInterface::class);
        $childNode = $this->createMock(NodeInterface::class);
        $withToString = new UserWithToString('foobar');
        $childNode->expects(self::once())->method('evaluate')->with($renderingContextMock)->willReturn($withToString);
        $subject = $this->getMockBuilder(AbstractNode::class)->onlyMethods(['evaluate'])->getMock();
        $subject->addChildNode($childNode);
        $method = new \ReflectionMethod($subject, 'evaluateChildNode');
        $result = $method->invoke($subject, $childNode, $renderingContextMock, true);
        self::assertEquals('foobar', $result);
    }

    #[Test]
    public function evaluateChildNodesConcatenatesOutputs(): void
    {
        $renderingContextMock = $this->createMock(RenderingContextInterface::class);
        $childNode = $this->createMock(NodeInterface::class);
        $subject = $this->getMockBuilder(AbstractNode::class)->onlyMethods(['evaluate'])->getMock();
        $subject->addChildNode($childNode);
        $child2 = clone $childNode;
        $child2->expects(self::once())->method('evaluate')->with($renderingContextMock)->willReturn('bar');
        $childNode->expects(self::once())->method('evaluate')->with($renderingContextMock)->willReturn('foo');
        $subject->addChildNode($child2);
        $method = new \ReflectionMethod($subject, 'evaluateChildNodes');
        $result = $method->invoke($subject, $renderingContextMock, true);
        self::assertEquals('foobar', $result);
    }

    #[Test]
    public function childNodeCanBeReadOutAgain(): void
    {
        $childNode = $this->createMock(NodeInterface::class);
        $subject = $this->getMockBuilder(AbstractNode::class)->onlyMethods(['evaluate'])->getMock();
        $subject->addChildNode($childNode);
        self::assertSame($subject->getChildNodes(), [$childNode]);
    }
}
