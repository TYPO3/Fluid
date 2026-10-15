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

final class ChunkViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderValidDataProvider(): \Generator
    {
        yield 'empty array' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '<f:array.chunk value="{value}" size="2" />',
            'expectation' => [],
        ];
        yield 'empty array inline' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '{value -> f:array.chunk(size: 2)}',
            'expectation' => [],
        ];
        yield 'evenly divisible array' => [
            'arguments' => [
                'value' => [1, 2, 3, 4],
            ],
            'src' => '<f:array.chunk value="{value}" size="2" />',
            'expectation' => [[1, 2], [3, 4]],
        ];
        yield 'evenly divisible array inline' => [
            'arguments' => [
                'value' => [1, 2, 3, 4],
            ],
            'src' => '{value -> f:array.chunk(size: 2)}',
            'expectation' => [[1, 2], [3, 4]],
        ];
        yield 'last chunk contains remaining items' => [
            'arguments' => [
                'value' => [1, 2, 3, 4, 5],
            ],
            'src' => '<f:array.chunk value="{value}" size="2" />',
            'expectation' => [[1, 2], [3, 4], [5]],
        ];
        yield 'size of one' => [
            'arguments' => [
                'value' => [1, 2, 3],
            ],
            'src' => '<f:array.chunk value="{value}" size="1" />',
            'expectation' => [[1], [2], [3]],
        ];
        yield 'size equal to number of items' => [
            'arguments' => [
                'value' => [1, 2, 3],
            ],
            'src' => '<f:array.chunk value="{value}" size="3" />',
            'expectation' => [[1, 2, 3]],
        ];
        yield 'size larger than number of items' => [
            'arguments' => [
                'value' => [1, 2, 3],
            ],
            'src' => '<f:array.chunk value="{value}" size="10" />',
            'expectation' => [[1, 2, 3]],
        ];
        yield 'size as variable' => [
            'arguments' => [
                'value' => [1, 2, 3, 4, 5, 6],
                'size' => 3,
            ],
            'src' => '<f:array.chunk value="{value}" size="{size}" />',
            'expectation' => [[1, 2, 3], [4, 5, 6]],
        ];
        yield 'size as string variable' => [
            'arguments' => [
                'value' => [1, 2, 3, 4, 5, 6],
                'size' => '3',
            ],
            'src' => '{value -> f:array.chunk(size: size)}',
            'expectation' => [[1, 2, 3], [4, 5, 6]],
        ];
        yield 'array with inline array' => [
            'arguments' => [],
            'src' => '<f:array.chunk value="{0: \'a\', 1: \'b\', 2: \'c\', 3: \'d\', 4: \'e\'}" size="2" />',
            'expectation' => [['a', 'b'], ['c', 'd'], ['e']],
        ];
        yield 'associative array does not preserve keys by default' => [
            'arguments' => [
                'value' => ['a' => 1, 'b' => 2, 'c' => 3],
            ],
            'src' => '<f:array.chunk value="{value}" size="2" />',
            'expectation' => [[1, 2], [3]],
        ];
        yield 'associative array with preserveKeys disabled' => [
            'arguments' => [
                'value' => ['a' => 1, 'b' => 2, 'c' => 3],
            ],
            'src' => '<f:array.chunk value="{value}" size="2" preserveKeys="false" />',
            'expectation' => [[1, 2], [3]],
        ];
        yield 'associative array with preserveKeys enabled' => [
            'arguments' => [
                'value' => ['a' => 1, 'b' => 2, 'c' => 3],
            ],
            'src' => '<f:array.chunk value="{value}" size="2" preserveKeys="true" />',
            'expectation' => [['a' => 1, 'b' => 2], ['c' => 3]],
        ];
        yield 'associative array with preserveKeys enabled inline' => [
            'arguments' => [
                'value' => ['a' => 1, 'b' => 2, 'c' => 3],
            ],
            'src' => '{value -> f:array.chunk(size: 2, preserveKeys: true)}',
            'expectation' => [['a' => 1, 'b' => 2], ['c' => 3]],
        ];
        yield 'numeric array with preserveKeys enabled' => [
            'arguments' => [
                'value' => [1, 2, 3],
            ],
            'src' => '<f:array.chunk value="{value}" size="2" preserveKeys="true" />',
            'expectation' => [[0 => 1, 1 => 2], [2 => 3]],
        ];
        yield 'preserveKeys with undefined variable' => [
            'arguments' => [
                'value' => ['a' => 1, 'b' => 2, 'c' => 3],
            ],
            'src' => '<f:array.chunk value="{value}" size="2" preserveKeys="{undefinedVariable}" />',
            'expectation' => [[1, 2], [3]],
        ];
        yield 'value as tag content' => [
            'arguments' => [
                'value' => [1, 2, 3],
            ],
            'src' => '<f:array.chunk size="2">{value}</f:array.chunk>',
            'expectation' => [[1, 2], [3]],
        ];
        yield 'value inline as iterable' => [
            'arguments' => [
                'value' => new IterableExample([1, 2, 3]),
            ],
            'src' => '{value -> f:array.chunk(size: 2)}',
            'expectation' => [[1, 2], [3]],
        ];
        yield 'value as iterable with preserveKeys enabled' => [
            'arguments' => [
                'value' => new IterableExample(['a' => 1, 'b' => 2, 'c' => 3]),
            ],
            'src' => '<f:array.chunk value="{value}" size="2" preserveKeys="true" />',
            'expectation' => [['a' => 1, 'b' => 2], ['c' => 3]],
        ];
        yield 'value inline and argument' => [
            'arguments' => [
                'valueInline' => [1, 2, 3],
                'valueArgument' => [4, 5, 6],
            ],
            'src' => '{valueInline -> f:array.chunk(value: valueArgument, size: 2)}',
            'expectation' => [[4, 5], [6]],
        ];
        yield 'nested arrays are kept intact' => [
            'arguments' => [
                'value' => [[1, 2], [3, 4], [5, 6]],
            ],
            'src' => '<f:array.chunk value="{value}" size="2" />',
            'expectation' => [[[1, 2], [3, 4]], [[5, 6]]],
        ];
        yield 'used as iteration subject' => [
            'arguments' => [
                'value' => ['a', 'b', 'c', 'd', 'e'],
            ],
            'src' => '<f:for each="{value -> f:array.chunk(size: 2)}" as="row">'
                . '[<f:for each="{row}" as="item">{item}</f:for>]'
                . '</f:for>',
            'expectation' => '[ab][cd][e]',
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
            'src' => '{value -> f:array.chunk(size: 2)}',
            'expectation' => [[2, 1], [4, 3]],
        ];
        yield 'repeated string keys' => [
            'valueFactory' => $stringKeys,
            'src' => '{value -> f:array.chunk(size: 2)}',
            'expectation' => [[2, 1], [4, 3]],
        ];
        yield 'composed generators' => [
            'valueFactory' => $composed,
            'src' => '<f:array.chunk value="{value}" size="2" />',
            'expectation' => [['a', 'b'], ['c', 'd'], ['e']],
        ];
        yield 'repeated keys with preserveKeys use the last item per key' => [
            'valueFactory' => $numericKeys,
            'src' => '{value -> f:array.chunk(size: 2, preserveKeys: true)}',
            'expectation' => [[0 => 4, 1 => 3]],
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
            'src' => '<f:array.chunk size="2">SOME TEXT</f:array.chunk>',
            'exceptionCode' => 1791976070,
        ];
        yield 'invalid string attribute' => [
            'arguments' => [],
            'src' => '<f:array.chunk value="string" size="2" />',
            'exceptionCode' => 1256475113,
        ];
        yield 'invalid string inline' => [
            'arguments' => [
                'value' => 'string',
            ],
            'src' => '{value -> f:array.chunk(size: 2)}',
            'exceptionCode' => 1791976070,
        ];
        yield 'invalid integer attribute' => [
            'arguments' => [
                'value' => 123,
            ],
            'src' => '<f:array.chunk value="{value}" size="2" />',
            'exceptionCode' => 1256475113,
        ];
        yield 'undefined value' => [
            'arguments' => [],
            'src' => '<f:array.chunk value="{undefinedVariable}" size="2" />',
            'exceptionCode' => 1791976070,
        ];
        yield 'arrayaccess inline' => [
            'arguments' => [
                'value' => new ArrayAccessExample(['foo' => 'bar']),
            ],
            'src' => '{value -> f:array.chunk(size: 2)}',
            'exceptionCode' => 1791976070,
        ];
        yield 'size of zero' => [
            'arguments' => [
                'value' => [1, 2, 3],
            ],
            'src' => '<f:array.chunk value="{value}" size="0" />',
            'exceptionCode' => 1791976071,
        ];
        yield 'size of zero inline' => [
            'arguments' => [
                'value' => [1, 2, 3],
            ],
            'src' => '{value -> f:array.chunk(size: 0)}',
            'exceptionCode' => 1791976071,
        ];
        yield 'negative size' => [
            'arguments' => [
                'value' => [1, 2, 3],
                'size' => -1,
            ],
            'src' => '<f:array.chunk value="{value}" size="{size}" />',
            'exceptionCode' => 1791976071,
        ];
        yield 'non-numeric size' => [
            'arguments' => [
                'value' => [1, 2, 3],
            ],
            'src' => '<f:array.chunk value="{value}" size="abc" />',
            'exceptionCode' => 1791976071,
        ];
        yield 'empty array with size of zero' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '<f:array.chunk value="{value}" size="0" />',
            'exceptionCode' => 1791976071,
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
