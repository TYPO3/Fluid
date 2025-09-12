<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers\Enum;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\ViewHelper;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\ArrayAccessExample;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\BackedEnumExample;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\BackedEnumIntExample;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\EnumExample;
use TYPO3Fluid\Fluid\View\TemplateView;

final class TryFromViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderDataProvider(): \Generator
    {
        yield 'backed enum with int and passing string as value and available case' => [
            '<f:enum.tryFrom enum="\TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\BackedEnumIntExample" value="42"/>',
            [],
            BackedEnumIntExample::BAR,
        ];

        yield 'backed enum with int and passing string as value and unavailable case' => [
            '<f:enum.tryFrom enum="\TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\BackedEnumIntExample" value="43"/>',
            [],
            null,
        ];

        yield 'backed enum with int and passing int as value and available case' => [
            '<f:enum.tryFrom enum="\TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\BackedEnumIntExample" value="{value}"/>',
            ['value' => 42],
            BackedEnumIntExample::BAR,
        ];

        yield 'backed enum with int and passing int as value and unavailable case' => [
            '<f:enum.tryFrom enum="\TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\BackedEnumIntExample" value="{value}"/>',
            ['value' => 43],
            null,
        ];

        yield 'backed enum with string and available case' => [
            '<f:enum.tryFrom enum="\TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\BackedEnumExample" value="bar"/>',
            [],
            BackedEnumExample::BAR,
        ];

        yield 'backed enum with string and unavailable case' => [
            '<f:enum.tryFrom enum="\TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\BackedEnumExample" value="foo"/>',
            [],
            null,
        ];
    }

    #[DataProvider('renderDataProvider')]
    #[Test]
    public function render(string $template, array $variables, ?\BackedEnum $expected): void
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

    public static function exceptionIsThrownOnInvalidArgumentsDataProvider(): \Generator
    {
        yield 'enum does not exist' => [
            [
                'enum' => '\Non\Existing\Enum',
                'value' => 'foo',
            ],
            1757668148,
        ];

        yield 'enum is a class' => [
            [
                'enum' => ArrayAccessExample::class,
                'value' => 'foo',
            ],
            1757668148,
        ];

        yield 'enum is not a backed enum' => [
            [
                'enum' => EnumExample::class,
                'value' => 'foo',
            ],
            1757668149,
        ];

        yield 'enum is backed but passed an array as value' => [
            [
                'enum' => BackedEnumExample::class,
                'value' => [],
            ],
            1757668151,
        ];

        yield 'enum is backed but passed an object as value' => [
            [
                'enum' => BackedEnumExample::class,
                'value' => new \stdClass(),
            ],
            1757668151,
        ];

        yield 'enum is backed but passed a bool as value' => [
            [
                'enum' => BackedEnumExample::class,
                'value' => true,
            ],
            1757668151,
        ];

        yield 'enum is backed but passed a float as value' => [
            [
                'enum' => BackedEnumExample::class,
                'value' => 42.42,
            ],
            1757668151,
        ];
    }

    #[DataProvider('exceptionIsThrownOnInvalidArgumentsDataProvider')]
    #[Test]
    public function exceptionIsThrownOnInvalidArguments(array $variables, int $expectedCode): void
    {
        $this->expectException(ViewHelper\Exception::class);
        $this->expectExceptionCode($expectedCode);

        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource(
            '<f:enum.tryFrom enum="{enum}" value="{value}"/>',
        );
        $view->render();
    }
}
