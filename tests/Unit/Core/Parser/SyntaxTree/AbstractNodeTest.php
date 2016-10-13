<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\AbstractNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithToString;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * An AbstractNode Test
 */
class AbstractNodeTest extends UnitTestCase
{

    protected $renderingContext;

    protected $abstractNode;

    protected $childNode;

    public function setUp()
    {
        $this->renderingContext = $this->getMock(RenderingContext::class, [], [], '', false);

        $this->abstractNode = $this->getMock(AbstractNode::class, ['evaluate']);

        $this->childNode = $this->getMock(AbstractNode::class);
        $this->abstractNode->addChildNode($this->childNode);
    }

    /**
     * @test
     */
    public function evaluateChildNodesPassesRenderingContextToChildNodes()
    {
        $this->childNode->expects($this->once())->method('evaluate')->with($this->renderingContext);
        $this->abstractNode->evaluateChildNodes($this->renderingContext);
    }

    /**
     * @test
     */
    public function evaluateChildNodesReturnsNullIfNoChildNodesExist()
    {
        $abstractNode = $this->getMock(AbstractNode::class, ['evaluate']);
        $this->assertNull($abstractNode->evaluateChildNodes($this->renderingContext));
    }

    /**
     * @test
     */
    public function evaluateChildNodeThrowsExceptionIfChildNodeCannotBeCastToString()
    {
        $this->childNode->expects($this->once())->method('evaluate')->with($this->renderingContext)->willReturn(new \DateTime('now'));
        $method = new \ReflectionMethod($this->abstractNode, 'evaluateChildNode');
        $method->setAccessible(true);
        $this->setExpectedException(Exception::class);
        $method->invokeArgs($this->abstractNode, [$this->childNode, $this->renderingContext, true]);
    }

    /**
     * @test
     */
    public function evaluateChildNodeCanCastToString()
    {
        $withToString = new UserWithToString('foobar');
        $this->childNode->expects($this->once())->method('evaluate')->with($this->renderingContext)->willReturn($withToString);
        $method = new \ReflectionMethod($this->abstractNode, 'evaluateChildNode');
        $method->setAccessible(true);
        $result = $method->invokeArgs($this->abstractNode, [$this->childNode, $this->renderingContext, true]);
        $this->assertEquals('foobar', $result);
    }

    /**
     * @test
     */
    public function evaluateChildNodesConcatenatesOutputs()
    {
        $child2 = clone $this->childNode;
        $child2->expects($this->once())->method('evaluate')->with($this->renderingContext)->willReturn('bar');
        $this->childNode->expects($this->once())->method('evaluate')->with($this->renderingContext)->willReturn('foo');
        $this->abstractNode->addChildNode($child2);
        $method = new \ReflectionMethod($this->abstractNode, 'evaluateChildNodes');
        $method->setAccessible(true);
        $result = $method->invokeArgs($this->abstractNode, [$this->renderingContext, true]);
        $this->assertEquals('foobar', $result);
    }

    /**
     * @test
     */
    public function childNodeCanBeReadOutAgain()
    {
        $this->assertSame($this->abstractNode->getChildNodes(), [$this->childNode]);
    }
}
