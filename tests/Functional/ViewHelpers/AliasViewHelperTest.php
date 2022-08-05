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
        yield 'adds and removes single alias' => [
            '<f:alias map="{x: \'foo\'}">{x}</f:alias>{x}',
            [],
            'foo',
        ];

        yield 'adds and removes multiple aliases' => [
            '<f:alias map="{x: \'foo\', y: \'bar\'}">{x} {y}</f:alias>{x} {y}',
            [],
            'foo bar '
        ];

        yield 'outputs wrapped content even if map is empty' => [
            '<f:alias map="{emptyMap}">wrapped content</f:alias>',
            ['emptyMap' => []],
            'wrapped content'
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
