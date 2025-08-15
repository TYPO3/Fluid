<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
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
        $this->expectException(\InvalidArgumentException::class);
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
