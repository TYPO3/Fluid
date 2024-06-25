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

final class SplitViewHelperTest extends AbstractFunctionalTestCase
{
    #[Test]
    public function throwsExceptionForInvalidValue(): void
    {
        self::expectExceptionCode(1705250408);
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('<f:split separator="," />');
        $view->render();
    }

    public static function renderDataProvider(): \Generator
    {
        yield 'value as argument' => [
            '<f:split value="{value}" separator="," />',
            ['value' => 'foo,bar,4'],
            ['foo', 'bar', '4'],
        ];
        yield 'value as tag content' => [
            '<f:split separator=",">{value}</f:split>',
            ['value' => 'foo,bar,4'],
            ['foo', 'bar', '4'],
        ];
        yield 'limit items' => [
            '<f:split separator="." limit="2">{value}</f:split>',
            ['value' => 'foo.bar.baz'],
            ['foo', 'bar.baz'],
        ];
        yield 'limit matches result' => [
            '<f:split separator="," limit="3">{value}</f:split>',
            ['value' => 'foo,bar,baz'],
            ['foo', 'bar', 'baz'],
        ];
        yield 'limit exceeds result' => [
            '<f:split separator="," limit="10">{value}</f:split>',
            ['value' => 'foo,bar,baz'],
            ['foo', 'bar', 'baz'],
        ];
        yield 'limit zero' => [
            '<f:split separator="," limit="0">{value}</f:split>',
            ['value' => 'foo,bar,baz'],
            ['foo,bar,baz'],
        ];
        yield 'limit one' => [
            '<f:split separator="," limit="1">{value}</f:split>',
            ['value' => 'foo,bar,baz'],
            ['foo,bar,baz'],
        ];
        yield 'negative limit' => [
            '<f:split separator="," limit="-1">{value}</f:split>',
            ['value' => 'foo,bar,baz'],
            ['foo', 'bar'],
        ];
        yield 'exceeding negative limit' => [
            '<f:split separator="," limit="-5">{value}</f:split>',
            ['value' => 'foo,bar,baz'],
            [],
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
