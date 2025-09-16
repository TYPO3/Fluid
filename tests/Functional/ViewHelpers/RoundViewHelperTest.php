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

final class RoundViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderDataProvider(): \Generator
    {
        yield 'with default precision' => [
            '<f:round value="123.456" />',
            123.46,
        ];
        yield 'with custom precision' => [
            '<f:round value="123.456" precision="1" />',
            123.5,
        ];
        yield 'with zero precision' => [
            '<f:round value="123.456" precision="0" />',
            123,
        ];
        yield 'with float as tag content' => [
            '<f:round precision="1">5.66</f:round>',
            5.7,
        ];
        yield 'with integer as tag content' => [
            '<f:round precision="2">5</f:round>',
            5.0,
        ];
        yield 'with custom precision and rounding mode HalfEven' => [
            '<f:round value="9.5" precision="0" roundingMode="HalfEven" />',
            10.0,
        ];
        yield 'with custom precision and rounding mode HalfOdd' => [
            '<f:round value="9.5" precision="0" roundingMode="HalfOdd" />',
            9.0,
        ];
        yield 'with float as tag content and rounding mode' => [
            '<f:round precision="1" roundingMode="HalfAwayFromZero">5.66</f:round>',
            5.7,
        ];
    }

    #[Test]
    #[DataProvider('renderDataProvider')]
    public function render(string $template, float $expected): void
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
