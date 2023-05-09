<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\AbstractNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\UserWithToString;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

class AbstractNodeTest extends UnitTestCase
{
    /**
     * @test
     */
    public function evaluateChildNodesPassesRenderingContextToChildNodes(): void
    {
        $renderingContextMock = $this->createMock(RenderingContextInterface::class);
        $childNode = $this->createMock(NodeInterface::class);
        $childNode->expects(self::once())->method('evaluate')->with($renderingContextMock);
        $subject = $this->getMockBuilder(AbstractNode::class)->onlyMethods(['evaluate'])->getMock();
        $subject->addChildNode($childNode);
        $subject->evaluateChildNodes($renderingContextMock);
    }

    /**
     * @test
     */
    public function evaluateChildNodesReturnsNullIfNoChildNodesExist(): void
    {
        $renderingContextMock = $this->createMock(RenderingContextInterface::class);
        $subject = $this->createMock(AbstractNode::class);
        self::assertNull($subject->evaluateChildNodes($renderingContextMock));
    }

    /**
     * @test
     */
    public function evaluateChildNodeThrowsExceptionIfChildNodeCannotBeCastToString(): void
    {
        $this->expectException(Exception::class);

        $renderingContextMock = $this->createMock(RenderingContextInterface::class);
        $childNode = $this->createMock(NodeInterface::class);
        $childNode->expects(self::once())->method('evaluate')->with($renderingContextMock)->willReturn(new \DateTime('now'));
        $subject = $this->getMockBuilder(AbstractNode::class)->onlyMethods(['evaluate'])->getMock();
        $subject->addChildNode($childNode);
        $method = new \ReflectionMethod($subject, 'evaluateChildNode');
        $method->invoke($subject, $childNode, $renderingContextMock, true);
    }

    /**
     * @test
     */
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

    /**
     * @test
     */
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

    /**
     * @test
     */
    public function childNodeCanBeReadOutAgain(): void
    {
        $childNode = $this->createMock(NodeInterface::class);
        $subject = $this->getMockBuilder(AbstractNode::class)->onlyMethods(['evaluate'])->getMock();
        $subject->addChildNode($childNode);
        self::assertSame($subject->getChildNodes(), [$childNode]);
    }
}
