<?php
declare(strict_types=1);
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

    public function setUp(): void
    {
        $this->renderingContext = $this->getMock(RenderingContext::class, [], [], false, false);

        $this->abstractNode = $this->getMock(AbstractNode::class, ['execute']);

        $this->childNode = $this->getMock(AbstractNode::class);
        $this->abstractNode->addChild($this->childNode);
    }

    /**
     * @test
     */
    public function flattenWithExtractTrueReturnsNullIfNoChildNodes(): void
    {
        $this->abstractNode = $this->getMock(AbstractNode::class, ['execute']);
        $this->assertNull($this->abstractNode->flatten(true));
    }

    /**
     * @test
     */
    public function evaluateChildNodesReturnsNullIfNoChildNodesExist(): void
    {
        $abstractNode = $this->getMock(AbstractNode::class, ['execute']);
        $this->assertNull($abstractNode->execute($this->renderingContext));
    }

    /**
     * @test
     */
    public function evaluateChildNodesConcatenatesOutputs(): void
    {
        $child2 = clone $this->childNode;
        $child2->expects($this->once())->method('execute')->with($this->renderingContext)->willReturn('bar');
        $this->childNode->expects($this->once())->method('execute')->with($this->renderingContext)->willReturn('foo');
        $this->abstractNode->addChild($child2);
        $method = new \ReflectionMethod($this->abstractNode, 'evaluateChildNodes');
        $method->setAccessible(true);
        $result = $method->invokeArgs($this->abstractNode, [$this->renderingContext]);
        $this->assertEquals('foobar', $result);
    }

    /**
     * @test
     */
    public function childNodeCanBeReadOutAgain(): void
    {
        $this->assertSame($this->abstractNode->getChildren(), [$this->childNode]);
    }
}
