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

final class MaxViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderValidDataProvider(): iterable
    {
        yield 'empty value' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '<f:max value="{value}" />',
            'expectation' => null,
        ];
        yield 'empty value inline' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '{value -> f:max()}',
            'expectation' => null,
        ];
        yield 'single item' => [
            'arguments' => [
                'value' => [1],
            ],
            'src' => '<f:max value="{value}" />',
            'expectation' => 1,
        ];
        yield 'multiple items string' => [
            'arguments' => [
                'value' => ['first', 'second', 'third'],
            ],
            'src' => '<f:max value="{value}" />',
            'expectation' => 'third',
        ];
        yield 'multiple items numbers' => [
            'arguments' => [
                'value' => [0, 8, 15, 47, 11],
            ],
            'src' => '<f:max value="{value}" />',
            'expectation' => 47,
        ];
        yield 'value inline as iterable' => [
            'arguments' => [
                'value' => new IterableExample(['first', 'second', 'third']),
            ],
            'src' => '{value -> f:max()}',
            'expectation' => 'third',
        ];
    }

    #[DataProvider('renderValidDataProvider')]
    #[Test]
    public function renderValid(array $arguments, string $src, mixed $expectation): void
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
        yield 'invalid string content' => [
            'arguments' => [
            ],
            'src' => '<f:max>SOME TEXT</f:max>',
        ];

        yield 'invalid string inline' => [
            'arguments' => [
                'value' => 'string',
            ],
            'src' => '{value -> f:max()}',
        ];
        yield 'arrayaccess inline' => [
            'arguments' => [
                'value' => new ArrayAccessExample(['foo' => 'bar']),
            ],
            'src' => '{value -> f:max()}',
        ];
    }

    #[DataProvider('renderInvalidDataProvider')]
    #[Test]
    public function renderInvalid(array $arguments, string $src): void
    {
        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionCode(1756178710);

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
        $maxNumericKeys = static function (): \Generator {
            yield from [9, 1];
            yield from [4, 3];
        };
        $maxStringKeys = static function (): \Generator {
            yield from ['a' => 9, 'b' => 1];
            yield from ['a' => 4, 'b' => 3];
        };
        $maxComposed = static function (): \Generator {
            yield from (static function (): \Generator {
                yield 1;
                yield 'k' => 8;
            })();
            yield from [3, 5];
            yield 'k' => 2;
        };
        yield 'repeated numeric keys' => [
            'valueFactory' => $maxNumericKeys,
            'src' => '{value -> f:max()}',
            'expectation' => 9,
        ];
        yield 'repeated string keys' => [
            'valueFactory' => $maxStringKeys,
            'src' => '{value -> f:max()}',
            'expectation' => 9,
        ];
        yield 'composed generators' => [
            'valueFactory' => $maxComposed,
            'src' => '<f:max value="{value}" />',
            'expectation' => 8,
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
