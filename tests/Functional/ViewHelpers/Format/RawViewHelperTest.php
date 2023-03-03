<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\ViewHelpers\Format\RawViewHelper;

class RawViewHelperTest extends AbstractFunctionalTestCase
{

    /**
     * @test
     */
    public function viewHelperDeactivatesEscapingInterceptor()
    {
        self::assertFalse((new RawViewHelper())->isOutputEscapingEnabled());
    }

    public static function renderDataProvider(): \Generator
    {
        yield 'value as argument' => [
            '<f:format.raw value="input value \" & äöüß@" />',
            'input value " & äöüß@',
        ];
        yield 'value as tag content' => [
            '<f:format.raw>input value " & äöüß@</f:format.raw>',
            'input value " & äöüß@',
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
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        self::assertSame($expected, $view->render());

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        self::assertSame($expected, $view->render());
    }
}
