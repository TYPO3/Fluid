<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers;

use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

class DebugViewHelperTest extends AbstractFunctionalTestCase
{
    public function renderDataProvider(): \Generator
    {
        yield 'string' => [
            '<f:debug>{value}</f:debug>',
            ['value' => 'test'],
            "string 'test'" . PHP_EOL,
        ];
        yield 'type only' => [
            '<f:debug typeOnly="1">{value}</f:debug>',
            ['value' => 'test'],
            'string'
        ];
        yield 'nested' => [
            '<f:debug>{value}</f:debug>',
            ['value' => ['nested' => 'test']],
            'array: ' . PHP_EOL . '  "nested": string \'test\'' . PHP_EOL
        ];
        yield 'escape html' => [
            '<f:debug html="1">{value}</f:debug>',
            ['value' => 'test<strong>bold</strong>'],
            '<code>string = \'test&lt;strong&gt;bold&lt;/strong&gt;\'</code>'
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
