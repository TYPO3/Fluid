<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers;

use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

class LayoutViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderDataProvider(): \Generator
    {
        yield 'template with named layout' => [
            '<f:layout name="Layout" /><f:section name="main">Content of main section</f:section>',
            'DefaultLayoutContent of main section',
        ];
        yield 'fallback layout' => [
            '<f:layout />',
            'DefaultLayout with name Default.html',
        ];
    }

    /**
     * @test
     * @dataProvider renderDataProvider
     */
    public function render(string $template, string $expected): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setLayoutRootPaths([__DIR__ . '/../Fixtures/Layouts/']);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        self::assertSame($expected, trim($view->render()));

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setLayoutRootPaths([__DIR__ . '/../Fixtures/Layouts/']);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        self::assertSame($expected, trim($view->render()));
    }
}
