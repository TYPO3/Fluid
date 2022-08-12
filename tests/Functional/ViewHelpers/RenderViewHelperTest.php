<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers;

use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers\RenderableFixture;
use TYPO3Fluid\Fluid\View\Exception\InvalidSectionException;
use TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException;
use TYPO3Fluid\Fluid\View\TemplateView;

class RenderViewHelperTest extends AbstractFunctionalTestCase
{
    /**
     * @test
     */
    public function exceptionForOptionalSetToFalseAndNoTargetGiven(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $template = '<f:render optional="false"/>';
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        $view->render();
    }

    /**
     * @test
     */
    public function exceptionForInvalidDelegate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $template = '<f:render delegate="TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture"/>';
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        $view->render();
    }

    /**
     * @test
     */
    public function exceptionForMissingPartial(): void
    {
        $this->expectException(InvalidTemplateResourceException::class);
        $this->expectExceptionCode(1225709595);
        $template = '<f:render partial="non-existent" />';
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths([__DIR__ . '/../Fixtures/Partials/']);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        $view->render();
    }

    /**
     * @test
     */
    public function exceptionForMissingSection(): void
    {
        $this->expectException(InvalidSectionException::class);
        $template = '<f:render partial="Partial" section="non-existent" />';
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths([__DIR__ . '/../Fixtures/Partials/']);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        $view->render();
    }

    public function renderDataProvider(): \Generator
    {
        yield 'delegate' => [
            '<f:render delegate="TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers\ParsedTemplateImplementationFixture"/>',
            [],
            'rendered by fixture',
        ];
        yield 'renderable' => [
            '<f:render renderable="{renderable}"/>',
            ['renderable' => new RenderableFixture()],
            'rendered by renderable',
        ];
        yield 'section' => [
            '<f:render section="main"/><f:section name="main">rendered by section</f:section>',
            [],
            'rendered by section',
        ];
        yield 'partial' => [
            '<f:render partial="Partial" />',
            [],
            'rendered by partial',
        ];
        yield 'partial section' => [
            '<f:render partial="Partial" section="main" />',
            [],
            'rendered by partial section',
        ];
        yield 'default if partial has no output' => [
            '<f:render partial="non-existent" default="default-foobar" optional="true" />',
            [],
            'default-foobar',
        ];
        yield 'default if section has no output' => [
            '<f:render partial="Partial" section="non-existent" default="default-foobar" optional="true" />',
            [],
            'default-foobar',
        ];
        yield 'contentAs' => [
            '<f:render partial="Partial" section="main" contentAs="content">foo bar</f:render>{content}',
            [],
            'rendered by partial section foo bar',
        ];
        yield 'children rendered when nothing else is given' => [
            '<f:render optional="true">tag content</f:render>',
            [],
            'tag content',
        ];
        yield 'all parameters passed' => [
            '<f:render partial="Partial" section="all" arguments="{_all}" />',
            ['foo' => 'bar', 'test' => 'test'],
            'foo: bar test: test',
        ];
        yield 'specific parameter passed' => [
            '<f:render partial="Partial" section="all" arguments="{foo: test1}" />',
            ['foo' => 'bar', 'test1' => 'test2'],
            'foo: test2',
        ];
    }

    /**
     * @test
     * @dataProvider renderDataProvider
     */
    public function render(string $template, array $arguments, string $expected): void
    {
        $view = new TemplateView();
        $view->assignMultiple($arguments);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths([__DIR__ . '/../Fixtures/Partials/']);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        self::assertSame($expected, trim($view->render()));

        $view = new TemplateView();
        $view->assignMultiple($arguments);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths([__DIR__ . '/../Fixtures/Partials/']);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        self::assertSame($expected, trim($view->render()));
    }
}
