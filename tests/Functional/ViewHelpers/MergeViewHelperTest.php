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

final class MergeViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderValidDataProvider(): iterable
    {
        yield 'array merge with empty arrays' => [
            'arguments' => [
                'array' => [],
                'with' => [],
            ],
            'src' => '<f:merge array="{array}" with="{with}" />',
            'expectation' => [],
        ];
        yield 'array merge with mixed arrays' => [
            'arguments' => [
                'array' => ['color' => 'red', 2, 4],
                'with' => ['a', 'b', 'color' => 'green', 'shape' => 'trapezoid', 4],
            ],
            'src' => '<f:merge array="{array}" with="{with}" />',
            'expectation' => ['color' => 'green', 2, 4, 'a', 'b', 'shape' => 'trapezoid', 4],
        ];
        yield 'inline array merge with mixed arrays' => [
            'arguments' => [
                'array' => ['color' => 'red', 2, 4],
                'with' => ['a', 'b', 'color' => 'green', 'shape' => 'trapezoid', 4],
            ],
            'src' => '{array -> f:merge(with: with)}',
            'expectation' => ['color' => 'green', 2, 4, 'a', 'b', 'shape' => 'trapezoid', 4],
        ];

        yield 'array merge recursive with empty arrays' => [
            'arguments' => [
                'array' => [],
                'with' => [],
                'recursive' => true,
            ],
            'src' => '<f:merge array="{array}" with="{with}" recursive="{recursive}" />',
            'expectation' => [],
        ];
        yield 'array merge recursive with mixed arrays' => [
            'arguments' => [
                'array' => ['color' => ['favorite' => 'red'], 5],
                'with' => [10, 'color' => ['favorite' => 'green', 'blue']],
                'recursive' => true,
            ],
            'src' => '<f:merge array="{array}" with="{with}" recursive="{recursive}" />',
            'expectation' => ['color' => ['favorite' => ['red', 'green'], 'blue'], 5, 10],
        ];
        yield 'inline array merge recursive with mixed arrays' => [
            'arguments' => [
                'array' => ['color' => ['favorite' => 'red'], 5],
                'with' => [10, 'color' => ['favorite' => 'green', 'blue']],
                'recursive' => true,
            ],
            'src' => '{array -> f:merge(with: with, recursive: recursive)}',
            'expectation' => ['color' => ['favorite' => ['red', 'green'], 'blue'], 5, 10],
        ];
    }

    #[DataProvider('renderValidDataProvider')]
    #[Test]
    public function renderValid(array $arguments, string $src, array $expectation): void
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

        yield 'invalid array parameter' => [
            'arguments' => [
                'array' => $noneScalarType,
                'with' => [],
            ],
            'src' => '<f:merge array="{array}" with="{with}" />',
            'expectedExceptionCode' => 1256475113,
        ];
        yield 'invalid with parameter' => [
            'arguments' => [
                'array' => [],
                'with' => $noneScalarType,
            ],
            'src' => '<f:merge array="{array}" with="{with}" />',
            'expectedExceptionCode' => 1256475113,
        ];
        yield 'invalid no array parameter' => [
            'arguments' => [
                'array' => null,
                'with' => [],
            ],
            'src' => '<f:merge array="{array}" with="{with}" />',
            'expectedExceptionCode' => 1755316529,
        ];
    }

    #[DataProvider('renderInvalidDataProvider')]
    #[Test]
    public function renderInvalid(array $arguments, string $src, int $expectedExceptionCode): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode($expectedExceptionCode);

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
