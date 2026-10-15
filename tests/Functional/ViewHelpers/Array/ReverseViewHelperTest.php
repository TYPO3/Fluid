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

final class ReverseViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderValidDataProvider(): \Generator
    {
        yield 'empty array' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '<f:array.reverse value="{value}" />',
            'expectation' => [],
        ];
        yield 'empty array inline' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '{value -> f:array.reverse()}',
            'expectation' => [],
        ];
        yield 'numeric array' => [
            'arguments' => [
                'value' => ['a', 'b', 'c'],
            ],
            'src' => '<f:array.reverse value="{value}" />',
            'expectation' => ['c', 'b', 'a'],
        ];
        yield 'numeric array inline' => [
            'arguments' => [
                'value' => ['a', 'b', 'c'],
            ],
            'src' => '{value -> f:array.reverse()}',
            'expectation' => ['c', 'b', 'a'],
        ];
        yield 'numeric array with preserveKeys enabled' => [
            'arguments' => [
                'value' => ['a', 'b', 'c'],
            ],
            'src' => '<f:array.reverse value="{value}" preserveKeys="true" />',
            'expectation' => [2 => 'c', 1 => 'b', 0 => 'a'],
        ];
        yield 'numeric array with preserveKeys enabled inline' => [
            'arguments' => [
                'value' => ['a', 'b', 'c'],
            ],
            'src' => '{value -> f:array.reverse(preserveKeys: true)}',
            'expectation' => [2 => 'c', 1 => 'b', 0 => 'a'],
        ];
        yield 'numeric array with preserveKeys disabled' => [
            'arguments' => [
                'value' => ['a', 'b', 'c'],
            ],
            'src' => '<f:array.reverse value="{value}" preserveKeys="false" />',
            'expectation' => ['c', 'b', 'a'],
        ];
        yield 'associative array keeps string keys' => [
            'arguments' => [
                'value' => ['a' => 1, 'b' => 2],
            ],
            'src' => '<f:array.reverse value="{value}" />',
            'expectation' => ['b' => 2, 'a' => 1],
        ];
        yield 'mixed keys' => [
            'arguments' => [
                'value' => [5 => 'a', 'x' => 'b', 10 => 'c'],
            ],
            'src' => '<f:array.reverse value="{value}" />',
            'expectation' => [0 => 'c', 'x' => 'b', 1 => 'a'],
        ];
        yield 'mixed keys with preserveKeys enabled' => [
            'arguments' => [
                'value' => [5 => 'a', 'x' => 'b', 10 => 'c'],
            ],
            'src' => '<f:array.reverse value="{value}" preserveKeys="true" />',
            'expectation' => [10 => 'c', 'x' => 'b', 5 => 'a'],
        ];
        yield 'array with inline array' => [
            'arguments' => [],
            'src' => '<f:array.reverse value="{0: \'a\', 1: \'b\', 2: \'c\'}" />',
            'expectation' => ['c', 'b', 'a'],
        ];
        yield 'value as tag content' => [
            'arguments' => [
                'value' => ['a', 'b', 'c'],
            ],
            'src' => '<f:array.reverse>{value}</f:array.reverse>',
            'expectation' => ['c', 'b', 'a'],
        ];
        yield 'value inline as iterable' => [
            'arguments' => [
                'value' => new IterableExample(['a', 'b', 'c']),
            ],
            'src' => '{value -> f:array.reverse()}',
            'expectation' => ['c', 'b', 'a'],
        ];
        yield 'value inline and argument' => [
            'arguments' => [
                'valueInline' => ['a', 'b'],
                'valueArgument' => ['c', 'd'],
            ],
            'src' => '{valueInline -> f:array.reverse(value: valueArgument)}',
            'expectation' => ['d', 'c'],
        ];
        yield 'reversed and joined' => [
            'arguments' => [
                'value' => ['a', 'b', 'c'],
            ],
            'src' => '{value -> f:array.reverse() -> f:join(separator: \', \')}',
            'expectation' => 'c, b, a',
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

    public static function renderInvalidDataProvider(): \Generator
    {
        yield 'invalid string content' => [
            'arguments' => [],
            'src' => '<f:array.reverse>SOME TEXT</f:array.reverse>',
            'exceptionCode' => 1792005160,
        ];
        yield 'invalid string attribute' => [
            'arguments' => [],
            'src' => '<f:array.reverse value="string" />',
            'exceptionCode' => 1256475113,
        ];
        yield 'invalid string inline' => [
            'arguments' => [
                'value' => 'string',
            ],
            'src' => '{value -> f:array.reverse()}',
            'exceptionCode' => 1792005160,
        ];
        yield 'invalid integer attribute' => [
            'arguments' => [
                'value' => 123,
            ],
            'src' => '<f:array.reverse value="{value}" />',
            'exceptionCode' => 1256475113,
        ];
        yield 'undefined value' => [
            'arguments' => [],
            'src' => '<f:array.reverse value="{undefinedVariable}" />',
            'exceptionCode' => 1792005160,
        ];
        yield 'arrayaccess inline' => [
            'arguments' => [
                'value' => new ArrayAccessExample(['foo' => 'bar']),
            ],
            'src' => '{value -> f:array.reverse()}',
            'exceptionCode' => 1792005160,
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
