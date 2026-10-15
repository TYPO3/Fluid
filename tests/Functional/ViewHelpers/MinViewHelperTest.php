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

final class MinViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderValidDataProvider(): iterable
    {
        yield 'empty value' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '<f:min value="{value}" />',
            'expectation' => null,
        ];
        yield 'empty value inline' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '{value -> f:min()}',
            'expectation' => null,
        ];
        yield 'single item' => [
            'arguments' => [
                'value' => [1],
            ],
            'src' => '<f:min value="{value}" />',
            'expectation' => 1,
        ];
        yield 'multiple items string' => [
            'arguments' => [
                'value' => ['first', 'second', 'third'],
            ],
            'src' => '<f:min value="{value}" />',
            'expectation' => 'first',
        ];
        yield 'multiple items numbers' => [
            'arguments' => [
                'value' => [0, 8, 15, 47, 11],
            ],
            'src' => '<f:min value="{value}" />',
            'expectation' => 0,
        ];
        yield 'value inline as iterable' => [
            'arguments' => [
                'value' => new IterableExample(['first', 'second', 'third']),
            ],
            'src' => '{value -> f:min()}',
            'expectation' => 'first',
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
            'src' => '<f:min>SOME TEXT</f:min>',
        ];

        yield 'invalid string inline' => [
            'arguments' => [
                'value' => 'string',
            ],
            'src' => '{value -> f:min()}',
        ];
        yield 'arrayaccess inline' => [
            'arguments' => [
                'value' => new ArrayAccessExample(['foo' => 'bar']),
            ],
            'src' => '{value -> f:min()}',
        ];
    }

    #[DataProvider('renderInvalidDataProvider')]
    #[Test]
    public function renderInvalid(array $arguments, string $src): void
    {
        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionCode(1756016877);

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
        $minNumericKeys = static function (): \Generator {
            yield from [2, 1];
            yield from [4, 3];
        };
        $minStringKeys = static function (): \Generator {
            yield from ['a' => 2, 'b' => 1];
            yield from ['a' => 4, 'b' => 3];
        };
        $minComposed = static function (): \Generator {
            yield from (static function (): \Generator {
                yield 7;
                yield 'k' => 1;
            })();
            yield from [3, 5];
            yield 'k' => 9;
        };
        yield 'repeated numeric keys' => [
            'valueFactory' => $minNumericKeys,
            'src' => '{value -> f:min()}',
            'expectation' => 1,
        ];
        yield 'repeated string keys' => [
            'valueFactory' => $minStringKeys,
            'src' => '{value -> f:min()}',
            'expectation' => 1,
        ];
        yield 'composed generators' => [
            'valueFactory' => $minComposed,
            'src' => '<f:min value="{value}" />',
            'expectation' => 1,
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
