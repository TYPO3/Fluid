<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers;

use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

class IfThenElseViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderDataProvider(): \Generator
    {
        yield 'no condition' => [
            '<f:if then="foo" else="bar" />',
            [],
            'bar',
        ];
        yield 'condition is true' => [
            '<f:if condition="{verdict}" then="foo" />',
            ['verdict' => true],
            'foo',
        ];
        yield 'condition is false' => [
            '<f:if condition="{verdict}" then="foo" />',
            ['verdict' => false],
            null,
        ];
        yield 'condition is true and else exists' => [
            '<f:if condition="{verdict}" then="foo" else="bar" />',
            ['verdict' => true],
            'foo',
        ];
        yield 'condition is false and else exists' => [
            '<f:if condition="{verdict}" then="foo" else="bar" />',
            ['verdict' => false],
            'bar',
        ];
        yield 'condition is true and only else exists' => [
            '<f:if condition="{verdict}" else="bar" />',
            ['verdict' => true],
            null,
        ];
        yield 'condition is false and only else exists' => [
            '<f:if condition="{verdict}" else="bar" />',
            ['verdict' => false],
            'bar',
        ];
        yield 'without then and else and condition is true' => [
            '<f:if condition="{verdict}">foo</f:if>',
            ['verdict' => true],
            'foo',
        ];
        yield 'without then and else and condition is false' => [
            '<f:if condition="{verdict}">foo</f:if>',
            ['verdict' => false],
            null,
        ];
        yield 'with then viewhelper' => [
            '<f:if condition="{verdict}">foo<f:then>bar</f:then></f:if>',
            ['verdict' => true],
            'bar',
        ];
        yield 'with else viewhelper' => [
            '<f:if condition="{verdict}">foo<f:else>bar</f:else></f:if>',
            ['verdict' => false],
            'bar',
        ];
        yield 'with then and else viewhelper and condition is true' => [
            '<f:if condition="{verdict}"><f:then>foo</f:then><f:else>bar</f:else></f:if>',
            ['verdict' => true],
            'foo',
        ];
        yield 'with then and else viewhelper and condition is false' => [
            '<f:if condition="{verdict}"><f:then>foo</f:then><f:else>bar</f:else></f:if>',
            ['verdict' => false],
            'bar',
        ];
        yield 'else if with if verdict true' => [
            '<f:if condition="{verdict}">' .
                '<f:then>foo</f:then>' .
                '<f:else if="{verdictElseIf}">bar</f:else>' .
                '<f:else>baz</f:else>' .
            '</f:if>',
            ['verdict' => true, 'verdictElseIf' => true],
            'foo',
        ];
        yield 'else if with elseif verdict true' => [
            '<f:if condition="{verdict}">' .
                '<f:then>foo</f:then>' .
                '<f:else if="{verdictElseIf}">bar</f:else>' .
                '<f:else>baz</f:else>' .
            '</f:if>',
            ['verdict' => false, 'verdictElseIf' => true],
            'bar',
        ];
        yield 'else if with elseif verdict false falls back to else' => [
            '<f:if condition="{verdict}">' .
                '<f:then>foo</f:then>' .
                '<f:else if="{verdictElseIf}">bar</f:else>' .
                '<f:else>baz</f:else>' .
            '</f:if>',
            ['verdict' => false, 'verdictElseIf' => false],
            'baz',
        ];
    }

    /**
     * @test
     * @dataProvider renderDataProvider
     */
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
