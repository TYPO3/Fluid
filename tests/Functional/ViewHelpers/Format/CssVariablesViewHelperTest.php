<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers\Format;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\IterableExample;
use TYPO3Fluid\Fluid\View\TemplateView;

final class CssVariablesViewHelperTest extends AbstractFunctionalTestCase
{
    private static array $value = [
        'red' => '#ff0000',
        'green' => 'rgb(0, 255, 0)',
        'nested' => [
            'white' => '#ffffff',
            'variable' => 'var(--my-variable)',
        ],
        'non-processable-item' => null,
    ];

    public static function renderDataProvider(): \Generator
    {
        yield 'value as argument' => [
            '<f:format.cssVariables value="{value}"/>',
            ['value' => self::$value],
            '--red: #ff0000; --green: rgb(0, 255, 0); --nested-white: #ffffff; --nested-variable: var(--my-variable);',
        ];
        yield 'value inline' => [
            '{value -> f:format.cssVariables()}',
            ['value' => self::$value],
            '--red: #ff0000; --green: rgb(0, 255, 0); --nested-white: #ffffff; --nested-variable: var(--my-variable);',
        ];
        yield 'value as child' => [
            '<f:format.cssVariables>{value}</f:format.cssVariables>',
            ['value' => self::$value],
            '--red: #ff0000; --green: rgb(0, 255, 0); --nested-white: #ffffff; --nested-variable: var(--my-variable);',
        ];
        yield 'value as child and argument' => [
            '<f:format.cssVariables value="{argument}">{child}</f:format.cssVariables>',
            ['argument' => ['abc' => 'argument'], 'child' => ['abc' => 'child']],
            '--abc: argument;',
        ];
        yield 'with prefix' => [
            '<f:format.cssVariables value="{value}" prefix="color"/>',
            ['value' => self::$value],
            '--color-red: #ff0000; --color-green: rgb(0, 255, 0); --color-nested-white: #ffffff; --color-nested-variable: var(--my-variable);',
        ];
        yield 'with selector' => [
            '<f:format.cssVariables value="{value}" selector=".my-css-class"/>',
            ['value' => self::$value],
            '.my-css-class {
--red: #ff0000;
--green: rgb(0, 255, 0);
--nested-white: #ffffff;
--nested-variable: var(--my-variable);
}',
        ];
        yield 'with prefix and selector' => [
            '<f:format.cssVariables value="{value}" prefix="color" selector=".my-css-class, #my-id"/>',
            ['value' => self::$value],
            '.my-css-class, #my-id {
--color-red: #ff0000;
--color-green: rgb(0, 255, 0);
--color-nested-white: #ffffff;
--color-nested-variable: var(--my-variable);
}',
        ];
        yield 'numeric array as value' => [
            '<f:format.cssVariables value="{value}" selector=":root"/>',
            ['value' => ['foo', null, 'bar']],
            ':root {
--0: foo;
--2: bar;
}',
        ];
        yield 'object as value' => [
            '<f:format.cssVariables value="{value}" selector=":root"/>',
            ['value' => new IterableExample(self::$value)],
            ':root {
--red: #ff0000;
--green: rgb(0, 255, 0);
--nested-white: #ffffff;
--nested-variable: var(--my-variable);
}',
        ];
        yield 'non-iterable as value' => [
            '<f:format.cssVariables value="{value}"/>',
            ['value' => 'some-string'],
            '',
        ];
    }

    #[DataProvider('renderDataProvider')]
    #[Test]
    public function render(string $template, array $variables, $expected): void
    {
        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        self::assertSame($expected, $view->render());

        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        self::assertSame($expected, $view->render());
    }
}
