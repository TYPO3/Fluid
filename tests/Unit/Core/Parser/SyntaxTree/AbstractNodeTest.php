<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\AbstractNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\UserWithToString;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

class AbstractNodeTest extends UnitTestCase
{
    private $renderingContext;
    private $abstractNode;
    private $childNode;

    public function setUp(): void
    {
        $this->renderingContext = $this->getMock(RenderingContext::class, [], [], false, false);
        $this->abstractNode = $this->getMock(AbstractNode::class, ['evaluate']);
        $this->childNode = $this->getMock(AbstractNode::class);
        $this->abstractNode->addChildNode($this->childNode);
    }

    /**
     * @test
     */
    public function evaluateChildNodesPassesRenderingContextToChildNodes(): void
    {
        $this->childNode->expects(self::once())->method('evaluate')->with($this->renderingContext);
        $this->abstractNode->evaluateChildNodes($this->renderingContext);
    }

    /**
     * @test
     */
    public function evaluateChildNodesReturnsNullIfNoChildNodesExist(): void
    {
        $abstractNode = $this->getMock(AbstractNode::class, ['evaluate']);
        self::assertNull($abstractNode->evaluateChildNodes($this->renderingContext));
    }

    /**
     * @test
     */
    public function evaluateChildNodeThrowsExceptionIfChildNodeCannotBeCastToString(): void
    {
        $this->expectException(Exception::class);
        $this->childNode->expects(self::once())->method('evaluate')->with($this->renderingContext)->willReturn(new \DateTime('now'));
        $method = new \ReflectionMethod($this->abstractNode, 'evaluateChildNode');
        $method->setAccessible(true);
        $method->invokeArgs($this->abstractNode, [$this->childNode, $this->renderingContext, true]);
    }

    /**
     * @test
     */
    public function evaluateChildNodeCanCastToString(): void
    {
        $withToString = new UserWithToString('foobar');
        $this->childNode->expects(self::once())->method('evaluate')->with($this->renderingContext)->willReturn($withToString);
        $method = new \ReflectionMethod($this->abstractNode, 'evaluateChildNode');
        $method->setAccessible(true);
        $result = $method->invokeArgs($this->abstractNode, [$this->childNode, $this->renderingContext, true]);
        self::assertEquals('foobar', $result);
    }

    /**
     * @test
     */
    public function evaluateChildNodesConcatenatesOutputs(): void
    {
        $child2 = clone $this->childNode;
        $child2->expects(self::once())->method('evaluate')->with($this->renderingContext)->willReturn('bar');
        $this->childNode->expects(self::once())->method('evaluate')->with($this->renderingContext)->willReturn('foo');
        $this->abstractNode->addChildNode($child2);
        $method = new \ReflectionMethod($this->abstractNode, 'evaluateChildNodes');
        $method->setAccessible(true);
        $result = $method->invokeArgs($this->abstractNode, [$this->renderingContext, true]);
        self::assertEquals('foobar', $result);
    }

    /**
     * @test
     */
    public function childNodeCanBeReadOutAgain(): void
    {
        self::assertSame($this->abstractNode->getChildNodes(), [$this->childNode]);
    }
}
