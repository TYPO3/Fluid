<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class UrlencodeViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderDataProvider(): array
    {
        return [
            'renderUsesValueAsSourceIfSpecified' => [
                '<f:format.urlencode value="Source" />',
                'Source',
            ],
            'renderUsesChildnodesAsSourceIfSpecified' => [
                '<f:format.urlencode>Source</f:format.urlencode>',
                'Source',
            ],
            'renderDoesNotModifyValueIfItDoesNotContainSpecialCharacters' => [
                '<f:format.urlencode>StringWithoutSpecialCharacters</f:format.urlencode>',
                'StringWithoutSpecialCharacters',
            ],
            'renderEncodesString' => [
                '<f:format.urlencode>Foo @+%/ "</f:format.urlencode>',
                'Foo%20%40%2B%25%2F%20%22',
            ],
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

    /**
     * Ensures that objects are handled properly:
     * + class having __toString() method gets tags stripped off
     *
     * @test
     */
    public function renderEscapesObjectIfPossible(): void
    {
        $toStringClass = new class () {
            public function __toString(): string
            {
                return '<script>alert(\'"xss"\')</script>';
            }
        };
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('<f:format.urlencode>{value}</f:format.urlencode>');
        $view->assign('value', $toStringClass);
        self::assertEquals('%3Cscript%3Ealert%28%27%22xss%22%27%29%3C%2Fscript%3E', $view->render());
    }
}
