<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers\Format;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class StripTagsViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderDataProvider(): array
    {
        return [
            'renderUsesValueAsSourceIfSpecified' => [
                '<f:format.stripTags value="Some string" />',
                'Some string',
            ],
            'int as input' => [
                '<f:format.stripTags value="123" />',
                '123',
            ],
            'renderUsesChildnodesAsSourceIfSpecified' => [
                '<f:format.stripTags>Some string</f:format.stripTags>',
                'Some string',
            ],
            'no special chars' => [
                '<f:format.stripTags>This is a sample text without special characters.</f:format.stripTags>',
                'This is a sample text without special characters.',
            ],
            'some tags' => [
                '<f:format.stripTags>This is a sample text <b>with <i>some</i> tags</b>.</f:format.stripTags>',
                'This is a sample text with some tags.',
            ],
            'some umlauts' => [
                '<f:format.stripTags>This text contains some &quot;&Uuml;mlaut&quot;.</f:format.stripTags>',
                'This text contains some &quot;&Uuml;mlaut&quot;.',
            ],
            'allowed tags' => [
                '<f:format.stripTags allowedTags="<strong>">This text <i>contains</i> some <strong>allowed</strong> tags.</f:format.stripTags>',
                'This text contains some <strong>allowed</strong> tags.',
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

    /**
     * Ensures that objects are handled properly:
     * + class having __toString() method gets tags stripped off
     */
    #[Test]
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
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('<f:format.stripTags>{value}</f:format.stripTags>');
        $view->assign('value', $toStringClass);
        self::assertEquals('alert(\'"xss"\')', $view->render());
    }

    public static function throwsExceptionForInvalidInputDataProvider(): array
    {
        return [
            'array input' => [
                [1, 2, 3],
                1700819707,
                'Specified array cannot be converted to string.',
            ],
            'object input' => [
                new stdClass(),
                1700819706,
                'Specified object cannot be converted to string.',
            ],
        ];
    }

    #[DataProvider('throwsExceptionForInvalidInputDataProvider')]
    #[Test]
    public function throwsExceptionForInvalidInput(mixed $value, int $expectedExceptionCode, string $expectedExceptionMessage): void
    {
        self::expectExceptionCode($expectedExceptionCode);
        self::expectExceptionMessage($expectedExceptionMessage);
        $view = new TemplateView();
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('<f:format.stripTags>{value}</f:format.stripTags>');
        $view->assign('value', $value);
        $view->render();
    }
}
