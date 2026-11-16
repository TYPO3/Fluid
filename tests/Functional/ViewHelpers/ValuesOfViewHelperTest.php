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
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\UserWithToString;
use TYPO3Fluid\Fluid\View\TemplateView;

final class ValuesOfViewHelperTest extends AbstractFunctionalTestCase
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
            'src' => '<f:valuesOf value="{value}" property="name" />',
            'expectation' => [],
        ];
        yield 'empty array inline' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '{value -> f:valuesOf(property: \'name\')}',
            'expectation' => [],
        ];
        yield 'array of arrays' => [
            'arguments' => [
                'value' => $users,
            ],
            'src' => '<f:valuesOf value="{value}" property="name" />',
            'expectation' => ['Anna', 'Ben'],
        ];
        yield 'array of arrays inline' => [
            'arguments' => [
                'value' => $users,
            ],
            'src' => '{value -> f:valuesOf(property: \'name\')}',
            'expectation' => ['Anna', 'Ben'],
        ];
        yield 'keys of the original array are not preserved' => [
            'arguments' => [
                'value' => ['first' => ['name' => 'Anna'], 'second' => ['name' => 'Ben']],
            ],
            'src' => '<f:valuesOf value="{value}" property="name" />',
            'expectation' => ['Anna', 'Ben'],
        ];
        yield 'objects with getters' => [
            'arguments' => [
                'value' => [new UserWithoutToString('Anna'), new UserWithoutToString('Ben')],
            ],
            'src' => '<f:valuesOf value="{value}" property="name" />',
            'expectation' => ['Anna', 'Ben'],
        ];
        yield 'objects with is methods' => [
            'arguments' => [
                'value' => [new UserWithoutToString('Anna'), new UserWithoutToString('')],
            ],
            'src' => '<f:valuesOf value="{value}" property="named" />',
            'expectation' => [true, false],
        ];
        yield 'objects with public properties' => [
            'arguments' => [
                'value' => [(object)['name' => 'Anna'], (object)['name' => 'Ben']],
            ],
            'src' => '<f:valuesOf value="{value}" property="name" />',
            'expectation' => ['Anna', 'Ben'],
        ];
        yield 'nested property path' => [
            'arguments' => [
                'value' => [
                    ['author' => new UserWithoutToString('Anna')],
                    ['author' => ['name' => 'Ben']],
                ],
            ],
            'src' => '<f:valuesOf value="{value}" property="author.name" />',
            'expectation' => ['Anna', 'Ben'],
        ];
        yield 'missing property results in null' => [
            'arguments' => [
                'value' => [['name' => 'Anna'], ['id' => 7]],
            ],
            'src' => '<f:valuesOf value="{value}" property="name" />',
            'expectation' => ['Anna', null],
        ];
        yield 'property values are not modified' => [
            'arguments' => [
                'value' => [['data' => [1, 2]], ['data' => 3.5], ['data' => false]],
            ],
            'src' => '<f:valuesOf value="{value}" property="data" />',
            'expectation' => [[1, 2], 3.5, false],
        ];
        yield 'indexProperty' => [
            'arguments' => [
                'value' => $users,
            ],
            'src' => '<f:valuesOf value="{value}" property="name" indexProperty="id" />',
            'expectation' => [3 => 'Anna', 7 => 'Ben'],
        ];
        yield 'indexProperty inline' => [
            'arguments' => [
                'value' => $users,
            ],
            'src' => '{value -> f:valuesOf(property: \'name\', indexProperty: \'id\')}',
            'expectation' => [3 => 'Anna', 7 => 'Ben'],
        ];
        yield 'indexProperty with string values' => [
            'arguments' => [
                'value' => [['code' => 'de', 'label' => 'German'], ['code' => 'en', 'label' => 'English']],
            ],
            'src' => '<f:valuesOf value="{value}" property="label" indexProperty="code" />',
            'expectation' => ['de' => 'German', 'en' => 'English'],
        ];
        yield 'indexProperty with stringable objects' => [
            'arguments' => [
                'value' => [
                    ['author' => new UserWithToString('Anna'), 'title' => 'First'],
                    ['author' => new UserWithToString('Ben'), 'title' => 'Second'],
                ],
            ],
            'src' => '<f:valuesOf value="{value}" property="title" indexProperty="author" />',
            'expectation' => ['Anna' => 'First', 'Ben' => 'Second'],
        ];
        yield 'indexProperty with missing values appends items' => [
            'arguments' => [
                'value' => [['id' => 3, 'name' => 'Anna'], ['name' => 'Ben']],
            ],
            'src' => '<f:valuesOf value="{value}" property="name" indexProperty="id" />',
            'expectation' => [3 => 'Anna', 4 => 'Ben'],
        ];
        yield 'indexProperty with duplicate values uses last item' => [
            'arguments' => [
                'value' => [['id' => 3, 'name' => 'Anna'], ['id' => 3, 'name' => 'Ben']],
            ],
            'src' => '<f:valuesOf value="{value}" property="name" indexProperty="id" />',
            'expectation' => [3 => 'Ben'],
        ];
        yield 'undefined indexProperty variable' => [
            'arguments' => [
                'value' => $users,
            ],
            'src' => '<f:valuesOf value="{value}" property="name" indexProperty="{undefinedVariable}" />',
            'expectation' => ['Anna', 'Ben'],
        ];
        yield 'array with inline array' => [
            'arguments' => [],
            'src' => '<f:valuesOf value="{0: {name: \'Anna\'}, 1: {name: \'Ben\'}}" property="name" />',
            'expectation' => ['Anna', 'Ben'],
        ];
        yield 'value as tag content' => [
            'arguments' => [
                'value' => $users,
            ],
            'src' => '<f:valuesOf property="name">{value}</f:valuesOf>',
            'expectation' => ['Anna', 'Ben'],
        ];
        yield 'value inline as iterable' => [
            'arguments' => [
                'value' => new IterableExample($users),
            ],
            'src' => '{value -> f:valuesOf(property: \'name\')}',
            'expectation' => ['Anna', 'Ben'],
        ];
        yield 'value inline and argument' => [
            'arguments' => [
                'valueInline' => [['name' => 'Anna']],
                'valueArgument' => [['name' => 'Ben']],
            ],
            'src' => '{valueInline -> f:valuesOf(value: valueArgument, property: \'name\')}',
            'expectation' => ['Ben'],
        ];
        yield 'values joined' => [
            'arguments' => [
                'value' => $users,
            ],
            'src' => '{value -> f:valuesOf(property: \'name\') -> f:join(separator: \', \')}',
            'expectation' => 'Anna, Ben',
        ];
        yield 'without property' => [
            'arguments' => [
                'value' => ['first' => 'Anna', 'second' => 'Ben'],
            ],
            'src' => '<f:valuesOf value="{value}" />',
            'expectation' => ['Anna', 'Ben'],
        ];
        yield 'without property inline' => [
            'arguments' => [
                'value' => ['first' => 'Anna', 'second' => 'Ben'],
            ],
            'src' => '{value -> f:valuesOf()}',
            'expectation' => ['Anna', 'Ben'],
        ];
        yield 'without property on an empty array' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '<f:valuesOf value="{value}" />',
            'expectation' => [],
        ];
        yield 'without property keeps items of any type' => [
            'arguments' => [
                'value' => [3 => 'Anna', 7 => 2, 9 => ['name' => 'Ben'], 11 => null],
            ],
            'src' => '<f:valuesOf value="{value}" />',
            'expectation' => ['Anna', 2, ['name' => 'Ben'], null],
        ];
        yield 'without property on an iterable' => [
            'arguments' => [
                'value' => new IterableExample(['first' => 'Anna', 'second' => 'Ben']),
            ],
            'src' => '{value -> f:valuesOf()}',
            'expectation' => ['Anna', 'Ben'],
        ];
        yield 'without property but with indexProperty' => [
            'arguments' => [
                'value' => $users,
            ],
            'src' => '<f:valuesOf value="{value}" indexProperty="id" />',
            'expectation' => [3 => ['id' => 3, 'name' => 'Anna'], 7 => ['id' => 7, 'name' => 'Ben']],
        ];
        yield 'object uses getters before properties' => [
            'arguments' => [
                'value' => new WithEverything(),
            ],
            'src' => '<f:valuesOf value="{value}" />',
            'expectation' => [
                'privateValue@getPrivateValue()',
                'protectedValue@getProtectedValue()',
                'publicValue@getPublicValue()',
            ],
        ];
        yield 'object uses getters before properties inline' => [
            'arguments' => [
                'value' => new WithEverything(),
            ],
            'src' => '{value -> f:valuesOf()}',
            'expectation' => [
                'privateValue@getPrivateValue()',
                'protectedValue@getProtectedValue()',
                'publicValue@getPublicValue()',
            ],
        ];
        yield 'object without getters only has public properties' => [
            'arguments' => [
                'value' => new WithProperties(),
            ],
            'src' => '{value -> f:valuesOf()}',
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
            'src' => '{value -> f:valuesOf()}',
            'expectation' => [true, false],
        ];
        yield 'object with inherited properties' => [
            'arguments' => [
                'value' => new class ('Anna') extends UserWithoutToString {},
            ],
            'src' => '{value -> f:valuesOf()}',
            'expectation' => ['Anna'],
        ];
        yield 'object with dynamic properties' => [
            'arguments' => [
                'value' => (object)['title' => 'Fluid', 'version' => 5],
            ],
            'src' => '{value -> f:valuesOf()}',
            'expectation' => ['Fluid', 5],
        ];
        yield 'object with property' => [
            'arguments' => [
                'value' => (object)['first' => ['name' => 'Anna'], 'second' => new UserWithoutToString('Ben')],
            ],
            'src' => '{value -> f:valuesOf(property: \'name\')}',
            'expectation' => ['Anna', 'Ben'],
        ];
    }

    #[Test]
    public function renderWithoutPropertyKeepsAllItemsOfIteratorsWithRepeatedKeys(): void
    {
        $valueFactory = static function (): \Generator {
            yield from ['Anna', 'Ben'];
            yield from ['Chris', 'Dana'];
        };
        $src = '{repeatedKeys -> f:valuesOf()}';

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($src);
        $view->assign('repeatedKeys', $valueFactory());
        self::assertSame(['Anna', 'Ben', 'Chris', 'Dana'], $view->render());

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($src);
        $view->assign('repeatedKeys', $valueFactory());
        self::assertSame(['Anna', 'Ben', 'Chris', 'Dana'], $view->render());
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
            'src' => '<f:valuesOf property="name">SOME TEXT</f:valuesOf>',
            'exceptionCode' => 1792005200,
        ];
        yield 'invalid string attribute' => [
            'arguments' => [],
            'src' => '<f:valuesOf value="string" property="name" />',
            'exceptionCode' => 1792005200,
        ];
        yield 'invalid string inline' => [
            'arguments' => [
                'value' => 'string',
            ],
            'src' => '{value -> f:valuesOf(property: \'name\')}',
            'exceptionCode' => 1792005200,
        ];
        yield 'invalid integer attribute' => [
            'arguments' => [
                'value' => 123,
            ],
            'src' => '<f:valuesOf value="{value}" property="name" />',
            'exceptionCode' => 1792005200,
        ];
        yield 'undefined value' => [
            'arguments' => [],
            'src' => '<f:valuesOf value="{undefinedVariable}" property="name" />',
            'exceptionCode' => 1792005200,
        ];
        yield 'arrayaccess inline' => [
            'arguments' => [
                'value' => new ArrayAccessExample(['foo' => 'bar']),
            ],
            'src' => '{value -> f:valuesOf(property: \'name\')}',
            'exceptionCode' => 1794819224,
        ];
        yield 'arrayaccess attribute' => [
            'arguments' => [
                'value' => new ArrayAccessExample(['foo' => 'bar']),
            ],
            'src' => '<f:valuesOf value="{value}" />',
            'exceptionCode' => 1794819224,
        ];
        yield 'scalar items' => [
            'arguments' => [
                'value' => [['name' => 'Anna'], 'Ben'],
            ],
            'src' => '<f:valuesOf value="{value}" property="name" />',
            'exceptionCode' => 1792005201,
        ];
        yield 'scalar items with indexProperty only' => [
            'arguments' => [
                'value' => [['id' => 3], 'Ben'],
            ],
            'src' => '<f:valuesOf value="{value}" indexProperty="id" />',
            'exceptionCode' => 1792005201,
        ];
        yield 'indexProperty with array values' => [
            'arguments' => [
                'value' => [['id' => [3], 'name' => 'Anna']],
            ],
            'src' => '<f:valuesOf value="{value}" property="name" indexProperty="id" />',
            'exceptionCode' => 1792005202,
        ];
        yield 'indexProperty with float values' => [
            'arguments' => [
                'value' => [['id' => 3.5, 'name' => 'Anna']],
            ],
            'src' => '<f:valuesOf value="{value}" property="name" indexProperty="id" />',
            'exceptionCode' => 1792005202,
        ];
        yield 'indexProperty with non-stringable objects' => [
            'arguments' => [
                'value' => [['author' => new UserWithoutToString('Anna'), 'title' => 'First']],
            ],
            'src' => '<f:valuesOf value="{value}" property="title" indexProperty="author" />',
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
