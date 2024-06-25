<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers\Format;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class NumberViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderDataProvider(): array
    {
        return [
            'formatNumberDefaultsToEnglishNotationWithTwoDecimals' => [
                '<f:format.number>3.1415926535898</f:format.number>',
                '3.14',
            ],
            'formatNumberWithDecimalPoint' => [
                '<f:format.number decimalSeparator=",">3.1415926535898</f:format.number>',
                '3,14',
            ],
            'formatNumberWithDecimals' => [
                '<f:format.number decimals="4">3.1415926535898</f:format.number>',
                '3.1416',
            ],
            'formatNumberWithThousandsSeparator' => [
                '<f:format.number thousandsSeparator=",">3141.5926535898</f:format.number>',
                '3,141.59',
            ],
            'formatNumberWithEmptyInput' => [
                '<f:format.number></f:format.number>',
                '0.00',
            ],
        ];
    }

    #[DataProvider('renderDataProvider')]
    #[Test]
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
