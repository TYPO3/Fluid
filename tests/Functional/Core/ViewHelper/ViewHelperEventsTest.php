<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\ViewHelper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class ViewHelperEventsTest extends AbstractFunctionalTestCase
{
    #[Test]
    public function nodeInitializedEvent(): void
    {
        self::expectException(\Exception::class);
        self::expectExceptionMessage('nodeInitializedEvent triggered');
        $view = new TemplateView();
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('<test:nodeInitializedEvent />');
        $view->render();

        // No second execution here because event only triggers for uncached templates
    }

    #[Test]
    #[IgnoreDeprecations]
    public function postParseEvent(): void
    {
        self::expectException(\Exception::class);
        self::expectExceptionMessage('postParseEvent triggered');
        $view = new TemplateView();
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('<test:postParseEvent />');
        $view->render();

        // No second execution here because event only triggers for uncached templates
    }

    public static function argumentsValidatedEventDataProvider(): array
    {
        return [
            ['<test:customValidation arg1="foo" />', 'foo||'],
            ['<test:customValidation arg2="foo" arg3="bar" />', '|foo|bar'],
            ['<test:customValidation arg1="foo" arg2="bar" arg3="baz" />', 'foo|bar|baz'],
        ];
    }

    #[Test]
    #[DataProvider('argumentsValidatedEventDataProvider')]
    public function argumentsValidatedEvent(string $template, string $expectedOutput): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        self::assertEquals($expectedOutput, $view->render());
    }

    public static function argumentsValidatedEventThrowsExceptionDataProvider(): array
    {
        return [
            ['<test:customValidation />'],
            ['<test:customValidation arg2="foo" />'],
        ];
    }

    #[Test]
    #[DataProvider('argumentsValidatedEventThrowsExceptionDataProvider')]
    public function argumentsValidatedEventThrowsException(string $template): void
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionCode(1755274666);
        $view = new TemplateView();
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        $view->render();
    }
}
