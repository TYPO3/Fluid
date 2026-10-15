<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers\Array;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\ViewHelper\InvalidArgumentValueException;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\ArrayAccessExample;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\IterableExample;
use TYPO3Fluid\Fluid\View\TemplateView;

final class KeysViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderValidDataProvider(): \Generator
    {
        yield 'empty array' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '<f:array.keys value="{value}" />',
            'expectation' => [],
        ];
        yield 'empty array inline' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '{value -> f:array.keys()}',
            'expectation' => [],
        ];
        yield 'numeric array' => [
            'arguments' => [
                'value' => ['a', 'b', 'c'],
            ],
            'src' => '<f:array.keys value="{value}" />',
            'expectation' => [0, 1, 2],
        ];
        yield 'associative array' => [
            'arguments' => [
                'value' => ['title' => 'Fluid', 'version' => 5],
            ],
            'src' => '<f:array.keys value="{value}" />',
            'expectation' => ['title', 'version'],
        ];
        yield 'associative array inline' => [
            'arguments' => [
                'value' => ['title' => 'Fluid', 'version' => 5],
            ],
            'src' => '{value -> f:array.keys()}',
            'expectation' => ['title', 'version'],
        ];
        yield 'mixed keys' => [
            'arguments' => [
                'value' => [5 => 'a', 'b' => 'b', 10 => 'c'],
            ],
            'src' => '<f:array.keys value="{value}" />',
            'expectation' => [5, 'b', 10],
        ];
        yield 'array with inline array' => [
            'arguments' => [],
            'src' => '<f:array.keys value="{title: \'Fluid\', version: 5}" />',
            'expectation' => ['title', 'version'],
        ];
        yield 'value as tag content' => [
            'arguments' => [
                'value' => ['a' => 1, 'b' => 2],
            ],
            'src' => '<f:array.keys>{value}</f:array.keys>',
            'expectation' => ['a', 'b'],
        ];
        yield 'value inline as iterable' => [
            'arguments' => [
                'value' => new IterableExample(['a' => 1, 'b' => 2]),
            ],
            'src' => '{value -> f:array.keys()}',
            'expectation' => ['a', 'b'],
        ];
        yield 'value inline and argument' => [
            'arguments' => [
                'valueInline' => ['a' => 1],
                'valueArgument' => ['b' => 2],
            ],
            'src' => '{valueInline -> f:array.keys(value: valueArgument)}',
            'expectation' => ['b'],
        ];
        yield 'keys joined' => [
            'arguments' => [
                'value' => ['title' => 'Fluid', 'version' => 5],
            ],
            'src' => '{value -> f:array.keys() -> f:join(separator: \', \')}',
            'expectation' => 'title, version',
        ];
    }

    #[DataProvider('renderValidDataProvider')]
    #[Test]
    public function renderValid(array $arguments, string $src, array|string $expectation): void
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
            'src' => '{value -> f:array.keys()}',
            'expectation' => [0, 1, 0, 1],
        ];
        yield 'repeated string keys' => [
            'valueFactory' => $stringKeys,
            'src' => '{value -> f:array.keys()}',
            'expectation' => ['a', 'b', 'a', 'b'],
        ];
        yield 'composed generators' => [
            'valueFactory' => $composed,
            'src' => '<f:array.keys value="{value}" />',
            'expectation' => [0, 'k', 0, 1, 'k'],
        ];
    }

    #[DataProvider('renderWithRepeatedIteratorKeysDataProvider')]
    #[Test]
    public function renderWithRepeatedIteratorKeys(\Closure $valueFactory, string $src, array $expectation): void
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

    public static function renderInvalidDataProvider(): \Generator
    {
        yield 'invalid string content' => [
            'arguments' => [],
            'src' => '<f:array.keys>SOME TEXT</f:array.keys>',
            'exceptionCode' => 1792005150,
        ];
        yield 'invalid string attribute' => [
            'arguments' => [],
            'src' => '<f:array.keys value="string" />',
            'exceptionCode' => 1256475113,
        ];
        yield 'invalid string inline' => [
            'arguments' => [
                'value' => 'string',
            ],
            'src' => '{value -> f:array.keys()}',
            'exceptionCode' => 1792005150,
        ];
        yield 'invalid integer attribute' => [
            'arguments' => [
                'value' => 123,
            ],
            'src' => '<f:array.keys value="{value}" />',
            'exceptionCode' => 1256475113,
        ];
        yield 'undefined value' => [
            'arguments' => [],
            'src' => '<f:array.keys value="{undefinedVariable}" />',
            'exceptionCode' => 1792005150,
        ];
        yield 'arrayaccess inline' => [
            'arguments' => [
                'value' => new ArrayAccessExample(['foo' => 'bar']),
            ],
            'src' => '{value -> f:array.keys()}',
            'exceptionCode' => 1792005150,
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
}
