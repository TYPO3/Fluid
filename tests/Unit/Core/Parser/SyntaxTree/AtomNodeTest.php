<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollection;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\AtomNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\EntryNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for AtomNode
 */
class AtomNodeTest extends UnitTestCase
{
    /**
     * @test
     * @throws \ReflectionException
     */
    public function evaluateParsesAtomAndAssignsArgumentsAsVariablesAndAddsChildren(): void
    {
        $subject = new AtomNode();
        $subject->setFile(__DIR__ . '/../../../../Fixtures/Atoms/testAtom.html');
        $child = new ObjectAccessorNode('foo');
        $subject->addChild($child);
        $context = new RenderingContextFixture();
        $atom = $this->getMockBuilder(EntryNode::class)->setMethods(['addChild'])->getMock();
        $atom->expects($this->once())->method('addChild')->with($child);
        $parser = $this->getMockBuilder(TemplateParser::class)->setMethods(['parseFile'])->disableOriginalConstructor()->getMock();
        $parser->expects($this->once())->method('parseFile')->willReturn($atom);
        $provider = $this->getMockBuilder(VariableProviderInterface::class)->getMockForAbstractClass();
        $provider->expects($this->once())->method('getScopeCopy')->with(['argumentName' => 'argumentValue']);
        $context->setVariableProvider($provider);
        $context->setTemplateParser($parser);
        $subject->getArguments()['argumentName'] = 'argumentValue';
        $subject->evaluate($context);
    }

    /**
     * @test
     */
    public function onOpenSetsRenderingContextInArgumentCollection(): void
    {
        $context = new RenderingContextFixture();
        $arguments = $this->getMockBuilder(ArgumentCollection::class)->setMethods(['setRenderingContext'])->getMock();
        $arguments->expects($this->once())->method('setRenderingContext')->with($context);
        $subject = $this->getMockBuilder(AtomNode::class)->setMethods(['getArguments'])->getMock();
        $subject->expects($this->once())->method('getArguments')->willReturn($arguments);
        $subject->onOpen($context);
    }
}
