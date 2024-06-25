<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\View;

use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\Compiler\AbstractCompiledTemplate;
use TYPO3Fluid\Fluid\Core\ErrorHandler\StandardErrorHandler;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\Tests\Unit\View\Fixtures\AbstractTemplateViewTestFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\AbstractTemplateView;
use TYPO3Fluid\Fluid\View\Exception\InvalidSectionException;

class AbstractTemplateViewTest extends UnitTestCase
{
    #[Test]
    public function getRenderingContextReturnsPreviouslySetRenderingContext(): void
    {
        $renderingContext = $this->createMock(RenderingContextInterface::class);
        $renderingContext->expects(self::once())->method('getViewHelperVariableContainer')->willReturn($this->createMock(ViewHelperVariableContainer::class));
        $subject = new AbstractTemplateViewTestFixture();
        $subject->setRenderingContext($renderingContext);
        self::assertSame($renderingContext, $subject->getRenderingContext());
    }

    #[Test]
    public function getViewHelperResolverReturnsViewHelperResolverFromRenderingContext(): void
    {
        $renderingContext = $this->createMock(RenderingContextInterface::class);
        $renderingContext->expects(self::any())->method('getViewHelperVariableContainer')->willReturn($this->createMock(ViewHelperVariableContainer::class));
        $viewHelperResolver = $this->createMock(ViewHelperResolver::class);
        $renderingContext->expects(self::once())->method('getViewHelperResolver')->willReturn($viewHelperResolver);
        $subject = new AbstractTemplateViewTestFixture();
        $subject->setRenderingContext($renderingContext);
        self::assertSame($viewHelperResolver, $subject->getViewHelperResolver());
    }

    #[Test]
    public function assignAddsValueToTemplateVariableContainer(): void
    {
        $renderingContext = $this->createMock(RenderingContextInterface::class);
        $renderingContext->expects(self::any())->method('getViewHelperVariableContainer')->willReturn($this->createMock(ViewHelperVariableContainer::class));
        $variableProvider = $this->createMock(VariableProviderInterface::class);
        $renderingContext->expects(self::any())->method('getVariableProvider')->willReturn($variableProvider);
        $subject = new AbstractTemplateViewTestFixture();
        $subject->setRenderingContext($renderingContext);
        $series = [
            ['foo', 'FooValue'],
            ['bar', 'BarValue'],
        ];
        $variableProvider->expects(self::exactly(2))->method('add')->willReturnCallback(function (...$args) use (&$series): void {
            [$expectedArgOne, $expectedArgTwo] = array_shift($series);
            self::assertSame($expectedArgOne, $args[0]);
            self::assertSame($expectedArgTwo, $args[1]);
        });
        $subject->assign('foo', 'FooValue')->assign('bar', 'BarValue');
    }

    #[Test]
    public function assignCanOverridePreviouslyAssignedValues(): void
    {
        $renderingContext = $this->createMock(RenderingContextInterface::class);
        $renderingContext->expects(self::any())->method('getViewHelperVariableContainer')->willReturn($this->createMock(ViewHelperVariableContainer::class));
        $variableProvider = $this->createMock(VariableProviderInterface::class);
        $renderingContext->expects(self::any())->method('getVariableProvider')->willReturn($variableProvider);
        $subject = new AbstractTemplateViewTestFixture();
        $subject->setRenderingContext($renderingContext);
        $series = [
            ['foo', 'FooValue'],
            ['foo', 'FooValueOverridden'],
        ];
        $variableProvider->expects(self::exactly(2))->method('add')->willReturnCallback(function (...$args) use (&$series): void {
            [$expectedArgOne, $expectedArgTwo] = array_shift($series);
            self::assertSame($expectedArgOne, $args[0]);
            self::assertSame($expectedArgTwo, $args[1]);
        });
        $subject->assign('foo', 'FooValue')->assign('foo', 'FooValueOverridden');
    }

    #[Test]
    public function assignMultipleAddsValuesToTemplateVariableContainer(): void
    {
        $renderingContext = $this->createMock(RenderingContextInterface::class);
        $renderingContext->expects(self::any())->method('getViewHelperVariableContainer')->willReturn($this->createMock(ViewHelperVariableContainer::class));
        $variableProvider = $this->createMock(VariableProviderInterface::class);
        $renderingContext->expects(self::any())->method('getVariableProvider')->willReturn($variableProvider);
        $subject = new AbstractTemplateViewTestFixture();
        $subject->setRenderingContext($renderingContext);
        $series = [
            ['foo', 'FooValue'],
            ['bar', 'BarValue'],
            ['baz', 'BazValue'],
        ];
        $variableProvider->expects(self::exactly(3))->method('add')->willReturnCallback(function (...$args) use (&$series): void {
            [$expectedArgOne, $expectedArgTwo] = array_shift($series);
            self::assertSame($expectedArgOne, $args[0]);
            self::assertSame($expectedArgTwo, $args[1]);
        });
        $subject->assignMultiple(['foo' => 'FooValue', 'bar' => 'BarValue'])->assignMultiple(['baz' => 'BazValue']);
    }

    #[Test]
    public function assignMultipleCanOverridePreviouslyAssignedValues(): void
    {
        $renderingContext = $this->createMock(RenderingContextInterface::class);
        $renderingContext->expects(self::any())->method('getViewHelperVariableContainer')->willReturn($this->createMock(ViewHelperVariableContainer::class));
        $variableProvider = $this->createMock(VariableProviderInterface::class);
        $renderingContext->expects(self::any())->method('getVariableProvider')->willReturn($variableProvider);
        $subject = new AbstractTemplateViewTestFixture();
        $subject->setRenderingContext($renderingContext);
        $series = [
            ['foo', 'FooValue'],
            ['foo', 'FooValueOverridden'],
            ['bar', 'BarValue'],
        ];
        $variableProvider->expects(self::exactly(3))->method('add')->willReturnCallback(function (...$args) use (&$series): void {
            [$expectedArgOne, $expectedArgTwo] = array_shift($series);
            self::assertSame($expectedArgOne, $args[0]);
            self::assertSame($expectedArgTwo, $args[1]);
        });
        $subject->assign('foo', 'FooValue')->assignMultiple(['foo' => 'FooValueOverridden', 'bar' => 'BarValue']);
    }

    #[Test]
    public function renderSectionThrowsExceptionIfSectionMissingAndNotIgnoringUnknownWithNotCompiledTemplate(): void
    {
        $this->expectException(InvalidSectionException::class);

        $renderingContext = $this->createMock(RenderingContextInterface::class);
        $renderingContext->expects(self::any())->method('getViewHelperVariableContainer')->willReturn($this->createMock(ViewHelperVariableContainer::class));
        $renderingContext->expects(self::any())->method('getErrorHandler')->willReturn(new StandardErrorHandler());
        $parsedTemplate = $this->createMock(AbstractCompiledTemplate::class);
        $parsedTemplate->expects(self::once())->method('isCompiled')->willReturn(false);
        $parsedTemplate->expects(self::any())->method('getVariableContainer')->willReturn(new StandardVariableProvider(['sections' => []]));
        $subject = $this->getMockBuilder(AbstractTemplateView::class)->onlyMethods(['getCurrentParsedTemplate', 'getCurrentRenderingType'])->getMock();
        $subject->setRenderingContext($renderingContext);
        $subject->expects(self::once())->method('getCurrentRenderingType')->willReturn(AbstractTemplateView::RENDERING_LAYOUT);
        $subject->expects(self::once())->method('getCurrentParsedTemplate')->willReturn($parsedTemplate);
        $subject->renderSection('Missing');
    }

    #[Test]
    public function renderSectionThrowsExceptionIfSectionMissingAndNotIgnoringUnknownWithCompiledTemplate(): void
    {
        $this->expectException(InvalidSectionException::class);

        $renderingContext = $this->createMock(RenderingContextInterface::class);
        $renderingContext->expects(self::any())->method('getViewHelperVariableContainer')->willReturn($this->createMock(ViewHelperVariableContainer::class));
        $renderingContext->expects(self::any())->method('getErrorHandler')->willReturn(new StandardErrorHandler());
        $parsedTemplate = $this->createMock(AbstractCompiledTemplate::class);
        $parsedTemplate->expects(self::once())->method('isCompiled')->willReturn(true);
        $parsedTemplate->expects(self::any())->method('getVariableContainer')->willReturn(new StandardVariableProvider(['sections' => []]));
        $subject = $this->getMockBuilder(AbstractTemplateView::class)->onlyMethods(['getCurrentParsedTemplate', 'getCurrentRenderingType'])->getMock();
        $subject->setRenderingContext($renderingContext);
        $subject->expects(self::once())->method('getCurrentRenderingType')->willReturn(AbstractTemplateView::RENDERING_LAYOUT);
        $subject->expects(self::once())->method('getCurrentParsedTemplate')->willReturn($parsedTemplate);
        $subject->renderSection('Missing');
    }

    #[Test]
    public function renderSectionOnCompiledTemplateDoesNotThrowExceptionWhenIgnoreUnknownIsTrue(): void
    {
        $renderingContext = $this->createMock(RenderingContextInterface::class);
        $renderingContext->expects(self::any())->method('getViewHelperVariableContainer')->willReturn($this->createMock(ViewHelperVariableContainer::class));
        $renderingContext->expects(self::any())->method('getErrorHandler')->willReturn(new StandardErrorHandler());
        $parsedTemplate = $this->createMock(AbstractCompiledTemplate::class);
        $parsedTemplate->expects(self::once())->method('isCompiled')->willReturn(true);
        $subject = $this->getMockBuilder(AbstractTemplateView::class)->onlyMethods(['getCurrentParsedTemplate', 'getCurrentRenderingType'])->getMock();
        $subject->setRenderingContext($renderingContext);
        $subject->expects(self::once())->method('getCurrentRenderingType')->willReturn(AbstractTemplateView::RENDERING_LAYOUT);
        $subject->expects(self::once())->method('getCurrentParsedTemplate')->willReturn($parsedTemplate);
        $subject->renderSection('Section', [], true);
    }
}
