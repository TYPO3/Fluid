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

final class UniqueViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderValidDataProvider(): \Generator
    {
        $tags = ['php', 'fluid', 'php', 'typo3', 'fluid'];
        $object = new \stdClass();
        $equalObject = new \stdClass();
        $otherObject = (object)['title' => 'Fluid'];
        yield 'empty array' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '<f:array.unique value="{value}" />',
            'expectation' => [],
        ];
        yield 'empty array inline' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '{value -> f:array.unique()}',
            'expectation' => [],
        ];
        yield 'array without duplicates' => [
            'arguments' => [
                'value' => ['a', 'b', 'c'],
            ],
            'src' => '<f:array.unique value="{value}" />',
            'expectation' => ['a', 'b', 'c'],
        ];
        yield 'array with duplicates' => [
            'arguments' => [
                'value' => $tags,
            ],
            'src' => '<f:array.unique value="{value}" />',
            'expectation' => ['php', 'fluid', 'typo3'],
        ];
        yield 'array with duplicates inline' => [
            'arguments' => [
                'value' => $tags,
            ],
            'src' => '{value -> f:array.unique()}',
            'expectation' => ['php', 'fluid', 'typo3'],
        ];
        yield 'values are compared loosely' => [
            'arguments' => [
                'value' => [1, '1', 1.0, '01', 2],
            ],
            'src' => '<f:array.unique value="{value}" />',
            'expectation' => [1, 2],
        ];
        yield 'empty values are loosely equal' => [
            'arguments' => [
                'value' => ['', null, false],
            ],
            'src' => '<f:array.unique value="{value}" />',
            'expectation' => [''],
        ];
        yield 'strings are compared case-sensitively' => [
            'arguments' => [
                'value' => ['php', 'PHP', 'php'],
            ],
            'src' => '<f:array.unique value="{value}" />',
            'expectation' => ['php', 'PHP'],
        ];
        yield 'nested arrays' => [
            'arguments' => [
                'value' => [[1, 2], ['1', '2'], [2, 1]],
            ],
            'src' => '<f:array.unique value="{value}" />',
            'expectation' => [[1, 2], [2, 1]],
        ];
        yield 'same object instance' => [
            'arguments' => [
                'value' => [$object, $object],
            ],
            'src' => '<f:array.unique value="{value}" />',
            'expectation' => [$object],
        ];
        yield 'objects with equal properties' => [
            'arguments' => [
                'value' => [$object, $equalObject],
            ],
            'src' => '<f:array.unique value="{value}" />',
            'expectation' => [$object],
        ];
        yield 'objects with different properties' => [
            'arguments' => [
                'value' => [$object, $otherObject],
            ],
            'src' => '<f:array.unique value="{value}" />',
            'expectation' => [$object, $otherObject],
        ];
        yield 'keys are reset by default' => [
            'arguments' => [
                'value' => ['a' => 'x', 'b' => 'y', 'c' => 'x'],
            ],
            'src' => '<f:array.unique value="{value}" />',
            'expectation' => ['x', 'y'],
        ];
        yield 'preserveKeys disabled' => [
            'arguments' => [
                'value' => ['a' => 'x', 'b' => 'y', 'c' => 'x'],
            ],
            'src' => '<f:array.unique value="{value}" preserveKeys="false" />',
            'expectation' => ['x', 'y'],
        ];
        yield 'preserveKeys enabled' => [
            'arguments' => [
                'value' => ['a' => 'x', 'b' => 'y', 'c' => 'x'],
            ],
            'src' => '<f:array.unique value="{value}" preserveKeys="true" />',
            'expectation' => ['a' => 'x', 'b' => 'y'],
        ];
        yield 'preserveKeys enabled inline' => [
            'arguments' => [
                'value' => $tags,
            ],
            'src' => '{value -> f:array.unique(preserveKeys: true)}',
            'expectation' => [0 => 'php', 1 => 'fluid', 3 => 'typo3'],
        ];
        yield 'array with inline array' => [
            'arguments' => [],
            'src' => '<f:array.unique value="{0: \'a\', 1: \'b\', 2: \'a\'}" />',
            'expectation' => ['a', 'b'],
        ];
        yield 'value as tag content' => [
            'arguments' => [
                'value' => $tags,
            ],
            'src' => '<f:array.unique>{value}</f:array.unique>',
            'expectation' => ['php', 'fluid', 'typo3'],
        ];
        yield 'value inline as iterable' => [
            'arguments' => [
                'value' => new IterableExample($tags),
            ],
            'src' => '{value -> f:array.unique()}',
            'expectation' => ['php', 'fluid', 'typo3'],
        ];
        yield 'value inline and argument' => [
            'arguments' => [
                'valueInline' => ['a', 'a'],
                'valueArgument' => ['b', 'b'],
            ],
            'src' => '{valueInline -> f:array.unique(value: valueArgument)}',
            'expectation' => ['b'],
        ];
        yield 'unique values joined' => [
            'arguments' => [
                'value' => $tags,
            ],
            'src' => '{value -> f:array.unique() -> f:join(separator: \', \')}',
            'expectation' => 'php, fluid, typo3',
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
        $duplicateValues = static function (): \Generator {
            yield from [1, 2];
            yield from [2, 3];
        };
        yield 'repeated numeric keys' => [
            'valueFactory' => $numericKeys,
            'src' => '{value -> f:array.unique()}',
            'expectation' => [2, 1, 4, 3],
        ];
        yield 'repeated string keys' => [
            'valueFactory' => $stringKeys,
            'src' => '{value -> f:array.unique()}',
            'expectation' => [2, 1, 4, 3],
        ];
        yield 'composed generators' => [
            'valueFactory' => $composed,
            'src' => '<f:array.unique value="{value}" />',
            'expectation' => ['a', 'b', 'c', 'd', 'e'],
        ];
        yield 'duplicate values with repeated keys' => [
            'valueFactory' => $duplicateValues,
            'src' => '{value -> f:array.unique()}',
            'expectation' => [1, 2, 3],
        ];
        yield 'repeated keys with preserveKeys use the last item per key' => [
            'valueFactory' => $numericKeys,
            'src' => '{value -> f:array.unique(preserveKeys: true)}',
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
            'src' => '<f:array.unique>SOME TEXT</f:array.unique>',
            'exceptionCode' => 1792005180,
        ];
        yield 'invalid string attribute' => [
            'arguments' => [],
            'src' => '<f:array.unique value="string" />',
            'exceptionCode' => 1256475113,
        ];
        yield 'invalid string inline' => [
            'arguments' => [
                'value' => 'string',
            ],
            'src' => '{value -> f:array.unique()}',
            'exceptionCode' => 1792005180,
        ];
        yield 'invalid integer attribute' => [
            'arguments' => [
                'value' => 123,
            ],
            'src' => '<f:array.unique value="{value}" />',
            'exceptionCode' => 1256475113,
        ];
        yield 'undefined value' => [
            'arguments' => [],
            'src' => '<f:array.unique value="{undefinedVariable}" />',
            'exceptionCode' => 1792005180,
        ];
        yield 'arrayaccess inline' => [
            'arguments' => [
                'value' => new ArrayAccessExample(['foo' => 'bar']),
            ],
            'src' => '{value -> f:array.unique()}',
            'exceptionCode' => 1792005180,
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
