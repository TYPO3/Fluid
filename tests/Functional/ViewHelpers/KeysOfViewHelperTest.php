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
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\Objects\WithEverything;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\Objects\WithProperties;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\UserWithoutToString;
use TYPO3Fluid\Fluid\View\TemplateView;

final class KeysOfViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderValidDataProvider(): \Generator
    {
        yield 'empty array' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '<f:keysOf value="{value}" />',
            'expectation' => [],
        ];
        yield 'empty array inline' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '{value -> f:keysOf()}',
            'expectation' => [],
        ];
        yield 'numeric array' => [
            'arguments' => [
                'value' => ['a', 'b', 'c'],
            ],
            'src' => '<f:keysOf value="{value}" />',
            'expectation' => [0, 1, 2],
        ];
        yield 'associative array' => [
            'arguments' => [
                'value' => ['title' => 'Fluid', 'version' => 5],
            ],
            'src' => '<f:keysOf value="{value}" />',
            'expectation' => ['title', 'version'],
        ];
        yield 'associative array inline' => [
            'arguments' => [
                'value' => ['title' => 'Fluid', 'version' => 5],
            ],
            'src' => '{value -> f:keysOf()}',
            'expectation' => ['title', 'version'],
        ];
        yield 'mixed keys' => [
            'arguments' => [
                'value' => [5 => 'a', 'b' => 'b', 10 => 'c'],
            ],
            'src' => '<f:keysOf value="{value}" />',
            'expectation' => [5, 'b', 10],
        ];
        yield 'array with inline array' => [
            'arguments' => [],
            'src' => '<f:keysOf value="{title: \'Fluid\', version: 5}" />',
            'expectation' => ['title', 'version'],
        ];
        yield 'value as tag content' => [
            'arguments' => [
                'value' => ['a' => 1, 'b' => 2],
            ],
            'src' => '<f:keysOf>{value}</f:keysOf>',
            'expectation' => ['a', 'b'],
        ];
        yield 'value inline as iterable' => [
            'arguments' => [
                'value' => new IterableExample(['a' => 1, 'b' => 2]),
            ],
            'src' => '{value -> f:keysOf()}',
            'expectation' => ['a', 'b'],
        ];
        yield 'value inline and argument' => [
            'arguments' => [
                'valueInline' => ['a' => 1],
                'valueArgument' => ['b' => 2],
            ],
            'src' => '{valueInline -> f:keysOf(value: valueArgument)}',
            'expectation' => ['b'],
        ];
        yield 'keys joined' => [
            'arguments' => [
                'value' => ['title' => 'Fluid', 'version' => 5],
            ],
            'src' => '{value -> f:keysOf() -> f:join(separator: \', \')}',
            'expectation' => 'title, version',
        ];
        yield 'object with getters' => [
            'arguments' => [
                'value' => new WithEverything(),
            ],
            'src' => '<f:keysOf value="{value}" />',
            'expectation' => ['privateValue', 'protectedValue', 'publicValue'],
        ];
        yield 'object with getters inline' => [
            'arguments' => [
                'value' => new WithEverything(),
            ],
            'src' => '{value -> f:keysOf()}',
            'expectation' => ['privateValue', 'protectedValue', 'publicValue'],
        ];
        yield 'object without getters only has public properties' => [
            'arguments' => [
                'value' => new WithProperties(),
            ],
            'src' => '{value -> f:keysOf()}',
            'expectation' => ['publicValue'],
        ];
        yield 'object with is and has methods' => [
            'arguments' => [
                'value' => new class () {
                    public static int $instances = 0;
                    public string $uninitialized;
                    protected bool $active = true;
                    protected array $children = [];
                    protected string $internal = 'internal';

                    public function isActive(): bool
                    {
                        return $this->active;
                    }

                    public function hasChildren(): bool
                    {
                        return $this->children !== [];
                    }
                },
            ],
            'src' => '{value -> f:keysOf()}',
            'expectation' => ['active', 'children'],
        ];
        yield 'object with inherited properties' => [
            'arguments' => [
                'value' => new class ('Anna') extends UserWithoutToString {},
            ],
            'src' => '{value -> f:keysOf()}',
            'expectation' => ['name'],
        ];
        yield 'object with dynamic properties' => [
            'arguments' => [
                'value' => (object)['title' => 'Fluid', 'version' => 5],
            ],
            'src' => '{value -> f:keysOf()}',
            'expectation' => ['title', 'version'],
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
            'src' => '{value -> f:keysOf()}',
            'expectation' => [0, 1, 0, 1],
        ];
        yield 'repeated string keys' => [
            'valueFactory' => $stringKeys,
            'src' => '{value -> f:keysOf()}',
            'expectation' => ['a', 'b', 'a', 'b'],
        ];
        yield 'composed generators' => [
            'valueFactory' => $composed,
            'src' => '<f:keysOf value="{value}" />',
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
            'src' => '<f:keysOf>SOME TEXT</f:keysOf>',
            'exceptionCode' => 1792005150,
        ];
        yield 'invalid string attribute' => [
            'arguments' => [],
            'src' => '<f:keysOf value="string" />',
            'exceptionCode' => 1792005150,
        ];
        yield 'invalid string inline' => [
            'arguments' => [
                'value' => 'string',
            ],
            'src' => '{value -> f:keysOf()}',
            'exceptionCode' => 1792005150,
        ];
        yield 'invalid integer attribute' => [
            'arguments' => [
                'value' => 123,
            ],
            'src' => '<f:keysOf value="{value}" />',
            'exceptionCode' => 1792005150,
        ];
        yield 'undefined value' => [
            'arguments' => [],
            'src' => '<f:keysOf value="{undefinedVariable}" />',
            'exceptionCode' => 1792005150,
        ];
        yield 'arrayaccess inline' => [
            'arguments' => [
                'value' => new ArrayAccessExample(['foo' => 'bar']),
            ],
            'src' => '{value -> f:keysOf()}',
            'exceptionCode' => 1794819223,
        ];
        yield 'arrayaccess attribute' => [
            'arguments' => [
                'value' => new ArrayAccessExample(['foo' => 'bar']),
            ],
            'src' => '<f:keysOf value="{value}" />',
            'exceptionCode' => 1794819223,
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
