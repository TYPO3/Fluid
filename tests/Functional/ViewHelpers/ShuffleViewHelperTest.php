<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\ViewHelper\InvalidArgumentValueException;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\ArrayAccessExample;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\IterableExample;
use TYPO3Fluid\Fluid\View\TemplateView;

final class ShuffleViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderValidDataProvider(): \Generator
    {
        yield 'empty array' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '<f:shuffle value="{value}" />',
            'expectation' => [],
        ];
        yield 'empty array inline' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '{value -> f:shuffle()}',
            'expectation' => [],
        ];
        yield 'single item' => [
            'arguments' => [
                'value' => [1],
            ],
            'src' => '<f:shuffle value="{value}" />',
            'expectation' => [1],
        ];
    }

    public static function shuffleContentDataProvider(): \Generator
    {
        yield 'value attribute' => [
            'arguments' => [
                'value' => [1, 2, 3],
            ],
            'src' => '<f:shuffle value="{value}" />',
            'expectation' => function (array $result, array $arguments) {
                return count($result) === count($arguments['value'])
                       && empty(array_diff($result, $arguments['value']))
                       && empty(array_diff($arguments['value'], $result));
            },
        ];
        yield 'value attribute inline array' => [
            'arguments' => [
                'value' => [1, 2, 3],
            ],
            'src' => '<f:shuffle value="{0: \'1\', 1: \'2\', 2: \'3\'}" />',
            'expectation' => function (array $result) {
                $expected = ['1', '2', '3'];
                return count($result) === count($expected)
                       && empty(array_diff($result, $expected))
                       && empty(array_diff($expected, $result));
            },
        ];
        yield 'value inline' => [
            'arguments' => [
                'value' => [1, 2, 3],
            ],
            'src' => '{value -> f:shuffle()}',
            'expectation' => function (array $result, array $arguments) {
                return count($result) === count($arguments['value'])
                       && empty(array_diff($result, $arguments['value']))
                       && empty(array_diff($arguments['value'], $result));
            },
        ];
        yield 'value inline as iterable' => [
            'arguments' => [
                'value' => new IterableExample([1, 2, 3]),
            ],
            'src' => '{value -> f:shuffle()}',
            'expectation' => function (array $result) {
                $expected = [1, 2, 3];
                return count($result) === count($expected)
                       && empty(array_diff($result, $expected))
                       && empty(array_diff($expected, $result));
            },
        ];
        yield 'value inline and argument' => [
            'arguments' => [
                'valueInline' => [1, 2, 3],
                'valueArgument' => [3, 2, 1],
            ],
            'src' => '{valueInline -> f:shuffle(value: valueArgument)}',
            'expectation' => function (array $result, array $arguments) {
                return count($result) === count($arguments['valueArgument'])
                       && empty(array_diff($result, $arguments['valueArgument']))
                       && empty(array_diff($arguments['valueArgument'], $result));
            },
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
        $result = $view->render();

        self::assertSame($expectation, $result);

        // Test with cache
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($src);
        $view->assignMultiple($arguments);
        $result = $view->render();

        self::assertSame($expectation, $result);
    }

    #[DataProvider('shuffleContentDataProvider')]
    #[Test]
    public function testShuffleContent(array $arguments, string $src, callable $expectation): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($src);
        $view->assignMultiple($arguments);
        $result = $view->render();

        self::assertTrue($expectation($result, $arguments), 'Expectation callback returned false');

        // Test with cache
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($src);
        $view->assignMultiple($arguments);
        $result = $view->render();

        self::assertTrue($expectation($result, $arguments), 'Expectation callback returned false');
    }

    public static function renderInvalidDataProvider(): \Generator
    {
        yield 'invalid string content' => [
            'arguments' => [
            ],
            'src' => '<f:shuffle>SOME TEXT</f:shuffle>',
            'exceptionCode' => 1750881571,
        ];
        yield 'invalid string attribute' => [
            'arguments' => [
                'value' => 'string',
            ],
            'src' => '<f:shuffle value="string" />',
            'exceptionCode' => 1256475113,
        ];
        yield 'invalid string inline' => [
            'arguments' => [
                'value' => 'string',
            ],
            'src' => '{value -> f:shuffle()}',
            'exceptionCode' => 1750881571,
        ];
        yield 'arrayaccess inline' => [
            'arguments' => [
                'value' => new ArrayAccessExample(['foo' => 'bar']),
            ],
            'src' => '{value -> f:shuffle()}',
            'exceptionCode' => 1750881571,
        ];
    }

    #[DataProvider('renderInvalidDataProvider')]
    #[Test]
    public function renderInvalid(array $arguments, string $src, int $exceptionCode): void
    {
        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionCode($exceptionCode);

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

    public static function renderKeepsAllItemsOfIteratorsWithRepeatedKeysDataProvider(): \Generator
    {
        $numericKeys = static function (): \Generator {
            yield from [2, 1];
            yield from [4, 3];
        };
        $stringKeys = static function (): \Generator {
            yield from ['a' => 2, 'b' => 1];
            yield from ['a' => 4, 'b' => 3];
        };
        $composed = static function (): \Generator {
            yield from (static function (): \Generator {
                yield 'a';
                yield 'k' => 'b';
            })();
            yield from ['c', 'd'];
            yield 'k' => 'e';
        };
        yield 'repeated numeric keys' => [
            'valueFactory' => $numericKeys,
            'expectation' => [1, 2, 3, 4],
        ];
        yield 'repeated string keys' => [
            'valueFactory' => $stringKeys,
            'expectation' => [1, 2, 3, 4],
        ];
        yield 'composed generators' => [
            'valueFactory' => $composed,
            'expectation' => ['a', 'b', 'c', 'd', 'e'],
        ];
    }

    #[DataProvider('renderKeepsAllItemsOfIteratorsWithRepeatedKeysDataProvider')]
    #[Test]
    public function renderKeepsAllItemsOfIteratorsWithRepeatedKeys(\Closure $valueFactory, array $expectation): void
    {
        // Generators can't be rewound, so each rendering gets a fresh one. The variable name is unique
        // per data set, so the first rendering can't reuse a template compiled by another test.
        $variableName = 'value' . hash('xxh3', (string)$this->dataName());
        $src = '{' . $variableName . ' -> f:shuffle()}';

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($src);
        $templateIdentifier = $view->getRenderingContext()->getTemplatePaths()->getTemplateIdentifier();
        self::assertFalse($view->getRenderingContext()->getTemplateCompiler()->has($templateIdentifier), 'First rendering must be uncompiled');
        $view->assign($variableName, $valueFactory());
        $result = $view->render();
        sort($result);
        self::assertSame($expectation, $result);

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($src);
        self::assertTrue($view->getRenderingContext()->getTemplateCompiler()->has($templateIdentifier), 'Second rendering must use the compiled template');
        $view->assign($variableName, $valueFactory());
        $result = $view->render();
        sort($result);
        self::assertSame($expectation, $result);
    }
}
