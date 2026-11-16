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

final class SliceViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderValidDataProvider(): \Generator
    {
        $items = ['a', 'b', 'c', 'd', 'e'];
        yield 'empty array' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '<f:slice value="{value}" length="3" />',
            'expectation' => [],
        ];
        yield 'empty array inline' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '{value -> f:slice(length: 3)}',
            'expectation' => [],
        ];
        yield 'without offset and length' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:slice value="{value}" />',
            'expectation' => ['a', 'b', 'c', 'd', 'e'],
        ];
        yield 'length only' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:slice value="{value}" length="3" />',
            'expectation' => ['a', 'b', 'c'],
        ];
        yield 'length only inline' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '{value -> f:slice(length: 3)}',
            'expectation' => ['a', 'b', 'c'],
        ];
        yield 'offset only' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:slice value="{value}" offset="1" />',
            'expectation' => ['b', 'c', 'd', 'e'],
        ];
        yield 'offset and length' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:slice value="{value}" offset="1" length="2" />',
            'expectation' => ['b', 'c'],
        ];
        yield 'offset and length inline' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '{value -> f:slice(offset: 1, length: 2)}',
            'expectation' => ['b', 'c'],
        ];
        yield 'negative offset' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:slice value="{value}" offset="-2" />',
            'expectation' => ['d', 'e'],
        ];
        yield 'negative length' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:slice value="{value}" offset="1" length="-1" />',
            'expectation' => ['b', 'c', 'd'],
        ];
        yield 'length of zero' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:slice value="{value}" length="0" />',
            'expectation' => [],
        ];
        yield 'length larger than array' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:slice value="{value}" length="10" />',
            'expectation' => ['a', 'b', 'c', 'd', 'e'],
        ];
        yield 'offset larger than array' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:slice value="{value}" offset="10" />',
            'expectation' => [],
        ];
        yield 'offset and length as variables' => [
            'arguments' => [
                'value' => $items,
                'offset' => 2,
                'length' => 2,
            ],
            'src' => '<f:slice value="{value}" offset="{offset}" length="{length}" />',
            'expectation' => ['c', 'd'],
        ];
        yield 'undefined length variable' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:slice value="{value}" offset="3" length="{undefinedVariable}" />',
            'expectation' => ['d', 'e'],
        ];
        yield 'numeric keys are reset by default' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:slice value="{value}" offset="2" length="2" />',
            'expectation' => [0 => 'c', 1 => 'd'],
        ];
        yield 'preserveKeys enabled' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:slice value="{value}" offset="2" length="2" preserveKeys="{true}" />',
            'expectation' => [2 => 'c', 3 => 'd'],
        ];
        yield 'preserveKeys enabled inline' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '{value -> f:slice(offset: 2, length: 2, preserveKeys: true)}',
            'expectation' => [2 => 'c', 3 => 'd'],
        ];
        yield 'associative array keeps string keys' => [
            'arguments' => [
                'value' => ['a' => 1, 'b' => 2, 'c' => 3],
            ],
            'src' => '<f:slice value="{value}" offset="1" />',
            'expectation' => ['b' => 2, 'c' => 3],
        ];
        yield 'array with inline array' => [
            'arguments' => [],
            'src' => '<f:slice value="{0: \'a\', 1: \'b\', 2: \'c\'}" length="2" />',
            'expectation' => ['a', 'b'],
        ];
        yield 'value as tag content' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:slice length="2">{value}</f:slice>',
            'expectation' => ['a', 'b'],
        ];
        yield 'value inline as iterable' => [
            'arguments' => [
                'value' => new IterableExample($items),
            ],
            'src' => '{value -> f:slice(length: 2)}',
            'expectation' => ['a', 'b'],
        ];
        yield 'value inline and argument' => [
            'arguments' => [
                'valueInline' => ['a', 'b', 'c'],
                'valueArgument' => ['d', 'e', 'f'],
            ],
            'src' => '{valueInline -> f:slice(value: valueArgument, length: 2)}',
            'expectation' => ['d', 'e'],
        ];
        yield 'used as iteration subject' => [
            'arguments' => [
                'value' => $items,
            ],
            'src' => '<f:for each="{value -> f:slice(length: 3)}" as="item">[{item}]</f:for>',
            'expectation' => '[a][b][c]',
        ];
        yield 'empty string' => [
            'arguments' => [
                'value' => '',
            ],
            'src' => '<f:slice value="{value}" length="3" />',
            'expectation' => '',
        ];
        yield 'string with length only' => [
            'arguments' => [
                'value' => 'Hello World',
            ],
            'src' => '<f:slice value="{value}" length="5" />',
            'expectation' => 'Hello',
        ];
        yield 'string with length only inline' => [
            'arguments' => [
                'value' => 'Hello World',
            ],
            'src' => '{value -> f:slice(length: 5)}',
            'expectation' => 'Hello',
        ];
        yield 'string with offset only' => [
            'arguments' => [
                'value' => 'Hello World',
            ],
            'src' => '<f:slice value="{value}" offset="6" />',
            'expectation' => 'World',
        ];
        yield 'string with offset and length' => [
            'arguments' => [
                'value' => 'Hello World',
            ],
            'src' => '<f:slice value="{value}" offset="1" length="3" />',
            'expectation' => 'ell',
        ];
        yield 'string with negative offset' => [
            'arguments' => [
                'value' => 'Hello World',
            ],
            'src' => '<f:slice value="{value}" offset="-5" />',
            'expectation' => 'World',
        ];
        yield 'string with negative length' => [
            'arguments' => [
                'value' => 'Hello World',
            ],
            'src' => '<f:slice value="{value}" length="-6" />',
            'expectation' => 'Hello',
        ];
        yield 'string with offset larger than string' => [
            'arguments' => [
                'value' => 'Hello World',
            ],
            'src' => '<f:slice value="{value}" offset="20" />',
            'expectation' => '',
        ];
        yield 'string with multibyte characters' => [
            'arguments' => [
                'value' => 'Grüße aus Köln',
            ],
            'src' => '<f:slice value="{value}" offset="-4" />',
            'expectation' => 'Köln',
        ];
        yield 'string ignores preserveKeys' => [
            'arguments' => [
                'value' => 'Hello World',
            ],
            'src' => '<f:slice value="{value}" length="5" preserveKeys="{true}" />',
            'expectation' => 'Hello',
        ];
        yield 'string attribute' => [
            'arguments' => [],
            'src' => '<f:slice value="Hello World" length="5" />',
            'expectation' => 'Hello',
        ];
        yield 'string as tag content' => [
            'arguments' => [],
            'src' => '<f:slice length="5">Hello World</f:slice>',
            'expectation' => 'Hello',
        ];
        yield 'string is escaped once' => [
            'arguments' => [
                'value' => 'Tom & Jerry',
            ],
            'src' => '{value -> f:slice(length: 5)}',
            'expectation' => 'Tom &amp;',
        ];
        yield 'string as tag content is escaped once' => [
            'arguments' => [
                'value' => '<b>Tom</b>',
            ],
            'src' => '<f:slice offset="3" length="3">{value}</f:slice>',
            'expectation' => 'Tom',
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
        yield 'invalid integer attribute' => [
            'arguments' => [
                'value' => 123,
            ],
            'src' => '<f:slice value="{value}" length="2" />',
            'exceptionCode' => 1792005170,
        ];
        yield 'invalid integer inline' => [
            'arguments' => [
                'value' => 123,
            ],
            'src' => '{value -> f:slice(length: 2)}',
            'exceptionCode' => 1792005170,
        ];
        yield 'undefined value' => [
            'arguments' => [],
            'src' => '<f:slice value="{undefinedVariable}" length="2" />',
            'exceptionCode' => 1792005170,
        ];
        yield 'arrayaccess inline' => [
            'arguments' => [
                'value' => new ArrayAccessExample(['foo' => 'bar']),
            ],
            'src' => '{value -> f:slice(length: 2)}',
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
