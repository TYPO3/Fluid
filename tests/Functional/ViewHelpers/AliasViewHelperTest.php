<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers;

use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

class AliasViewHelperTest extends AbstractFunctionalTestCase
{
    public function renderDataProvider(): \Generator
    {
        yield 'single alias is defined' => [
            '<f:alias map="{x: \'foo\'}">{x}</f:alias>',
            [],
            'foo',
        ];

        yield 'multiple aliases are defined' => [
            '<f:alias map="{x: \'foo\', y: \'bar\'}">{y} {x}</f:alias>',
            [],
            'bar foo'
        ];

        yield 'wrapped content is output even if map is empty' => [
            '<f:alias map="{emptyMap}">wrapped content</f:alias>',
            ['emptyMap' => []],
            'wrapped content'
        ];

        yield 'defined alias does not exist anymore outside tag' => [
            '<f:alias map="{x: \'foo\'}"></f:alias>{x}',
            [],
            '',
        ];
    }

    /**
     * @test
     * @dataProvider renderDataProvider
     */
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
