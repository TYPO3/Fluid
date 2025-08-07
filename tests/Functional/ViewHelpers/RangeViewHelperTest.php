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

final class RangeViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderValidDataProvider(): iterable
    {
        yield 'single element' => [
            'arguments' => [
                'start' => 1,
                'end' => 1,
            ],
            'src' => '<f:range start="{start}" end="{end}" />',
            'expectation' => [1],
        ];
        yield 'single element inline' => [
            'arguments' => [
                'start' => 1,
                'end' => 1,
            ],
            'src' => '{f:range(start: start, end: end)}',
            'expectation' => [1],
        ];
        yield 'multiple elements' => [
            'arguments' => [
                'start' => 1,
                'end' => 5,
            ],
            'src' => '<f:range start="{start}" end="{end}" />',
            'expectation' => [1, 2, 3, 4, 5],
        ];
        yield 'multiple elements inline' => [
            'arguments' => [
                'start' => 1,
                'end' => 5,
            ],
            'src' => '{f:range(start: start, end: end)}',
            'expectation' => [1, 2, 3, 4, 5],
        ];
        yield 'step' => [
            'arguments' => [
                'start' => 1,
                'end' => 10,
                'step' => 2,
            ],
            'src' => '<f:range start="{start}" end="{end}" step="{step}" />',
            'expectation' => [1, 3, 5, 7, 9],
        ];
        yield 'step inline' => [
            'arguments' => [
                'start' => 1,
                'end' => 10,
                'step' => 2,
            ],
            'src' => '{f:range(start: start, end: end, step: step)}',
            'expectation' => [1, 3, 5, 7, 9],
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
        yield 'invalid step' => [
            'arguments' => [
                'start' => 1,
                'end' => 1,
                'step' => 0,
            ],
            'src' => '<f:range start="{start}" end="{end}" step="{step}" />',
        ];
        yield 'invalid step inline' => [
            'arguments' => [
                'start' => 1,
                'end' => 1,
                'step' => 0,
            ],
            'src' => '{f:range(start: start, end: end, step: step)}',
        ];
    }

    #[DataProvider('renderInvalidDataProvider')]
    #[Test]
    public function renderInvalid(array $arguments, string $src): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1754596304);

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
