<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\Parser\Interceptor;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class EscapeInterceptorTest extends AbstractFunctionalTestCase
{
    public static function getTemplateCodeFixturesAndExpectations(): array
    {
        return [
            'escapeChildren can be disabled in template' => [
                '{escapingEnabled=false}<test:escapeChildrenEnabledAndEscapeOutputDisabled>{settings.test}</test:escapeChildrenEnabledAndEscapeOutputDisabled>',
                '<strong>Bla</strong>',
                '&lt;strong&gt;Bla&lt;/strong&gt;',
            ],
            'escapeOutput can be disabled in template' => [
                '{escapingEnabled=false}<test:escapeChildrenDisabledAndEscapeOutputEnabled>{settings.test}</test:escapeChildrenDisabledAndEscapeOutputEnabled>',
                '<strong>Bla</strong>',
                '&lt;strong&gt;Bla&lt;/strong&gt;',
            ],
            'EscapeChildrenEnabledAndEscapeOutputDisabled: Tag syntax with children, properly encodes variable value' => [
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled>{settings.test}</test:escapeChildrenEnabledAndEscapeOutputDisabled>',
                '&lt;strong&gt;Bla&lt;/strong&gt;',
                '<strong>Bla</strong>',
            ],
            'EscapeChildrenEnabledAndEscapeOutputDisabled: Inline syntax with children, properly encodes variable value' => [
                '{settings.test -> test:escapeChildrenEnabledAndEscapeOutputDisabled()}',
                '&lt;strong&gt;Bla&lt;/strong&gt;',
                '<strong>Bla</strong>',
            ],
            'EscapeChildrenEnabledAndEscapeOutputDisabled: Tag syntax with argument, does not encode variable value' => [
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled content="{settings.test}" />',
                '<strong>Bla</strong>',
                '&lt;strong&gt;Bla&lt;/strong&gt;',
            ],
            'EscapeChildrenEnabledAndEscapeOutputDisabled: Inline syntax with argument, does not encode variable value' => [
                '{test:escapeChildrenEnabledAndEscapeOutputDisabled(content: settings.test)}',
                '<strong>Bla</strong>',
                '&lt;strong&gt;Bla&lt;/strong&gt;',
            ],
            'EscapeChildrenEnabledAndEscapeOutputDisabled: Inline syntax with string, does not encode string value' => [
                '{test:escapeChildrenEnabledAndEscapeOutputDisabled(content: \'<strong>Bla</strong>\')}',
                '<strong>Bla</strong>',
                '&lt;strong&gt;Bla&lt;/strong&gt;',
            ],
            'EscapeChildrenEnabledAndEscapeOutputDisabled: Inline syntax with argument in quotes, does not encode variable value' => [
                '{test:escapeChildrenEnabledAndEscapeOutputDisabled(content: \'{settings.test}\')}',
                '<strong>Bla</strong>',
                '&lt;strong&gt;Bla&lt;/strong&gt;',
            ],
            'EscapeChildrenEnabledAndEscapeOutputDisabled: Tag syntax with nested inline syntax and children rendering, does not encode variable value' => [
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled content="{settings.test -> test:escapeChildrenEnabledAndEscapeOutputDisabled()}" />',
                '<strong>Bla</strong>',
                '&lt;strong&gt;Bla&lt;/strong&gt;',
            ],
            'EscapeChildrenEnabledAndEscapeOutputDisabled: Tag syntax with nested inline syntax and argument in inline, does not encode variable value' => [
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled content="{test:escapeChildrenEnabledAndEscapeOutputDisabled(content: settings.test)}" />',
                '<strong>Bla</strong>',
                '&lt;strong&gt;Bla&lt;/strong&gt;',
            ],
            'EscapeChildrenDisabledAndEscapeOutputDisabled: Tag syntax with children, properly encodes variable value' => [
                '<test:escapeChildrenDisabledAndEscapeOutputDisabled>{settings.test}</test:escapeChildrenDisabledAndEscapeOutputDisabled>',
                '<strong>Bla</strong>',
                '&lt;strong&gt;Bla&lt;/strong&gt;',
            ],
            'EscapeChildrenDisabledAndEscapeOutputDisabled: Inline syntax with children, properly encodes variable value' => [
                '{settings.test -> test:escapeChildrenDisabledAndEscapeOutputDisabled()}',
                '<strong>Bla</strong>',
                '&lt;strong&gt;Bla&lt;/strong&gt;',
            ],
            'EscapeChildrenDisabledAndEscapeOutputDisabled: Tag syntax with argument, does not encode variable value' => [
                '<test:escapeChildrenDisabledAndEscapeOutputDisabled content="{settings.test}" />',
                '<strong>Bla</strong>',
                '&lt;strong&gt;Bla&lt;/strong&gt;',
            ],
            'EscapeChildrenDisabledAndEscapeOutputDisabled: Inline syntax with argument, does not encode variable value' => [
                '{test:escapeChildrenDisabledAndEscapeOutputDisabled(content: settings.test)}',
                '<strong>Bla</strong>',
                '&lt;strong&gt;Bla&lt;/strong&gt;',
            ],
            'EscapeChildrenDisabledAndEscapeOutputDisabled: Inline syntax with string, does not encode string value' => [
                '{test:escapeChildrenDisabledAndEscapeOutputDisabled(content: \'<strong>Bla</strong>\')}',
                '<strong>Bla</strong>',
                '&lt;strong&gt;Bla&lt;/strong&gt;',
            ],
            'EscapeChildrenDisabledAndEscapeOutputDisabled: Inline syntax with argument in quotes, does not encode variable value' => [
                '{test:escapeChildrenDisabledAndEscapeOutputDisabled(content: \'{settings.test}\')}',
                '<strong>Bla</strong>',
                '&lt;strong&gt;Bla&lt;/strong&gt;',
            ],
            'EscapeChildrenDisabledAndEscapeOutputDisabled: Tag syntax with nested inline syntax and children rendering, does not encode variable value' => [
                '<test:escapeChildrenDisabledAndEscapeOutputDisabled content="{settings.test -> test:escapeChildrenDisabledAndEscapeOutputDisabled()}" />',
                '<strong>Bla</strong>',
                '&lt;strong&gt;Bla&lt;/strong&gt;',
            ],
            'EscapeChildrenDisabledAndEscapeOutputDisabled: Tag syntax with nested inline syntax and argument in inline, does not encode variable value' => [
                '<test:escapeChildrenDisabledAndEscapeOutputDisabled content="{test:escapeChildrenDisabledAndEscapeOutputDisabled(content: settings.test)}" />',
                '<strong>Bla</strong>',
                '&lt;strong&gt;Bla&lt;/strong&gt;',
            ],
        ];
    }

    #[DataProvider('getTemplateCodeFixturesAndExpectations')]
    #[Test]
    public function testTemplateCodeFixture(string $source, string $expected, string $notExpected): void
    {
        $variables = ['settings' => ['test' => '<strong>Bla</strong>']];

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $view->assignMultiple($variables);
        $output = $view->render();
        self::assertStringContainsString($expected, $output);
        self::assertNotEquals($notExpected, $output);

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $view->assignMultiple($variables);
        $output = $view->render();
        self::assertStringContainsString($expected, $output);
        self::assertNotEquals($notExpected, $output);
    }

    #[Test]
    public function disablingEscapingTwiceInTemplateThrowsParsingException(): void
    {
        $this->expectException(Exception::class);
        $source = '{escapingEnabled=false}<test:escapeChildrenDisabledAndEscapeOutputEnabled>{settings.test}</test:escapeChildrenDisabledAndEscapeOutputEnabled>{escapingEnabled=false}';

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $view->render();

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $view->render();
    }
}
