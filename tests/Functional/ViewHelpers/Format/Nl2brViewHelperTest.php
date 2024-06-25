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

final class Nl2brViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderDataProvider(): array
    {
        return [
            'viewHelperDoesNotModifyTextWithoutLineBreaks' => [
                '<f:format.nl2br><p class="bodytext">Some Text without line breaks</p></f:format.nl2br>',
                '<p class="bodytext">Some Text without line breaks</p>',
            ],
            'viewHelperConvertsLineBreaksToBRTags' => [
                '<f:format.nl2br>' . 'Line 1' . chr(10) . 'Line 2' . '</f:format.nl2br>',
                'Line 1<br />' . chr(10) . 'Line 2',
            ],
            'viewHelperConvertsWindowsLineBreaksToBRTags' => [
                '<f:format.nl2br>' . 'Line 1' . chr(13) . chr(10) . 'Line 2' . '</f:format.nl2br>',
                'Line 1<br />' . chr(13) . chr(10) . 'Line 2',
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
