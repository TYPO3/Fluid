<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers;

use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

class SpacelessViewHelperTest extends AbstractFunctionalTestCase
{
    public function renderDataProvider(): \Generator
    {
        yield 'extra whitespace between tags' => [
            '<f:spaceless><div>foo</div>  <div>bar</div></f:spaceless>',
            '<div>foo</div><div>bar</div>',
        ];
        yield 'whitespace preserved in text node' => [
            '<f:spaceless>' . PHP_EOL . '<div>' . PHP_EOL . 'foo</div></f:spaceless>',
            '<div>' . PHP_EOL . 'foo</div>',
        ];
        yield 'whitespace removed from non-text node' => [
            '<f:spaceless>' . PHP_EOL . '<div>' . PHP_EOL . '<div>foo</div></div></f:spaceless>',
            '<div><div>foo</div></div>',
        ];
    }

    /**
     * @test
     * @dataProvider renderDataProvider
     */
    public function render(string $template, $expected): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        self::assertSame($expected, $view->render());

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        self::assertSame($expected, $view->render());
    }
}
