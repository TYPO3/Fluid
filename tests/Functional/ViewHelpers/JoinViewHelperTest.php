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
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\ArrayAccessExample;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\IterableExample;
use TYPO3Fluid\Fluid\View\TemplateView;

final class JoinViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderValidDataProvider(): \Generator
    {
        yield 'template with named layout' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '<f:join value="{value}" />',
            'expectation' => '',
        ];
        yield 'empty value inline' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '{value -> f:join()}',
            'expectation' => '',
        ];
        yield 'single item' => [
            'arguments' => [
                'value' => [1],
            ],
            'src' => '<f:join value="{value}" />',
            'expectation' => '1',
        ];
        yield 'empty attribute value, with separator' => [
            'arguments' => [
                'value' => [],
                'separator' => ',',
            ],
            'src' => '<f:join value="{value}" separator="{separator}" />',
            'expectation' => '',
        ];
        yield 'empty attribute value, with separatorLast' => [
            'arguments' => [
                'value' => [],
                'separatorLast' => ' and ',
            ],
            'src' => '<f:join value="{value}" separatorLast="{separatorLast}" />',
            'expectation' => '',
        ];
        yield 'value attribute' => [
            'arguments' => [
                'value' => [1, 2, 3],
            ],
            'src' => '<f:join value="{value}" />',
            'expectation' => '123',
        ];
        yield 'value attribute inline array' => [
            'arguments' => [
                'value' => [1, 2, 3],
            ],
            'src' => '<f:join value="{0: \'1\', 1: \'2\', 2: \'3\'}" />',
            'expectation' => '123',
        ];
        yield 'value inline' => [
            'arguments' => [
                'value' => [1, 2, 3],
            ],
            'src' => '{value -> f:join()}',
            'expectation' => '123',
        ];
        yield 'value inline as iterable' => [
            'arguments' => [
                'value' => new IterableExample([1, 2, 3]),
            ],
            'src' => '{value -> f:join()}',
            'expectation' => '123',
        ];
        yield 'value inline and argument' => [
            'arguments' => [
                'valueInline' => [1, 2, 3],
                'valueArgument' => [3, 2, 1],
            ],
            'src' => '{valueInline -> f:join(value: valueArgument)}',
            'expectation' => '321',
        ];
        yield 'value and separator set' => [
            'arguments' => [
                'value' => [1, 2, 3],
                'separator' => ', ',
            ],
            'src' => '<f:join value="{value}" separator="{separator}" />',
            'expectation' => '1, 2, 3',
        ];
        yield 'value and separatorLast set' => [
            'arguments' => [
                'value' => [1, 2, 3],
                'separatorLast' => ' and ',
            ],
            'src' => '<f:join value="{value}" separatorLast="{separatorLast}" />',
            'expectation' => '12 and 3',
        ];
        yield 'value, separator and separatorLast set' => [
            'arguments' => [
                'value' => [1, 2, 3],
                'separator' => ', ',
                'separatorLast' => ' and ',
            ],
            'src' => '<f:join value="{value}" separator="{separator}" separatorLast="{separatorLast}" />',
            'expectation' => '1, 2 and 3',
        ];
    }

    #[DataProvider('renderValidDataProvider')]
    #[Test]
    public function renderValid(array $arguments, string $src, string $expectation): void
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
            'src' => '<f:join>SOME TEXT</f:join>',
        ];
        yield 'invalid string attribute' => [
            'arguments' => [
                'value' => 'string',
            ],
            'src' => '<f:join value="string" />',
        ];
        yield 'invalid string inline' => [
            'arguments' => [
                'value' => 'string',
            ],
            'src' => '{value -> f:join()}',
        ];
        yield 'arrayaccess inline' => [
            'arguments' => [
                'value' => new ArrayAccessExample(['foo' => 'bar']),
            ],
            'src' => '{value -> f:join()}',
        ];
    }

    #[DataProvider('renderInvalidDataProvider')]
    #[Test]
    public function renderInvalid(array $arguments, string $src): void
    {
        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionCode(1256475113);

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
            'src' => '{value -> f:join(separator: \',\')}',
            'expectation' => '2,1,4,3',
        ];
        yield 'repeated numeric keys with separatorLast' => [
            'valueFactory' => $numericKeys,
            'src' => '{value -> f:join(separator: \', \', separatorLast: \' and \')}',
            'expectation' => '2, 1, 4 and 3',
        ];
        yield 'repeated string keys with separatorLast' => [
            'valueFactory' => $stringKeys,
            'src' => '{value -> f:join(separator: \', \', separatorLast: \' and \')}',
            'expectation' => '2, 1, 4 and 3',
        ];
        yield 'composed generators' => [
            'valueFactory' => $composed,
            'src' => '<f:join value="{value}" separator="-" />',
            'expectation' => 'a-b-c-d-e',
        ];
        yield 'associative array with separatorLast' => [
            'valueFactory' => static fn(): array => ['a' => 'x', 'b' => 'y', 'c' => 'z'],
            'src' => '{value -> f:join(separator: \', \', separatorLast: \' and \')}',
            'expectation' => 'x, y and z',
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
