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

final class FlattenViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderValidDataProvider(): \Generator
    {
        yield 'empty array' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '<f:flatten value="{value}" />',
            'expectation' => [],
        ];
        yield 'empty array inline' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '{value -> f:flatten()}',
            'expectation' => [],
        ];
        yield 'single-dimensional array' => [
            'arguments' => [
                'value' => [1, 2, 3],
            ],
            'src' => '<f:flatten value="{value}" />',
            'expectation' => [1, 2, 3],
        ];
        yield 'single-dimensional array inline' => [
            'arguments' => [
                'value' => [1, 2, 3],
            ],
            'src' => '{value -> f:flatten()}',
            'expectation' => [1, 2, 3],
        ];
        yield 'simple multi-dimensional array' => [
            'arguments' => [
                'value' => [[1, 2], [3, 4]],
            ],
            'src' => '<f:flatten value="{value}" />',
            'expectation' => [1, 2, 3, 4],
        ];
        yield 'simple multi-dimensional array inline' => [
            'arguments' => [
                'value' => [[1, 2], [3, 4]],
            ],
            'src' => '{value -> f:flatten()}',
            'expectation' => [1, 2, 3, 4],
        ];
        yield 'multi-dimensional array with inline array' => [
            'arguments' => [],
            'src' => '<f:flatten value="{0: {0: \'1\', 1: \'2\'}, 1: {0: \'3\', 1: \'4\'}}" />',
            'expectation' => [1, 2, 3, 4],
        ];
        yield 'deeply nested array' => [
            'arguments' => [
                'value' => [1, [2, [3, [4, 5]]]],
            ],
            'src' => '<f:flatten value="{value}" />',
            'expectation' => [1, 2, 3, 4, 5],
        ];
        yield 'array with mixed types' => [
            'arguments' => [
                'value' => [1, ['string', true], [null, 3.14]],
            ],
            'src' => '<f:flatten value="{value}" />',
            'expectation' => [1, 'string', true, null, 3.14],
        ];
        yield 'value inline as iterable' => [
            'arguments' => [
                'value' => new IterableExample([[1, 2], [3, 4]]),
            ],
            'src' => '{value -> f:flatten()}',
            'expectation' => [1, 2, 3, 4],
        ];
        yield 'value inline and argument' => [
            'arguments' => [
                'valueInline' => [[1, 2], [3, 4]],
                'valueArgument' => [[5, 6], [7, 8]],
            ],
            'src' => '{valueInline -> f:flatten(value: valueArgument)}',
            'expectation' => [5, 6, 7, 8],
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

    public static function renderInvalidDataProvider(): \Generator
    {
        yield 'invalid string content' => [
            'arguments' => [
            ],
            'src' => '<f:flatten>SOME TEXT</f:flatten>',
            'exceptionCode' => 1750878602,
        ];
        yield 'invalid string attribute' => [
            'arguments' => [
                'value' => 'string',
            ],
            'src' => '<f:flatten value="string" />',
            'exceptionCode' => 1256475113,
        ];
        yield 'invalid string inline' => [
            'arguments' => [
                'value' => 'string',
            ],
            'src' => '{value -> f:flatten()}',
            'exceptionCode' => 1750878602,
        ];
        yield 'invalid integer attribute' => [
            'arguments' => [
                'value' => 123,
            ],
            'src' => '<f:flatten value="{value}" />',
            'exceptionCode' => 1256475113,
        ];
        yield 'invalid boolean attribute' => [
            'arguments' => [
                'value' => true,
            ],
            'src' => '<f:flatten value="{value}" />',
            'exceptionCode' => 1256475113,
        ];
        yield 'arrayaccess inline' => [
            'arguments' => [
                'value' => new ArrayAccessExample(['foo' => 'bar']),
            ],
            'src' => '{value -> f:flatten()}',
            'exceptionCode' => 1750878602,
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

    public static function renderWithRepeatedIteratorKeysDataProvider(): \Generator
    {
        $nestedNumericKeys = static function (): \Generator {
            yield from [[1, 2]];
            yield from [[3, 4]];
        };
        $nestedStringKeys = static function (): \Generator {
            yield from ['a' => [1, 2]];
            yield from ['a' => [3]];
        };
        $nestedComposed = static function (): \Generator {
            yield from (static function (): \Generator {
                yield [1];
                yield 'k' => [2, [3]];
            })();
            yield from [[4]];
            yield 'k' => 5;
        };
        yield 'repeated numeric keys' => [
            'valueFactory' => $nestedNumericKeys,
            'src' => '{value -> f:flatten()}',
            'expectation' => [1, 2, 3, 4],
        ];
        yield 'repeated string keys' => [
            'valueFactory' => $nestedStringKeys,
            'src' => '{value -> f:flatten()}',
            'expectation' => [1, 2, 3],
        ];
        yield 'composed generators' => [
            'valueFactory' => $nestedComposed,
            'src' => '<f:flatten value="{value}" />',
            'expectation' => [1, 2, 3, 4, 5],
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
