<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Component\Error\ChildNotFoundException;
use TYPO3Fluid\Fluid\Core\ErrorHandler\ErrorHandlerInterface;
use TYPO3Fluid\Fluid\Core\Parser\PassthroughSourceException;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Rendering\FluidRenderer;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\Exception;
use TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException;
use TYPO3Fluid\Fluid\View\TemplatePaths;

/**
 * Test cases for the FluidRenderer API
 */
class FluidRendererTest extends UnitTestCase
{
    /**
     * @test
     */
    public function renderingContextSetWithSetterIsReturnedFromGetter(): void
    {
        $context = new RenderingContextFixture();
        $subject = new FluidRenderer($context);
        $otherContext = new RenderingContextFixture();
        $subject->setRenderingContext($otherContext);
        $this->assertSame($otherContext, $subject->getRenderingContext());
    }

    /**
     * @test
     */
    public function renderingContextPassedToConstructorIsReturnedFromGetter(): void
    {
        $context = new RenderingContextFixture();
        $subject = new FluidRenderer($context);
        $this->assertSame($context, $subject->getRenderingContext());
    }

    /**
     * @test
     */
    public function testRenderSectionThrowsExceptionIfSectionMissingAndNotIgnoringUnknown(): void
    {
        $parsedTemplate = $this->getMockBuilder(ComponentInterface::class)->setMethods(['getNamedChild'])->getMockForAbstractClass();
        $parsedTemplate->expects($this->any())->method('getNamedChild')->willThrowException(new ChildNotFoundException('...'));
        $context = new RenderingContextFixture();
        $subject = $this->getMockBuilder(FluidRenderer::class)
            ->setMethods(['getCurrentParsedTemplate'])
            ->setConstructorArgs([$context])
            ->enableOriginalConstructor()
            ->getMock();
        $subject->expects($this->once())->method('getCurrentParsedTemplate')->willReturn($parsedTemplate);
        $this->setExpectedException(ChildNotFoundException::class);
        $subject->renderSection('Missing');
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function renderSectionCatchesInvalidTemplateResourceExceptionWithOptionalTrue(): void
    {
        $subject = $this->getMockBuilder(FluidRenderer::class)->setMethods(['getCurrentParsedTemplate'])->disableOriginalConstructor()->getMock();
        $subject->expects($this->once())->method('getCurrentParsedTemplate')->willThrowException(new InvalidTemplateResourceException('foo'));
        $subject->setRenderingContext(new RenderingContextFixture());
        $subject->renderSection('Foo', [], true);
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function renderSectionRethrowsInvalidTemplateResourceExceptionWithOptionalFalse(): void
    {
        $context = new RenderingContextFixture();
        $subject = $this->getMockBuilder(FluidRenderer::class)->setMethods(['getCurrentParsedTemplate'])->setConstructorArgs([$context])->getMock();
        $subject->expects($this->once())->method('getCurrentParsedTemplate')->willThrowException(new InvalidTemplateResourceException('foo'));
        $this->setExpectedException(InvalidTemplateResourceException::class);
        $subject->renderSection('Foo', [], false);
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function renderSectionDelegatesViewExceptionToErrorHandler(): void
    {
        $context = new RenderingContextFixture();
        $errorHandler = $this->getMockBuilder(ErrorHandlerInterface::class)->getMockForAbstractClass();
        $errorHandler->expects($this->once())->method('handleViewError');
        $context->setErrorHandler($errorHandler);
        $subject = $this->getMockBuilder(FluidRenderer::class)->setMethods(['getCurrentParsedTemplate'])->setConstructorArgs([$context])->getMock();
        $subject->expects($this->once())->method('getCurrentParsedTemplate')->willThrowException(new Exception('foo'));
        $subject->renderSection('Foo', [], false);
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function renderPartialDelegatesToRenderSectionWhenBothPartialAndSectionProvided(): void
    {
        $context = new RenderingContextFixture();
        $subject = $this->getMockBuilder(FluidRenderer::class)->setMethods(['renderSection'])->setConstructorArgs([$context])->getMock();
        $subject->expects($this->once())->method('renderSection')->with('section', [], false)->willReturn('something');
        $paths = $this->getMockBuilder(TemplatePaths::class)->setMethods(['getPartialIdentifier'])->getMock();
        $parser = $this->getMockBuilder(TemplateParser::class)->setMethods(['getOrParseAndStoreTemplate'])->disableOriginalConstructor()->getMock();
        $context->setTemplateParser($parser);
        $context->setTemplatePaths($paths);
        $paths->expects($this->once())->method('getPartialIdentifier')->willReturn('foo');
        $output = $subject->renderPartial('Foo', 'section', [], false);
        $this->assertSame('something', $output);
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function renderPartialReturnsSourceOnPassthroughSourceException(): void
    {
        $exception = new PassthroughSourceException('foo');
        $exception->setSource('source');
        $context = new RenderingContextFixture();
        $subject = new FluidRenderer($context);
        $paths = $this->getMockBuilder(TemplatePaths::class)->setMethods(['getPartialIdentifier'])->getMock();
        $parser = $this->getMockBuilder(TemplateParser::class)->setMethods(['getOrParseAndStoreTemplate'])->disableOriginalConstructor()->getMock();
        $context->setTemplateParser($parser);
        $context->setTemplatePaths($paths);
        $parser->expects($this->once())->method('getOrParseAndStoreTemplate')->willThrowException($exception);
        $paths->expects($this->once())->method('getPartialIdentifier')->willReturn('foo');
        $output = $subject->renderPartial('Foo', null, [], false);
        $this->assertSame('source', $output);
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function renderPartialReturnsEmptyStringOnInvalidTemplateResourceExceptionWithIgnoreUnknownTrue(): void
    {
        $exception = new InvalidTemplateResourceException('foo');
        $context = new RenderingContextFixture();
        $subject = new FluidRenderer($context);
        $paths = $this->getMockBuilder(TemplatePaths::class)->setMethods(['getPartialIdentifier'])->getMock();
        $parser = $this->getMockBuilder(TemplateParser::class)->setMethods(['getOrParseAndStoreTemplate'])->disableOriginalConstructor()->getMock();
        $context->setTemplateParser($parser);
        $context->setTemplatePaths($paths);
        $parser->expects($this->once())->method('getOrParseAndStoreTemplate')->willThrowException($exception);
        $paths->expects($this->once())->method('getPartialIdentifier')->willReturn('foo');
        $output = $subject->renderPartial('Foo', null, [], true);
        $this->assertSame('', $output);
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function renderPartialDelegatesViewExceptionToErrorHandler(): void
    {
        $exception = new Exception('foo');
        $context = new RenderingContextFixture();
        $subject = new FluidRenderer($context);
        $paths = $this->getMockBuilder(TemplatePaths::class)->setMethods(['getPartialIdentifier'])->getMock();
        $parser = $this->getMockBuilder(TemplateParser::class)->setMethods(['getOrParseAndStoreTemplate'])->disableOriginalConstructor()->getMock();
        $errorHandler = $this->getMockBuilder(ErrorHandlerInterface::class)->getMockForAbstractClass();
        $context->setTemplateParser($parser);
        $context->setTemplatePaths($paths);
        $context->setErrorHandler($errorHandler);
        $errorHandler->expects($this->once())->method('handleViewError');
        $parser->expects($this->once())->method('getOrParseAndStoreTemplate')->willThrowException($exception);
        $paths->expects($this->once())->method('getPartialIdentifier')->willReturn('foo');
        $output = $subject->renderPartial('Foo', null, [], true);
        $this->assertSame('', $output);
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function renderComponentRendersComponent(): void
    {
        $context = new RenderingContextFixture();
        $subject = new FluidRenderer($context);
        $component = $this->getMockBuilder(ComponentInterface::class)->setMethods(['evaluate'])->getMockForAbstractClass();
        $component->expects($this->once())->method('evaluate')->with($context);
        $subject->renderComponent($component);
    }

    /**
     * @test
     */
    public function getCurrentParsedTemplateUsesDefaultClosuresAsFallback(): void
    {
        $paths = $this->getMockBuilder(TemplatePaths::class)->setMethods(['getTemplateIdentifier', 'getTemplateSource'])->getMock();
        $paths->expects($this->once())->method('getTemplateIdentifier')->willReturn('foo');
        $paths->expects($this->once())->method('getTemplateSource')->willReturn('<f:section name="foo">foo</f:section>');
        $context = new RenderingContextFixture();
        $context->setTemplatePaths($paths);
        $subject = new FluidRenderer($context);
        $subject->renderSection('foo', []);
    }
}