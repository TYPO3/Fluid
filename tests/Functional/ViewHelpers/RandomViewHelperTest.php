<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3\CMS\Fluid\Tests\Functional\ViewHelpers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\ArrayAccessExample;
use TYPO3Fluid\Fluid\View\TemplateView;

final class RandomViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderValidDataProvider(): iterable
    {
        yield 'empty value' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '<f:random value="{value}" />',
            'expectation' => [],
        ];
        yield 'empty value inline' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '{value -> f:random()}',
            'expectation' => [],
        ];
        yield 'single item' => [
            'arguments' => [
                'value' => [1],
            ],
            'src' => '<f:random value="{value}" />',
            'expectation' => 1,
        ];
    }

    #[DataProvider('renderValidDataProvider')]
    #[Test]
    public function renderValid(array $arguments, string $src, mixed $expectation): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($src);
        $view->assignMultiple($arguments);
        self::assertSame($expectation, $view->render());

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($src);
        $view->assignMultiple($arguments);
        self::assertSame($expectation, $view->render());
    }

    public static function renderValidArrayDataProvider(): iterable
    {
        yield 'multiple items' => [
            'arguments' => [
                'value' => [0, 8, 15, 47, 11],
            ],
            'src' => '<f:random value="{value}" />',
            'expectation' => [0, 8, 15, 47, 11],
        ];
        yield 'multiple items inline' => [
            'arguments' => [
                'value' => [0, 8, 15, 47, 11],
            ],
            'src' => '{f:random(value: value)}',
            'expectation' => [0, 8, 15, 47, 11],
        ];
    }

    #[DataProvider('renderValidArrayDataProvider')]
    #[Test]
    public function renderValidWithArray(array $arguments, string $src, mixed $expectation): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($src);
        $view->assignMultiple($arguments);
        self::assertContains($view->render(), $expectation);

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($src);
        $view->assignMultiple($arguments);
        self::assertContains($view->render(), $expectation);
    }

    public static function renderInvalidDataProvider(): iterable
    {
        yield 'invalid string content' => [
            'arguments' => [
            ],
            'src' => '<f:random>SOME TEXT</f:random>',
        ];

        yield 'invalid string inline' => [
            'arguments' => [
                'value' => 'string',
            ],
            'src' => '{value -> f:random()}',
        ];
        yield 'arrayaccess inline' => [
            'arguments' => [
                'value' => new ArrayAccessExample(['foo' => 'bar']),
            ],
            'src' => '{value -> f:random()}',
        ];
    }

    #[DataProvider('renderInvalidDataProvider')]
    #[Test]
    public function renderInvalid(array $arguments, string $src): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1756181371);

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($src);
        $view->assignMultiple($arguments);
        $view->render();

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($src);
        $view->assignMultiple($arguments);
        $view->render();
    }
}
