<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Fixtures\TestViewHelper;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

class ViewHelperNodeTest extends UnitTestCase
{
    /**
     * @var RenderingContextInterface
     */
    private $renderingContext;

    /**
     * @var ViewHelperResolver&MockObject
     */
    private $mockViewHelperResolver;

    public function setUp(): void
    {
        $this->renderingContext = new RenderingContextFixture();
        $this->mockViewHelperResolver = $this->createMock(ViewHelperResolver::class);
        $this->mockViewHelperResolver->expects(self::any())->method('resolveViewHelperClassName')->with('f', 'vh')->willReturn(TestViewHelper::class);
        $this->mockViewHelperResolver->expects(self::any())->method('createViewHelperInstanceFromClassName')->with(TestViewHelper::class)->willReturn(new TestViewHelper());
        $this->mockViewHelperResolver->expects(self::any())->method('getArgumentDefinitionsForViewHelper')->willReturn([
            'foo' => new ArgumentDefinition('foo', 'string', 'Dummy required argument', true)
        ]);
        $this->renderingContext->setViewHelperResolver($this->mockViewHelperResolver);
    }

    /**
     * @test
     */
    public function constructorSetsViewHelperAndArguments(): void
    {
        $arguments = ['foo' => 'bar'];
        /** @var ViewHelperNode|MockObject $viewHelperNode */
        $viewHelperNode = new ViewHelperNode($this->renderingContext, 'f', 'vh', $arguments, new ParsingState());
        self::assertAttributeEquals($arguments, 'arguments', $viewHelperNode);
    }

    /**
     * @test
     */
    public function testEvaluateCallsInvoker(): void
    {
        $invoker = $this->createMock(ViewHelperInvoker::class);
        $invoker->expects(self::once())->method('invoke')->willReturn('test');
        $this->renderingContext->setViewHelperInvoker($invoker);
        $node = new ViewHelperNode($this->renderingContext, 'f', 'vh', ['foo' => 'bar'], new ParsingState());
        $result = $node->evaluate($this->renderingContext);
        self::assertEquals('test', $result);
    }
}
