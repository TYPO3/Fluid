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

final class CountViewHelperTest extends AbstractFunctionalTestCase
{
    #[Test]
    public function renderThrowsExceptionIfSubjectIsNotCountable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $value = new \stdClass();
        $view = new TemplateView();
        $view->assignMultiple(['value' => $value]);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('<f:count subject="{value}" />');
        $view->render();
    }

    public static function renderDataProvider(): \Generator
    {
        yield 'value as argument' => [
            '<f:count subject="{0:foo, 1:bar, 2:baz}" />',
            [],
            3,
        ];
        $value = new \ArrayObject(['foo', 'bar']);
        yield 'value is object' => [
            '<f:count subject="{value}" />',
            ['value' => $value],
            2,
        ];
        $value = [];
        yield 'zero for empty array' => [
            '<f:count subject="{value}" />',
            ['value' => $value],
            0,
        ];
        $value = ['foo', 'baz', 'bar'];
        yield 'value as tag content' => [
            '<f:count>{value}</f:count>',
            ['value' => $value],
            3,
        ];
        $value = [];
        yield 'empty value as tag content' => [
            '<f:count>{value}</f:count>',
            ['value' => $value],
            0,
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
