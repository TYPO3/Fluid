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
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\UserWithoutToString;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\UserWithToString;
use TYPO3Fluid\Fluid\View\TemplateView;

final class SortViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderValidDataProvider(): \Generator
    {
        $book = ['title' => 'Book', 'price' => 12];
        $pen = ['title' => 'Pen', 'price' => 2.5];
        $cup = ['title' => 'Cup', 'price' => 6];
        $mug = ['title' => 'Mug', 'price' => 6];
        $anna = new UserWithoutToString('Anna');
        $ben = new UserWithoutToString('Ben');
        $files = ['img12.png', 'img10.png', 'img2.png', 'img1.png'];
        $spring = ['date' => new \DateTimeImmutable('2026-03-01')];
        $christmas = ['date' => new \DateTimeImmutable('2025-12-24')];
        yield 'empty array' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '<f:sort value="{value}" />',
            'expectation' => [],
        ];
        yield 'empty array inline' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '{value -> f:sort()}',
            'expectation' => [],
        ];
        yield 'numbers' => [
            'arguments' => [
                'value' => [3, 1, 2.5],
            ],
            'src' => '<f:sort value="{value}" />',
            'expectation' => [1, 2.5, 3],
        ];
        yield 'numbers inline' => [
            'arguments' => [
                'value' => [3, 1, 2.5],
            ],
            'src' => '{value -> f:sort()}',
            'expectation' => [1, 2.5, 3],
        ];
        yield 'numbers are compared by value' => [
            'arguments' => [
                'value' => [100, 9, 11],
            ],
            'src' => '<f:sort value="{value}" />',
            'expectation' => [9, 11, 100],
        ];
        yield 'numeric strings are compared by value' => [
            'arguments' => [
                'value' => ['100', '9', '11'],
            ],
            'src' => '<f:sort value="{value}" />',
            'expectation' => ['9', '11', '100'],
        ];
        yield 'numbers and numeric strings are compared by value' => [
            'arguments' => [
                'value' => ['100', 11, '9.5'],
            ],
            'src' => '<f:sort value="{value}" />',
            'expectation' => ['9.5', 11, '100'],
        ];
        yield 'negative and decimal numbers' => [
            'arguments' => [
                'value' => [-5, '10', 2.5, -10, '1.10', '1.9'],
            ],
            'src' => '<f:sort value="{value}" />',
            'expectation' => [-10, -5, '1.10', '1.9', 2.5, '10'],
        ];
        yield 'strings are compared case-insensitively' => [
            'arguments' => [
                'value' => ['banana', 'apple', 'Cherry'],
            ],
            'src' => '<f:sort value="{value}" />',
            'expectation' => ['apple', 'banana', 'Cherry'],
        ];
        yield 'strings differing only in case keep their order' => [
            'arguments' => [
                'value' => ['b', 'B', 'a', 'A'],
            ],
            'src' => '<f:sort value="{value}" />',
            'expectation' => ['a', 'A', 'b', 'B'],
        ];
        yield 'strings in natural order' => [
            'arguments' => [
                'value' => ['img12.png', 'IMG10.png', 'img2.png', 'img1.png', 'img100.png', 'img11.png'],
            ],
            'src' => '<f:sort value="{value}" />',
            'expectation' => ['img1.png', 'img2.png', 'IMG10.png', 'img11.png', 'img12.png', 'img100.png'],
        ];
        yield 'descending' => [
            'arguments' => [
                'value' => [3, 1, 2],
            ],
            'src' => '<f:sort value="{value}" descending="true" />',
            'expectation' => [3, 2, 1],
        ];
        yield 'descending inline' => [
            'arguments' => [
                'value' => [3, 1, 2],
            ],
            'src' => '{value -> f:sort(descending: true)}',
            'expectation' => [3, 2, 1],
        ];
        yield 'descending disabled' => [
            'arguments' => [
                'value' => [3, 1, 2],
            ],
            'src' => '<f:sort value="{value}" descending="false" />',
            'expectation' => [1, 2, 3],
        ];
        yield 'keys are reset by default' => [
            'arguments' => [
                'value' => ['c' => 3, 'a' => 1, 'b' => 2],
            ],
            'src' => '<f:sort value="{value}" />',
            'expectation' => [1, 2, 3],
        ];
        yield 'preserveKeys enabled' => [
            'arguments' => [
                'value' => ['c' => 3, 'a' => 1, 'b' => 2],
            ],
            'src' => '<f:sort value="{value}" preserveKeys="true" />',
            'expectation' => ['a' => 1, 'b' => 2, 'c' => 3],
        ];
        yield 'preserveKeys enabled inline' => [
            'arguments' => [
                'value' => [3, 1, 2],
            ],
            'src' => '{value -> f:sort(preserveKeys: true)}',
            'expectation' => [1 => 1, 2 => 2, 0 => 3],
        ];
        yield 'property of arrays' => [
            'arguments' => [
                'value' => [$book, $pen, $cup],
            ],
            'src' => '<f:sort value="{value}" property="price" />',
            'expectation' => [$pen, $cup, $book],
        ];
        yield 'property of arrays inline' => [
            'arguments' => [
                'value' => [$book, $pen, $cup],
            ],
            'src' => '{value -> f:sort(property: \'price\')}',
            'expectation' => [$pen, $cup, $book],
        ];
        yield 'property descending' => [
            'arguments' => [
                'value' => [$pen, $book, $cup],
            ],
            'src' => '<f:sort value="{value}" property="price" descending="true" />',
            'expectation' => [$book, $cup, $pen],
        ];
        yield 'equal values keep their order' => [
            'arguments' => [
                'value' => [$mug, $book, $cup],
            ],
            'src' => '<f:sort value="{value}" property="price" />',
            'expectation' => [$mug, $cup, $book],
        ];
        yield 'equal values keep their order when descending' => [
            'arguments' => [
                'value' => [$mug, $pen, $cup],
            ],
            'src' => '<f:sort value="{value}" property="price" descending="true" />',
            'expectation' => [$mug, $cup, $pen],
        ];
        yield 'property with preserveKeys enabled' => [
            'arguments' => [
                'value' => ['book' => $book, 'pen' => $pen],
            ],
            'src' => '<f:sort value="{value}" property="price" preserveKeys="true" />',
            'expectation' => ['pen' => $pen, 'book' => $book],
        ];
        yield 'property of objects with getters' => [
            'arguments' => [
                'value' => [$ben, $anna],
            ],
            'src' => '<f:sort value="{value}" property="name" />',
            'expectation' => [$anna, $ben],
        ];
        yield 'nested property path' => [
            'arguments' => [
                'value' => [['author' => $ben], ['author' => ['name' => 'Anna']]],
            ],
            'src' => '<f:sort value="{value}" property="author.name" />',
            'expectation' => [['author' => ['name' => 'Anna']], ['author' => $ben]],
        ];
        yield 'missing property sorts first' => [
            'arguments' => [
                'value' => [$book, ['title' => 'Gift']],
            ],
            'src' => '<f:sort value="{value}" property="price" />',
            'expectation' => [['title' => 'Gift'], $book],
        ];
        yield 'date objects' => [
            'arguments' => [
                'value' => [$spring, $christmas],
            ],
            'src' => '<f:sort value="{value}" property="date" />',
            'expectation' => [$christmas, $spring],
        ];
        yield 'natural order inline' => [
            'arguments' => [
                'value' => $files,
            ],
            'src' => '{value -> f:sort()}',
            'expectation' => ['img1.png', 'img2.png', 'img10.png', 'img12.png'],
        ];
        yield 'natural order descending' => [
            'arguments' => [
                'value' => $files,
            ],
            'src' => '<f:sort value="{value}" descending="true" />',
            'expectation' => ['img12.png', 'img10.png', 'img2.png', 'img1.png'],
        ];
        yield 'natural order with property' => [
            'arguments' => [
                'value' => [['file' => 'img10.png'], ['file' => 'img2.png']],
            ],
            'src' => '<f:sort value="{value}" property="file" />',
            'expectation' => [['file' => 'img2.png'], ['file' => 'img10.png']],
        ];
        yield 'natural order with stringable objects and null' => [
            'arguments' => [
                'value' => [['user' => new UserWithToString('user10')], ['user' => null], ['user' => new UserWithToString('user9')]],
            ],
            'src' => '<f:for each="{value -> f:sort(property: \'user\')}" as="item">[{item.user}]</f:for>',
            'expectation' => '[][user9][user10]',
        ];
        yield 'array values' => [
            'arguments' => [
                'value' => [[2, 1], [1, 2], [1]],
            ],
            'src' => '<f:sort value="{value}" />',
            'expectation' => [[1], [1, 2], [2, 1]],
        ];
        yield 'array with inline array' => [
            'arguments' => [],
            'src' => '<f:sort value="{0: 3, 1: 1, 2: 2}" />',
            'expectation' => [1, 2, 3],
        ];
        yield 'value as tag content' => [
            'arguments' => [
                'value' => [3, 1, 2],
            ],
            'src' => '<f:sort>{value}</f:sort>',
            'expectation' => [1, 2, 3],
        ];
        yield 'value inline as iterable' => [
            'arguments' => [
                'value' => new IterableExample([3, 1, 2]),
            ],
            'src' => '{value -> f:sort()}',
            'expectation' => [1, 2, 3],
        ];
        yield 'value inline and argument' => [
            'arguments' => [
                'valueInline' => [3, 1],
                'valueArgument' => [6, 4],
            ],
            'src' => '{valueInline -> f:sort(value: valueArgument)}',
            'expectation' => [4, 6],
        ];
        yield 'used as iteration subject' => [
            'arguments' => [
                'value' => [$book, $pen, $cup],
            ],
            'src' => '<f:for each="{value -> f:sort(property: \'price\')}" as="item">[{item.title}]</f:for>',
            'expectation' => '[Pen][Cup][Book]',
        ];
        yield 'object' => [
            'arguments' => [
                'value' => (object)['large' => 100, 'small' => 9, 'medium' => 11],
            ],
            'src' => '<f:sort value="{value}" />',
            'expectation' => [9, 11, 100],
        ];
        yield 'object inline with preserveKeys' => [
            'arguments' => [
                'value' => (object)['large' => 100, 'small' => 9, 'medium' => 11],
            ],
            'src' => '{value -> f:sort(preserveKeys: true)}',
            'expectation' => ['small' => 9, 'medium' => 11, 'large' => 100],
        ];
        yield 'object uses getters before properties' => [
            'arguments' => [
                'value' => new class () {
                    public int $first = 1;
                    protected int $second = 2;
                    protected int $internal = 0;

                    public function getFirst(): int
                    {
                        return 3;
                    }

                    public function getSecond(): int
                    {
                        return $this->second;
                    }
                },
            ],
            'src' => '{value -> f:sort(preserveKeys: true)}',
            'expectation' => ['second' => 2, 'first' => 3],
        ];
        yield 'object with property' => [
            'arguments' => [
                'value' => (object)['book' => ['price' => 12], 'pen' => ['price' => 2.5]],
            ],
            'src' => '{value -> f:sort(property: \'price\', preserveKeys: true)}',
            'expectation' => ['pen' => ['price' => 2.5], 'book' => ['price' => 12]],
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
            'src' => '{value -> f:sort()}',
            'expectation' => [1, 2, 3, 4],
        ];
        yield 'repeated string keys' => [
            'valueFactory' => $stringKeys,
            'src' => '{value -> f:sort(descending: true)}',
            'expectation' => [4, 3, 2, 1],
        ];
        yield 'composed generators' => [
            'valueFactory' => $composed,
            'src' => '<f:sort value="{value}" descending="true" />',
            'expectation' => ['e', 'd', 'c', 'b', 'a'],
        ];
        yield 'repeated keys with preserveKeys use the last item per key' => [
            'valueFactory' => $numericKeys,
            'src' => '{value -> f:sort(preserveKeys: true)}',
            'expectation' => [1 => 3, 0 => 4],
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
            'src' => '<f:sort>SOME TEXT</f:sort>',
            'exceptionCode' => 1792005220,
        ];
        yield 'invalid string attribute' => [
            'arguments' => [],
            'src' => '<f:sort value="string" />',
            'exceptionCode' => 1792005220,
        ];
        yield 'invalid string inline' => [
            'arguments' => [
                'value' => 'string',
            ],
            'src' => '{value -> f:sort()}',
            'exceptionCode' => 1792005220,
        ];
        yield 'invalid integer attribute' => [
            'arguments' => [
                'value' => 123,
            ],
            'src' => '<f:sort value="{value}" />',
            'exceptionCode' => 1792005220,
        ];
        yield 'undefined value' => [
            'arguments' => [],
            'src' => '<f:sort value="{undefinedVariable}" />',
            'exceptionCode' => 1792005220,
        ];
        yield 'arrayaccess inline' => [
            'arguments' => [
                'value' => new ArrayAccessExample(['foo' => 'bar']),
            ],
            'src' => '{value -> f:sort()}',
            'exceptionCode' => 1794820193,
        ];
        yield 'arrayaccess attribute' => [
            'arguments' => [
                'value' => new ArrayAccessExample(['foo' => 'bar']),
            ],
            'src' => '<f:sort value="{value}" />',
            'exceptionCode' => 1794820193,
        ];
        yield 'scalar items with property' => [
            'arguments' => [
                'value' => [['price' => 1], 2],
            ],
            'src' => '<f:sort value="{value}" property="price" />',
            'exceptionCode' => 1792005221,
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
