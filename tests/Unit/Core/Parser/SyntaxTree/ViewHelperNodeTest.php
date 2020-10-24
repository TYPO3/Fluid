<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Fixtures\TestViewHelper;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\Fixtures\ChildNodeAccessFacetViewHelper;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for \TYPO3Fluid\CMS\Fluid\Core\Parser\SyntaxTree\ViewHelperNode
 */
class ViewHelperNodeTest extends UnitTestCase
{

    /**
     * @var RenderingContext
     */
    protected $renderingContext;

    /**
     * @var TemplateVariableContainer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $templateVariableContainer;

    /**
     * @var ViewHelperResolver|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $mockViewHelperResolver;

    /**
     * Setup fixture
     */
    public function setUp(): void
    {
        $this->renderingContext = new RenderingContextFixture();
        $this->mockViewHelperResolver = $this->getMock(ViewHelperResolver::class, ['resolveViewHelperClassName', 'createViewHelperInstanceFromClassName', 'getArgumentDefinitionsForViewHelper']);
        $this->mockViewHelperResolver->expects($this->any())->method('resolveViewHelperClassName')->with('f', 'vh')->willReturn(TestViewHelper::class);
        $this->mockViewHelperResolver->expects($this->any())->method('createViewHelperInstanceFromClassName')->with(TestViewHelper::class)->willReturn(new TestViewHelper());
        $this->mockViewHelperResolver->expects($this->any())->method('getArgumentDefinitionsForViewHelper')->willReturn([
            'foo' => new ArgumentDefinition('foo', 'string', 'Dummy required argument', true)
        ]);
        $this->renderingContext->setViewHelperResolver($this->mockViewHelperResolver);
    }

    /**
     * @test
     */
    public function constructorSetsViewHelperAndArguments()
    {
        $arguments = ['foo' => 'bar'];
        /** @var ViewHelperNode|\PHPUnit\Framework\MockObject\MockObject $viewHelperNode */
        $viewHelperNode = new ViewHelperNode($this->renderingContext, 'f', 'vh', $arguments, new ParsingState());

        $this->assertAttributeEquals($arguments, 'arguments', $viewHelperNode);
    }

    /**
     * @test
     */
    public function testEvaluateCallsInvoker()
    {
        $invoker = $this->getMock(ViewHelperInvoker::class, ['invoke']);
        $invoker->expects($this->once())->method('invoke')->willReturn('test');
        $this->renderingContext->setViewHelperInvoker($invoker);
        $node = new ViewHelperNode($this->renderingContext, 'f', 'vh', ['foo' => 'bar'], new ParsingState());
        $result = $node->evaluate($this->renderingContext);
        $this->assertEquals('test', $result);
    }
}
