<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class JsonViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderDataProvider(): \Generator
    {
        yield 'value as argument' => [
            '<f:format.json value="{value}"/>',
            ['value' => ['abc' => 'def']],
            '{&quot;abc&quot;:&quot;def&quot;}',
        ];
        yield 'value as child' => [
            '<f:format.json>{value}</f:format.json>',
            ['value' => ['abc' => 'def']],
            '{&quot;abc&quot;:&quot;def&quot;}',
        ];
        yield 'value as child and argument' => [
            '<f:format.json value="{argument}">{child}</f:format.json>',
            ['argument' => ['abc' => 'argument'], 'child' => ['abc' => 'child']],
            '{&quot;abc&quot;:&quot;argument&quot;}',
        ];
        yield 'force array as object' => [
            '<f:format.json forceObject="1">{value}</f:format.json>',
            ['value' => [1, 2]],
            '{&quot;0&quot;:1,&quot;1&quot;:2}',
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
