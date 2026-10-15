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
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\UserWithoutToString;
use TYPO3Fluid\Fluid\View\TemplateView;

final class FilterViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderValidDataProvider(): \Generator
    {
        $news = [
            ['title' => 'A', 'top' => true],
            ['title' => 'B', 'top' => false],
            ['title' => 'C', 'top' => true],
        ];
        $anna = new UserWithoutToString('Anna');
        $nobody = new UserWithoutToString('');
        $object = new \stdClass();
        $emptyCountable = new \ArrayObject();
        $countable = new \ArrayObject(['a']);
        yield 'empty array' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '<f:array.filter value="{value}" />',
            'expectation' => [],
        ];
        yield 'empty array inline' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '{value -> f:array.filter()}',
            'expectation' => [],
        ];
        yield 'empty values are removed' => [
            'arguments' => [
                'value' => ['a', '', null, 'b', 0, false, [], '0'],
            ],
            'src' => '<f:array.filter value="{value}" />',
            'expectation' => ['a', 'b'],
        ];
        yield 'empty values are removed inline' => [
            'arguments' => [
                'value' => ['a', '', null, 'b', 0],
            ],
            'src' => '{value -> f:array.filter()}',
            'expectation' => ['a', 'b'],
        ];
        yield 'values are evaluated like conditions' => [
            'arguments' => [
                'value' => ['false', 'FALSE', '0.0', 0.0, $emptyCountable, 'true', 1, -1, 0.5, [0], $countable, $object],
            ],
            'src' => '<f:array.filter value="{value}" />',
            'expectation' => ['true', 1, -1, 0.5, [0], $countable, $object],
        ];
        yield 'keys are reset by default' => [
            'arguments' => [
                'value' => ['a' => 'x', 'b' => '', 'c' => 'y'],
            ],
            'src' => '<f:array.filter value="{value}" />',
            'expectation' => ['x', 'y'],
        ];
        yield 'preserveKeys disabled' => [
            'arguments' => [
                'value' => ['a' => 'x', 'b' => '', 'c' => 'y'],
            ],
            'src' => '<f:array.filter value="{value}" preserveKeys="false" />',
            'expectation' => ['x', 'y'],
        ];
        yield 'preserveKeys enabled' => [
            'arguments' => [
                'value' => ['a' => 'x', 'b' => '', 'c' => 'y'],
            ],
            'src' => '<f:array.filter value="{value}" preserveKeys="true" />',
            'expectation' => ['a' => 'x', 'c' => 'y'],
        ];
        yield 'preserveKeys enabled inline' => [
            'arguments' => [
                'value' => ['x', '', 'y'],
            ],
            'src' => '{value -> f:array.filter(preserveKeys: true)}',
            'expectation' => [0 => 'x', 2 => 'y'],
        ];
        yield 'property of arrays' => [
            'arguments' => [
                'value' => $news,
            ],
            'src' => '<f:array.filter value="{value}" property="top" />',
            'expectation' => [$news[0], $news[2]],
        ];
        yield 'property of arrays inline' => [
            'arguments' => [
                'value' => $news,
            ],
            'src' => '{value -> f:array.filter(property: \'top\')}',
            'expectation' => [$news[0], $news[2]],
        ];
        yield 'property with preserveKeys enabled' => [
            'arguments' => [
                'value' => $news,
            ],
            'src' => '<f:array.filter value="{value}" property="top" preserveKeys="true" />',
            'expectation' => [0 => $news[0], 2 => $news[2]],
        ];
        yield 'property of objects with getters' => [
            'arguments' => [
                'value' => [$anna, $nobody],
            ],
            'src' => '<f:array.filter value="{value}" property="name" />',
            'expectation' => [$anna],
        ];
        yield 'property of objects with is methods' => [
            'arguments' => [
                'value' => [$nobody, $anna],
            ],
            'src' => '<f:array.filter value="{value}" property="named" />',
            'expectation' => [$anna],
        ];
        yield 'nested property path' => [
            'arguments' => [
                'value' => [['author' => $anna], ['author' => $nobody], ['author' => ['name' => 'Ben']]],
            ],
            'src' => '<f:array.filter value="{value}" property="author.name" />',
            'expectation' => [['author' => $anna], ['author' => ['name' => 'Ben']]],
        ];
        yield 'missing property removes item' => [
            'arguments' => [
                'value' => [['top' => true], ['title' => 'B']],
            ],
            'src' => '<f:array.filter value="{value}" property="top" />',
            'expectation' => [['top' => true]],
        ];
        yield 'property with empty countable' => [
            'arguments' => [
                'value' => [['tags' => $emptyCountable], ['tags' => $countable]],
            ],
            'src' => '<f:array.filter value="{value}" property="tags" />',
            'expectation' => [['tags' => $countable]],
        ];
        yield 'array with inline array' => [
            'arguments' => [],
            'src' => '<f:array.filter value="{0: \'a\', 1: \'\', 2: \'b\'}" />',
            'expectation' => ['a', 'b'],
        ];
        yield 'value as tag content' => [
            'arguments' => [
                'value' => ['a', '', 'b'],
            ],
            'src' => '<f:array.filter>{value}</f:array.filter>',
            'expectation' => ['a', 'b'],
        ];
        yield 'value inline as iterable' => [
            'arguments' => [
                'value' => new IterableExample(['a', '', 'b']),
            ],
            'src' => '{value -> f:array.filter()}',
            'expectation' => ['a', 'b'],
        ];
        yield 'value inline and argument' => [
            'arguments' => [
                'valueInline' => ['a', ''],
                'valueArgument' => ['', 'b'],
            ],
            'src' => '{valueInline -> f:array.filter(value: valueArgument)}',
            'expectation' => ['b'],
        ];
        yield 'used as iteration subject' => [
            'arguments' => [
                'value' => $news,
            ],
            'src' => '<f:for each="{value -> f:array.filter(property: \'top\')}" as="item">[{item.title}]</f:for>',
            'expectation' => '[A][C]',
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
            'src' => '{value -> f:array.filter()}',
            'expectation' => [2, 1, 4, 3],
        ];
        yield 'repeated string keys' => [
            'valueFactory' => $stringKeys,
            'src' => '{value -> f:array.filter()}',
            'expectation' => [2, 1, 4, 3],
        ];
        yield 'composed generators' => [
            'valueFactory' => $composed,
            'src' => '<f:array.filter value="{value}" />',
            'expectation' => ['a', 'b', 'c', 'd', 'e'],
        ];
        yield 'repeated keys with preserveKeys use the last item per key' => [
            'valueFactory' => $numericKeys,
            'src' => '{value -> f:array.filter(preserveKeys: true)}',
            'expectation' => [0 => 4, 1 => 3],
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
            'src' => '<f:array.filter>SOME TEXT</f:array.filter>',
            'exceptionCode' => 1792005210,
        ];
        yield 'invalid string attribute' => [
            'arguments' => [],
            'src' => '<f:array.filter value="string" />',
            'exceptionCode' => 1256475113,
        ];
        yield 'invalid string inline' => [
            'arguments' => [
                'value' => 'string',
            ],
            'src' => '{value -> f:array.filter()}',
            'exceptionCode' => 1792005210,
        ];
        yield 'invalid integer attribute' => [
            'arguments' => [
                'value' => 123,
            ],
            'src' => '<f:array.filter value="{value}" />',
            'exceptionCode' => 1256475113,
        ];
        yield 'undefined value' => [
            'arguments' => [],
            'src' => '<f:array.filter value="{undefinedVariable}" />',
            'exceptionCode' => 1792005210,
        ];
        yield 'arrayaccess inline' => [
            'arguments' => [
                'value' => new ArrayAccessExample(['foo' => 'bar']),
            ],
            'src' => '{value -> f:array.filter()}',
            'exceptionCode' => 1792005210,
        ];
        yield 'scalar items with property' => [
            'arguments' => [
                'value' => [['top' => true], 'B'],
            ],
            'src' => '<f:array.filter value="{value}" property="top" />',
            'exceptionCode' => 1792005211,
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
