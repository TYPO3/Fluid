<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers;

use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

class OrViewHelperTest extends AbstractFunctionalTestCase
{
    public function renderDataProvider(): \Generator
    {
        yield 'without arguments' => [
            '<f:or>{var}</f:or>',
            ['var' => 'foo'],
            'foo',
        ];
        yield 'with content argument and non-empty content' => [
            '<f:or content="{var}" />',
            ['var' => 'foo'],
            'foo',
        ];
        yield 'with content argument and empty content' => [
            '<f:or content="{var}" />',
            ['var' => null],
            null, // @TODO this should probably be an empty string?
        ];
        yield 'with alternative' => [
            '<f:or content="{var}" alternative="alt" />',
            ['var' => null],
            'alt',
        ];
        yield 'with arguments and non-empty content' => [
            '<f:or content="{var}" alternative="alt" arguments="{0: \'bar\', 1: 42}" />',
            ['var' => 'foo %1$s %2$d'],
            'foo bar 42',
        ];
        yield 'with arguments and empty content' => [
            '<f:or content="{var}" alternative="alt %1$s %2$d" arguments="{0: \'bar\', 1: 42}" />',
            ['var' => null],
            'alt bar 42',
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
