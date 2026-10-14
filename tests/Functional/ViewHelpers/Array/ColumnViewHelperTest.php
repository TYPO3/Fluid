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
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\UserWithToString;
use TYPO3Fluid\Fluid\View\TemplateView;

final class ColumnViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderValidDataProvider(): \Generator
    {
        $users = [
            ['id' => 3, 'name' => 'Anna'],
            ['id' => 7, 'name' => 'Ben'],
        ];
        yield 'empty array' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '<f:array.column value="{value}" property="name" />',
            'expectation' => [],
        ];
        yield 'empty array inline' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '{value -> f:array.column(property: \'name\')}',
            'expectation' => [],
        ];
        yield 'array of arrays' => [
            'arguments' => [
                'value' => $users,
            ],
            'src' => '<f:array.column value="{value}" property="name" />',
            'expectation' => ['Anna', 'Ben'],
        ];
        yield 'array of arrays inline' => [
            'arguments' => [
                'value' => $users,
            ],
            'src' => '{value -> f:array.column(property: \'name\')}',
            'expectation' => ['Anna', 'Ben'],
        ];
        yield 'keys of the original array are not preserved' => [
            'arguments' => [
                'value' => ['first' => ['name' => 'Anna'], 'second' => ['name' => 'Ben']],
            ],
            'src' => '<f:array.column value="{value}" property="name" />',
            'expectation' => ['Anna', 'Ben'],
        ];
        yield 'objects with getters' => [
            'arguments' => [
                'value' => [new UserWithoutToString('Anna'), new UserWithoutToString('Ben')],
            ],
            'src' => '<f:array.column value="{value}" property="name" />',
            'expectation' => ['Anna', 'Ben'],
        ];
        yield 'objects with is methods' => [
            'arguments' => [
                'value' => [new UserWithoutToString('Anna'), new UserWithoutToString('')],
            ],
            'src' => '<f:array.column value="{value}" property="named" />',
            'expectation' => [true, false],
        ];
        yield 'objects with public properties' => [
            'arguments' => [
                'value' => [(object)['name' => 'Anna'], (object)['name' => 'Ben']],
            ],
            'src' => '<f:array.column value="{value}" property="name" />',
            'expectation' => ['Anna', 'Ben'],
        ];
        yield 'nested property path' => [
            'arguments' => [
                'value' => [
                    ['author' => new UserWithoutToString('Anna')],
                    ['author' => ['name' => 'Ben']],
                ],
            ],
            'src' => '<f:array.column value="{value}" property="author.name" />',
            'expectation' => ['Anna', 'Ben'],
        ];
        yield 'missing property results in null' => [
            'arguments' => [
                'value' => [['name' => 'Anna'], ['id' => 7]],
            ],
            'src' => '<f:array.column value="{value}" property="name" />',
            'expectation' => ['Anna', null],
        ];
        yield 'property values are not modified' => [
            'arguments' => [
                'value' => [['data' => [1, 2]], ['data' => 3.5], ['data' => false]],
            ],
            'src' => '<f:array.column value="{value}" property="data" />',
            'expectation' => [[1, 2], 3.5, false],
        ];
        yield 'indexProperty' => [
            'arguments' => [
                'value' => $users,
            ],
            'src' => '<f:array.column value="{value}" property="name" indexProperty="id" />',
            'expectation' => [3 => 'Anna', 7 => 'Ben'],
        ];
        yield 'indexProperty inline' => [
            'arguments' => [
                'value' => $users,
            ],
            'src' => '{value -> f:array.column(property: \'name\', indexProperty: \'id\')}',
            'expectation' => [3 => 'Anna', 7 => 'Ben'],
        ];
        yield 'indexProperty with string values' => [
            'arguments' => [
                'value' => [['code' => 'de', 'label' => 'German'], ['code' => 'en', 'label' => 'English']],
            ],
            'src' => '<f:array.column value="{value}" property="label" indexProperty="code" />',
            'expectation' => ['de' => 'German', 'en' => 'English'],
        ];
        yield 'indexProperty with stringable objects' => [
            'arguments' => [
                'value' => [
                    ['author' => new UserWithToString('Anna'), 'title' => 'First'],
                    ['author' => new UserWithToString('Ben'), 'title' => 'Second'],
                ],
            ],
            'src' => '<f:array.column value="{value}" property="title" indexProperty="author" />',
            'expectation' => ['Anna' => 'First', 'Ben' => 'Second'],
        ];
        yield 'indexProperty with missing values appends items' => [
            'arguments' => [
                'value' => [['id' => 3, 'name' => 'Anna'], ['name' => 'Ben']],
            ],
            'src' => '<f:array.column value="{value}" property="name" indexProperty="id" />',
            'expectation' => [3 => 'Anna', 4 => 'Ben'],
        ];
        yield 'indexProperty with duplicate values uses last item' => [
            'arguments' => [
                'value' => [['id' => 3, 'name' => 'Anna'], ['id' => 3, 'name' => 'Ben']],
            ],
            'src' => '<f:array.column value="{value}" property="name" indexProperty="id" />',
            'expectation' => [3 => 'Ben'],
        ];
        yield 'undefined indexProperty variable' => [
            'arguments' => [
                'value' => $users,
            ],
            'src' => '<f:array.column value="{value}" property="name" indexProperty="{undefinedVariable}" />',
            'expectation' => ['Anna', 'Ben'],
        ];
        yield 'array with inline array' => [
            'arguments' => [],
            'src' => '<f:array.column value="{0: {name: \'Anna\'}, 1: {name: \'Ben\'}}" property="name" />',
            'expectation' => ['Anna', 'Ben'],
        ];
        yield 'value as tag content' => [
            'arguments' => [
                'value' => $users,
            ],
            'src' => '<f:array.column property="name">{value}</f:array.column>',
            'expectation' => ['Anna', 'Ben'],
        ];
        yield 'value inline as iterable' => [
            'arguments' => [
                'value' => new IterableExample($users),
            ],
            'src' => '{value -> f:array.column(property: \'name\')}',
            'expectation' => ['Anna', 'Ben'],
        ];
        yield 'value inline and argument' => [
            'arguments' => [
                'valueInline' => [['name' => 'Anna']],
                'valueArgument' => [['name' => 'Ben']],
            ],
            'src' => '{valueInline -> f:array.column(value: valueArgument, property: \'name\')}',
            'expectation' => ['Ben'],
        ];
        yield 'values joined' => [
            'arguments' => [
                'value' => $users,
            ],
            'src' => '{value -> f:array.column(property: \'name\') -> f:join(separator: \', \')}',
            'expectation' => 'Anna, Ben',
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
            'src' => '<f:array.column property="name">SOME TEXT</f:array.column>',
            'exceptionCode' => 1792005200,
        ];
        yield 'invalid string attribute' => [
            'arguments' => [],
            'src' => '<f:array.column value="string" property="name" />',
            'exceptionCode' => 1256475113,
        ];
        yield 'invalid string inline' => [
            'arguments' => [
                'value' => 'string',
            ],
            'src' => '{value -> f:array.column(property: \'name\')}',
            'exceptionCode' => 1792005200,
        ];
        yield 'invalid integer attribute' => [
            'arguments' => [
                'value' => 123,
            ],
            'src' => '<f:array.column value="{value}" property="name" />',
            'exceptionCode' => 1256475113,
        ];
        yield 'undefined value' => [
            'arguments' => [],
            'src' => '<f:array.column value="{undefinedVariable}" property="name" />',
            'exceptionCode' => 1792005200,
        ];
        yield 'arrayaccess inline' => [
            'arguments' => [
                'value' => new ArrayAccessExample(['foo' => 'bar']),
            ],
            'src' => '{value -> f:array.column(property: \'name\')}',
            'exceptionCode' => 1792005200,
        ];
        yield 'scalar items' => [
            'arguments' => [
                'value' => [['name' => 'Anna'], 'Ben'],
            ],
            'src' => '<f:array.column value="{value}" property="name" />',
            'exceptionCode' => 1792005201,
        ];
        yield 'indexProperty with array values' => [
            'arguments' => [
                'value' => [['id' => [3], 'name' => 'Anna']],
            ],
            'src' => '<f:array.column value="{value}" property="name" indexProperty="id" />',
            'exceptionCode' => 1792005202,
        ];
        yield 'indexProperty with float values' => [
            'arguments' => [
                'value' => [['id' => 3.5, 'name' => 'Anna']],
            ],
            'src' => '<f:array.column value="{value}" property="name" indexProperty="id" />',
            'exceptionCode' => 1792005202,
        ];
        yield 'indexProperty with non-stringable objects' => [
            'arguments' => [
                'value' => [['author' => new UserWithoutToString('Anna'), 'title' => 'First']],
            ],
            'src' => '<f:array.column value="{value}" property="title" indexProperty="author" />',
            'exceptionCode' => 1792005202,
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
