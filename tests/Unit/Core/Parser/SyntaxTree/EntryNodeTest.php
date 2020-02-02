<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3Fluid\Fluid\Component\Argument\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\EntryNode;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\ViewHelpers\ExtendViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\ParameterViewHelper;

/**
 * Testcase for EntryNode
 */
class EntryNodeTest extends UnitTestCase
{
    /**
     * @test
     * @throws \ReflectionException
     */
    public function evaluateAssignsArgumentsAsVariables(): void
    {
        $subject = new EntryNode();
        $context = new RenderingContextFixture();
        /** @var VariableProviderInterface|MockObject $provider */
        $provider = $this->getMockBuilder(VariableProviderInterface::class)->getMockForAbstractClass();
        $provider->expects($this->once())->method('getScopeCopy')->with(['argumentName' => 'argumentValue']);
        $context->setVariableProvider($provider);
        $subject->getArguments()['argumentName'] = 'argumentValue';
        $subject->evaluate($context);
    }

    /**
     * @test
     */
    public function onCloseAddsArgumentsFromParameterViewHelperChildren(): void
    {
        $context = new RenderingContextFixture();
        $subject = new EntryNode();
        $definitionArguments = ['name' => 'foo', 'type' => 'string', 'description' => 'Test'];

        $child = (new ParameterViewHelper())->onOpen($context);
        $child->getArguments()->setRenderingContext($context)->assignAll($definitionArguments);

        $subject->getArguments()->setRenderingContext($context);
        $subject->addChild($child);
        $subject->onClose($context);

        $expectedDefinitions = ['foo' => new ArgumentDefinition('foo', 'string', 'Test', false)];
        $this->assertEquals($expectedDefinitions, $subject->getArguments()->getDefinitions());
    }

    /**
     * @test
     */
    public function onCloseAddsArgumentsFromExtendViewHelperChildren(): void
    {
        $context = new RenderingContextFixture();
        $subject = new EntryNode();
        $definitionArguments = ['atom' => 'foo:test'];

        $atom = new EntryNode();
        $atom->getArguments()->addDefinition(
            new ArgumentDefinition('foo', 'string', 'Test', false)
        );

        /** @var ViewHelperResolver|MockObject $resolver */
        $resolver = $this->getMockBuilder(ViewHelperResolver::class)->setMethods(['resolveAtom'])->setConstructorArgs([$context])->getMock();
        $resolver->expects($this->once())->method('resolveAtom')->with('foo', 'test')->willReturn($atom);
        $context->setViewHelperResolver($resolver);

        $child = (new ExtendViewHelper());
        $child->getArguments()->setRenderingContext($context)->assignAll($definitionArguments);
        $child->onOpen($context);

        $subject->getArguments()->setRenderingContext($context);
        $subject->addChild($child);
        $subject->onClose($context);

        $expectedDefinitions = ['foo' => new ArgumentDefinition('foo', 'string', 'Test', false)];
        $this->assertEquals($expectedDefinitions, $subject->getArguments()->getDefinitions());
    }

    /**
     * @test
     */
    public function testEvaluateCallsEvaluateChildNodes(): void
    {
        /** @var EntryNode|MockObject $subject */
        $subject = $this->getMock(EntryNode::class, ['evaluateChildNodes']);
        $subject->expects($this->once())->method('evaluateChildNodes');
        $subject->evaluate(new RenderingContextFixture());
    }
}
