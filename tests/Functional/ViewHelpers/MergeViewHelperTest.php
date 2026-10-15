<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3\CMS\Fluid\Tests\Functional\ViewHelpers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\ViewHelper\InvalidArgumentValueException;
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
        $this->expectException(InvalidArgumentValueException::class);
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

    public static function renderWithRepeatedIteratorKeysDataProvider(): \Generator
    {
        $numericKeys = static function (): \Generator {
            yield from [2, 1];
            yield from [4, 3];
        };
        $stringKeys = static function (): \Generator {
            yield from ['a' => 2, 'b' => 1];
            yield from ['a' => 4, 'b' => 3];
        };
        $nestedStringValues = static function (): \Generator {
            yield from ['a' => [1]];
            yield from ['a' => [2]];
        };
        yield 'repeated numeric keys' => [
            'valueFactory' => $numericKeys,
            'src' => '{value -> f:merge(with: {0: 5})}',
            'expectation' => [2, 1, 4, 3, 5],
        ];
        yield 'repeated numeric keys in with' => [
            'valueFactory' => $numericKeys,
            'src' => '<f:merge array="{0: 1}" with="{value}" />',
            'expectation' => [1, 2, 1, 4, 3],
        ];
        yield 'repeated string keys use the last item per key' => [
            'valueFactory' => $stringKeys,
            'src' => '{value -> f:merge(with: {c: 5})}',
            'expectation' => ['a' => 4, 'b' => 3, 'c' => 5],
        ];
        yield 'repeated string keys are merged recursively' => [
            'valueFactory' => $nestedStringValues,
            'src' => '{value -> f:merge(with: {a: {0: 3}}, recursive: true)}',
            'expectation' => ['a' => [1, 2, 3]],
        ];
    }

    #[DataProvider('renderWithRepeatedIteratorKeysDataProvider')]
    #[Test]
    public function renderWithRepeatedIteratorKeys(\Closure $valueFactory, string $src, mixed $expectation): void
    {
        // Generators can't be rewound, so each rendering gets a fresh one. The variable name is unique
        // per data set, so the first rendering can't reuse a template compiled by another test.
        $variableName = 'value' . hash('xxh3', (string)$this->dataName());
        $src = str_replace('{value', '{' . $variableName, $src);

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($src);
        $templateIdentifier = $view->getRenderingContext()->getTemplatePaths()->getTemplateIdentifier();
        self::assertFalse($view->getRenderingContext()->getTemplateCompiler()->has($templateIdentifier), 'First rendering must be uncompiled');
        $view->assign($variableName, $valueFactory());
        self::assertSame($expectation, $view->render());

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($src);
        self::assertTrue($view->getRenderingContext()->getTemplateCompiler()->has($templateIdentifier), 'Second rendering must use the compiled template');
        $view->assign($variableName, $valueFactory());
        self::assertSame($expectation, $view->render());
    }
}
