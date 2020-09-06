<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollection;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\EntryNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ReferenceNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Rendering\FluidRenderer;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for ReferenceNode
 */
class ReferenceNodeTest extends UnitTestCase
{
    /**
     * @test
     * @throws \ReflectionException
     */
    public function evaluateAssignsArgumentsAsVariables(): void
    {
        $subject = new ReferenceNode('test');
        $context = new RenderingContextFixture();
        $root = (new RootNode())->addChild((new EntryNode())->setName('test')->addChild(new ObjectAccessorNode('test')));
        $renderer = $this->getMockBuilder(FluidRenderer::class)->setMethods(['getComponentBeingRendered'])->setConstructorArgs([$context])->getMock();
        $renderer->expects($this->once())->method('getComponentBeingRendered')->willReturn($root);
        $provider = $this->getMockBuilder(StandardVariableProvider::class)->setMethods(['getScopeCopy'])->getMock();
        $provider->expects($this->once())->method('getScopeCopy')->with(['test' => 'test']);
        $context->setVariableProvider($provider);
        $context->setRenderer($renderer);
        $subject->getArguments()['test'] = 'test';
        $subject->evaluate($context);
    }

    /**
     * @test
     */
    public function onOpenSetsRenderingContextInArguments(): void
    {
        $context = new RenderingContextFixture();
        $arguments = $this->getMockBuilder(ArgumentCollection::class)->setMethods(['setRenderingContext'])->getMock();
        $arguments->expects($this->once())->method('setRenderingContext')->with($context);
        $subject = new ReferenceNode('test');
        $subject->setArguments($arguments);
        $subject->onOpen($context);
    }
}
