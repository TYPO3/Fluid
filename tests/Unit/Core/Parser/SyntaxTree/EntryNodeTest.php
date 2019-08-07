<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\EntryNode;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

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
        $provider = $this->getMockBuilder(VariableProviderInterface::class)->getMockForAbstractClass();
        $provider->expects($this->once())->method('add')->with('argumentName', 'argumentValue');
        $context->setVariableProvider($provider);
        $subject->getArguments()['argumentName'] = 'argumentValue';
        $subject->evaluate($context);
    }

    /**
     * @test
     */
    public function testEvaluateCallsEvaluateChildNodes(): void
    {
        $subject = $this->getMock(EntryNode::class, ['evaluateChildren']);
        $subject->expects($this->once())->method('evaluateChildren');
        $subject->evaluate(new RenderingContextFixture());
    }
}
