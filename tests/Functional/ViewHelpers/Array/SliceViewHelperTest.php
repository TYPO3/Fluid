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

final class SliceViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderValidDataProvider(): \Generator
    {
        $items = ['a', 'b', 'c', 'd', 'e'];
        yield 'empty array' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '<f:array.slice value="{value}" length="3" />',
            'expectation' => [],
        ];
        yield 'empty array inline' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '{value -> f:array.slice(length: 3)}',
            'expectation' => [],
        ];
        yield 'without offset and length' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:array.slice value="{value}" />',
            'expectation' => ['a', 'b', 'c', 'd', 'e'],
        ];
        yield 'length only' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:array.slice value="{value}" length="3" />',
            'expectation' => ['a', 'b', 'c'],
        ];
        yield 'length only inline' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '{value -> f:array.slice(length: 3)}',
            'expectation' => ['a', 'b', 'c'],
        ];
        yield 'offset only' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:array.slice value="{value}" offset="1" />',
            'expectation' => ['b', 'c', 'd', 'e'],
        ];
        yield 'offset and length' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:array.slice value="{value}" offset="1" length="2" />',
            'expectation' => ['b', 'c'],
        ];
        yield 'offset and length inline' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '{value -> f:array.slice(offset: 1, length: 2)}',
            'expectation' => ['b', 'c'],
        ];
        yield 'negative offset' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:array.slice value="{value}" offset="-2" />',
            'expectation' => ['d', 'e'],
        ];
        yield 'negative length' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:array.slice value="{value}" offset="1" length="-1" />',
            'expectation' => ['b', 'c', 'd'],
        ];
        yield 'length of zero' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:array.slice value="{value}" length="0" />',
            'expectation' => [],
        ];
        yield 'length larger than array' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:array.slice value="{value}" length="10" />',
            'expectation' => ['a', 'b', 'c', 'd', 'e'],
        ];
        yield 'offset larger than array' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:array.slice value="{value}" offset="10" />',
            'expectation' => [],
        ];
        yield 'offset and length as variables' => [
            'arguments' => [
                'value' => $items,
                'offset' => 2,
                'length' => 2,
            ],
            'src' => '<f:array.slice value="{value}" offset="{offset}" length="{length}" />',
            'expectation' => ['c', 'd'],
        ];
        yield 'undefined length variable' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:array.slice value="{value}" offset="3" length="{undefinedVariable}" />',
            'expectation' => ['d', 'e'],
        ];
        yield 'numeric keys are reset by default' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:array.slice value="{value}" offset="2" length="2" />',
            'expectation' => [0 => 'c', 1 => 'd'],
        ];
        yield 'preserveKeys enabled' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:array.slice value="{value}" offset="2" length="2" preserveKeys="true" />',
            'expectation' => [2 => 'c', 3 => 'd'],
        ];
        yield 'preserveKeys enabled inline' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '{value -> f:array.slice(offset: 2, length: 2, preserveKeys: true)}',
            'expectation' => [2 => 'c', 3 => 'd'],
        ];
        yield 'associative array keeps string keys' => [
            'arguments' => [
                'value' => ['a' => 1, 'b' => 2, 'c' => 3],
            ],
            'src' => '<f:array.slice value="{value}" offset="1" />',
            'expectation' => ['b' => 2, 'c' => 3],
        ];
        yield 'array with inline array' => [
            'arguments' => [],
            'src' => '<f:array.slice value="{0: \'a\', 1: \'b\', 2: \'c\'}" length="2" />',
            'expectation' => ['a', 'b'],
        ];
        yield 'value as tag content' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:array.slice length="2">{value}</f:array.slice>',
            'expectation' => ['a', 'b'],
        ];
        yield 'value inline as iterable' => [
            'arguments' => [
                'value' => new IterableExample($items),
            ],
            'src' => '{value -> f:array.slice(length: 2)}',
            'expectation' => ['a', 'b'],
        ];
        yield 'value inline and argument' => [
            'arguments' => [
                'valueInline' => ['a', 'b', 'c'],
                'valueArgument' => ['d', 'e', 'f'],
            ],
            'src' => '{valueInline -> f:array.slice(value: valueArgument, length: 2)}',
            'expectation' => ['d', 'e'],
        ];
        yield 'used as iteration subject' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:for each="{value -> f:array.slice(length: 3)}" as="item">[{item}]</f:for>',
            'expectation' => '[a][b][c]',
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
            'src' => '<f:array.slice length="2">SOME TEXT</f:array.slice>',
            'exceptionCode' => 1792005170,
        ];
        yield 'invalid string attribute' => [
            'arguments' => [],
            'src' => '<f:array.slice value="string" length="2" />',
            'exceptionCode' => 1256475113,
        ];
        yield 'invalid string inline' => [
            'arguments' => [
                'value' => 'string',
            ],
            'src' => '{value -> f:array.slice(length: 2)}',
            'exceptionCode' => 1792005170,
        ];
        yield 'invalid integer attribute' => [
            'arguments' => [
                'value' => 123,
            ],
            'src' => '<f:array.slice value="{value}" length="2" />',
            'exceptionCode' => 1256475113,
        ];
        yield 'undefined value' => [
            'arguments' => [],
            'src' => '<f:array.slice value="{undefinedVariable}" length="2" />',
            'exceptionCode' => 1792005170,
        ];
        yield 'arrayaccess inline' => [
            'arguments' => [
                'value' => new ArrayAccessExample(['foo' => 'bar']),
            ],
            'src' => '{value -> f:array.slice(length: 2)}',
            'exceptionCode' => 1792005170,
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
