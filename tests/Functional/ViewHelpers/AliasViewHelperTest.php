<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class AliasViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderDataProvider(): \Generator
    {
        yield 'single alias is defined' => [
            '<f:alias map="{x: \'foo\'}">{x}</f:alias>',
            [],
            'foo',
        ];

        yield 'multiple aliases are defined' => [
            '<f:alias map="{x: \'foo\', y: \'bar\'}">{y} {x}</f:alias>',
            [],
            'bar foo',
        ];

        yield 'wrapped content is output even if map is empty' => [
            '<f:alias map="{emptyMap}">wrapped content</f:alias>',
            ['emptyMap' => []],
            'wrapped content',
        ];

        yield 'defined alias does not exist anymore outside tag' => [
            '<f:alias map="{x: \'foo\'}"></f:alias>{x}',
            [],
            '',
        ];

        yield 'variables are restored correctly' => [
            '<f:alias map="{x: \'foo\'}"></f:alias>{x}',
            ['x' => 'bar'],
            'bar',
        ];

        yield 'variables are restored correctly if overwritten in alias' => [
            '<f:alias map="{x: \'foo\'}"><f:variable name="x" value="foo2" /></f:alias>{x}',
            ['x' => 'bar'],
            'foo2',
        ];

        yield 'variables set inside alias can be used afterwards' => [
            '<f:alias map="{x: \'foo\'}"><f:variable name="foo" value="bar" /></f:alias>{foo}',
            [],
            'bar',
        ];

        yield 'existing variables can be modified in alias and retain the value set in the alias' => [
            '<f:alias map="{x: \'foo\'}"><f:variable name="foo" value="bar" /></f:alias>{foo}',
            ['foo' => 'fallback'],
            'bar',
        ];
    }

    #[DataProvider('renderDataProvider')]
    #[Test]
    public function render(string $template, array $variables, string $expected): void
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
