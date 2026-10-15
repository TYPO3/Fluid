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

final class RandomViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderSingleResultDataProvider(): \Generator
    {
        yield 'empty array' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '<f:array.random value="{value}" />',
            'expectation' => null,
        ];
        yield 'empty array inline' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '{value -> f:array.random()}',
            'expectation' => null,
        ];
        yield 'single item' => [
            'arguments' => [
                'value' => ['a'],
            ],
            'src' => '<f:array.random value="{value}" />',
            'expectation' => 'a',
        ];
        yield 'single item inline' => [
            'arguments' => [
                'value' => ['a'],
            ],
            'src' => '{value -> f:array.random()}',
            'expectation' => 'a',
        ];
        yield 'single item of associative array returns value' => [
            'arguments' => [
                'value' => ['key' => 'a'],
            ],
            'src' => '<f:array.random value="{value}" />',
            'expectation' => 'a',
        ];
        yield 'single item as tag content' => [
            'arguments' => [
                'value' => ['a'],
            ],
            'src' => '<f:array.random>{value}</f:array.random>',
            'expectation' => 'a',
        ];
        yield 'single item inline as iterable' => [
            'arguments' => [
                'value' => new IterableExample(['a']),
            ],
            'src' => '{value -> f:array.random()}',
            'expectation' => 'a',
        ];
        yield 'single item with inline array' => [
            'arguments' => [],
            'src' => '<f:array.random value="{0: \'a\'}" />',
            'expectation' => 'a',
        ];
        yield 'value inline and argument' => [
            'arguments' => [
                'valueInline' => ['a'],
                'valueArgument' => ['b'],
            ],
            'src' => '{valueInline -> f:array.random(value: valueArgument)}',
            'expectation' => 'b',
        ];
        yield 'single item used in text' => [
            'arguments' => [
                'value' => ['Teaser A'],
            ],
            'src' => 'Today: {value -> f:array.random()}',
            'expectation' => 'Today: Teaser A',
        ];
    }

    #[DataProvider('renderSingleResultDataProvider')]
    #[Test]
    public function renderSingleResult(array $arguments, string $src, mixed $expectation): void
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

    public static function renderEventuallyReturnsEveryItemDataProvider(): \Generator
    {
        yield 'numeric array' => [
            'value' => ['a', 'b', 'c'],
            'src' => '<f:array.random value="{value}" />',
        ];
        yield 'numeric array inline' => [
            'value' => ['a', 'b', 'c'],
            'src' => '{value -> f:array.random()}',
        ];
        yield 'associative array' => [
            'value' => ['x' => 'a', 'y' => 'b', 'z' => 'c'],
            'src' => '<f:array.random value="{value}" />',
        ];
        yield 'iterable' => [
            'value' => new IterableExample(['a', 'b', 'c']),
            'src' => '{value -> f:array.random()}',
        ];
    }

    /**
     * Renders the template repeatedly. Each result must be an item of the array,
     * and every item must be selected at least once. With three items and 200
     * renderings, the chance of an item never being selected is negligible.
     */
    #[DataProvider('renderEventuallyReturnsEveryItemDataProvider')]
    #[Test]
    public function renderEventuallyReturnsEveryItem(iterable $value, string $src): void
    {
        $expectedItems = ['a', 'b', 'c'];
        $selectedItems = [];
        for ($i = 0; $i < 200; $i++) {
            $view = new TemplateView();
            $view->getRenderingContext()->setCache(self::$cache);
            $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($src);
            $view->assign('value', $value);
            $result = $view->render();
            self::assertContains($result, $expectedItems);
            $selectedItems[$result] = true;
        }
        self::assertEqualsCanonicalizing($expectedItems, array_keys($selectedItems));
    }

    #[Test]
    public function renderSelectsEveryItemOfIteratorWithRepeatedKeys(): void
    {
        $repeatedKeys = static function (): \Generator {
            yield from [2, 1];
            yield from [4, 3];
        };
        $src = '{repeatedKeys -> f:array.random()}';
        $selectedItems = [];
        for ($i = 0; $i < 200; $i++) {
            // Generators can't be rewound, so each rendering gets a fresh one
            $view = new TemplateView();
            $view->getRenderingContext()->setCache(self::$cache);
            $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($src);
            $templateIdentifier = $view->getRenderingContext()->getTemplatePaths()->getTemplateIdentifier();
            // The first rendering is uncompiled, all further renderings use the compiled template
            self::assertSame($i > 0, $view->getRenderingContext()->getTemplateCompiler()->has($templateIdentifier));
            $view->assign('repeatedKeys', $repeatedKeys());
            $result = $view->render();
            self::assertContains($result, [2, 1, 4, 3]);
            $selectedItems[$result] = true;
        }
        self::assertEqualsCanonicalizing([2, 1, 4, 3], array_keys($selectedItems));
    }

    public static function renderInvalidDataProvider(): \Generator
    {
        yield 'invalid string content' => [
            'arguments' => [],
            'src' => '<f:array.random>SOME TEXT</f:array.random>',
            'exceptionCode' => 1792005190,
        ];
        yield 'invalid string attribute' => [
            'arguments' => [],
            'src' => '<f:array.random value="string" />',
            'exceptionCode' => 1256475113,
        ];
        yield 'invalid string inline' => [
            'arguments' => [
                'value' => 'string',
            ],
            'src' => '{value -> f:array.random()}',
            'exceptionCode' => 1792005190,
        ];
        yield 'invalid integer attribute' => [
            'arguments' => [
                'value' => 123,
            ],
            'src' => '<f:array.random value="{value}" />',
            'exceptionCode' => 1256475113,
        ];
        yield 'undefined value' => [
            'arguments' => [],
            'src' => '<f:array.random value="{undefinedVariable}" />',
            'exceptionCode' => 1792005190,
        ];
        yield 'arrayaccess inline' => [
            'arguments' => [
                'value' => new ArrayAccessExample(['foo' => 'bar']),
            ],
            'src' => '{value -> f:array.random()}',
            'exceptionCode' => 1792005190,
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
