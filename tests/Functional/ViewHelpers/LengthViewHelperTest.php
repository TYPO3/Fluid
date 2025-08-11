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
use TYPO3Fluid\Fluid\View\TemplateView;

final class LengthViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderValidDataProvider(): iterable
    {
        yield 'single element' => [
            'arguments' => [
                'value' => 'Hello, World!',
            ],
            'src' => '<f:length value="{value}" />',
            'expectation' => 13,
        ];
        yield 'single element, scalar type' => [
            'arguments' => [
                'value' => 13,
            ],
            'src' => '<f:length value="{value}" />',
            'expectation' => 2,
        ];
        yield 'single element, with encoding' => [
            'arguments' => [
                'value' => 'Hello, World!',
                'encoding' => 'UTF-8',
            ],
            'src' => '<f:length value="{value}" encoding="{encoding}" />',
            'expectation' => 13,
        ];
        yield 'single element inline' => [
            'arguments' => [
                'value' => 'Hello, World!',
            ],
            'src' => '{f:length(value: value)}',
            'expectation' => 13,
        ];
        yield 'single element inline, pipe variable' => [
            'arguments' => [
                'value' => 'Hello, World!',
            ],
            'src' => '{value -> f:length()}',
            'expectation' => 13,
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

    public static function renderInvalidDataProvider(): iterable
    {
        $noneScalarType = new \stdClass();

        yield 'invalid value' => [
            'arguments' => [
                'value' => $noneScalarType,
            ],
            'src' => '<f:length value="{value}" />',
        ];
        yield 'invalid value inline' => [
            'arguments' => [
                'value' => $noneScalarType,
            ],
            'src' => '{f:length(value: value)}',
        ];
        yield 'invalid value inline, pipe variable' => [
            'arguments' => [
                'value' => $noneScalarType,
            ],
            'src' => '{value -> f:length()}',
        ];
    }

    #[DataProvider('renderInvalidDataProvider')]
    #[Test]
    public function renderInvalid(array $arguments, string $src): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1754637887);

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
