<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3\CMS\Fluid\Tests\Functional\ViewHelpers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\ArrayAccessExample;
use TYPO3Fluid\Fluid\View\TemplateView;

final class ContainsViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderValidDataProvider(): iterable
    {
        yield 'result body, value and string given' => [
            '<f:contains value="{value}" string="{string}">' .
            'thenArgument' .
            '</f:contains>',
            ['value' => 'Wo', 'string' => 'Hello, World!'],
            'thenArgument',
        ];
        yield 'then argument, value and string given' => [
            '<f:contains value="{value}" string="{string}" then="thenArgument" />',
            ['value' => 'Wo', 'string' => 'Hello, World!'],
            'thenArgument',
        ];
        yield 'else argument, value and string given' => [
            '<f:contains value="{value}" string="{string}" else="elseArgument" />',
            ['value' => 'Wie', 'string' => 'Hello, World!'],
            'elseArgument',
        ];
        yield 'then argument, else argument, value and string given, result is thenArgument' => [
            '<f:contains value="{value}" string="{string}" then="thenArgument" else="elseArgument" />',
            ['value' => 'Wo', 'string' => 'Hello, World!'],
            'thenArgument',
        ];
        yield 'then argument, else argument, value and string given, result is elseArgument' => [
            '<f:contains value="{value}" string="{string}" then="thenArgument" else="elseArgument" />',
            ['value' => 'Wie', 'string' => 'Hello, World!'],
            'elseArgument',
        ];
        yield 'leading whitespace, empty result body, value and string given' => [
            ' <f:contains value="{value}" string="{string}"><f:variable name="foo" value="bar" /></f:contains>',
            ['value' => 'Wo', 'string' => 'Hello, World!'],
            ' ',
        ];
        yield 'then child, else child, value and string given' => [
            '<f:contains value="{value}" string="{string}">' .
            '<f:then>thenChild</f:then>' .
            '<f:else>elseChild</f:else>' .
            '</f:contains>',
            ['value' => 'Wo', 'string' => 'Hello, World!'],
            'thenChild',
        ];
        yield 'inline syntax, then argument, value and string given' => [
            '{f:contains(value="Wo", string: string, then: "thenArgument")}',
            ['string' => 'Hello, World!'],
            'thenArgument',
        ];
        yield 'nested example, inside if with condition, value and string given, render body' => [
            '<f:variable name="condition" value="false" />' .
            '<f:if condition="{condition} || {f:contains(value: value, string: string)}">' .
            'It Works!' .
            '</f:if>',
            ['value' => 'Wo', 'string' => 'Hello, World!'],
            'It Works!',
        ];

        // Array
        yield 'result body, value and array given' => [
            '<f:contains value="{value}" array="{array}">' .
            'thenArgument' .
            '</f:contains>',
            ['value' => 'World!', 'array' => ['Hello', 'World!']],
            'thenArgument',
        ];
        yield 'then argument, value and array given' => [
            '<f:contains value="{value}" array="{array}" then="thenArgument" />',
            ['value' => 'World!', 'array' => ['Hello', 'World!']],
            'thenArgument',
        ];
        yield 'else argument, value and array given' => [
            '<f:contains value="{value}" array="{array}" else="elseArgument" />',
            ['value' => 'Wie', 'array' => ['Hello', 'World!']],
            'elseArgument',
        ];
        yield 'then argument, else argument, value and array given, result is thenArgument' => [
            '<f:contains value="{value}" array="{array}" then="thenArgument" else="elseArgument" />',
            ['value' => 'World!', 'array' => ['Hello', 'World!']],
            'thenArgument',
        ];
        yield 'then argument, else argument, value and array given, result is elseArgument' => [
            '<f:contains value="{value}" array="{array}" then="thenArgument" else="elseArgument" />',
            ['value' => 'Wie', 'array' => ['Hello', 'World!']],
            'elseArgument',
        ];
        yield 'leading whitespace, empty result body, value and array given' => [
            ' <f:contains value="{value}" array="{array}"><f:variable name="foo" value="bar" /></f:contains>',
            ['value' => 'World!', 'array' => ['Hello', 'World!']],
            ' ',
        ];
        yield 'then child, else child, value and array given' => [
            '<f:contains value="{value}" array="{array}">' .
            '<f:then>thenChild</f:then>' .
            '<f:else>elseChild</f:else>' .
            '</f:contains>',
            ['value' => 'World!', 'array' => ['Hello', 'World!']],
            'thenChild',
        ];
        yield 'inline syntax, then argument, value and array given' => [
            '{f:contains(value: "World!", array: array, then: "thenArgument")}',
            ['array' => ['Hello', 'World!']],
            'thenArgument',
        ];
        yield 'nested example, inside if with condition, value and array given, render body' => [
            '<f:variable name="condition" value="false" />' .
            '<f:if condition="{condition} || {f:contains(value: value, array: array)}">' .
            'It Works!' .
            '</f:if>',
            ['value' => 'World!', 'array' => ['Hello', 'World!']],
            'It Works!',
        ];
        yield 'inline syntax, then argument, integer value and array given' => [
            '{f:contains(value: 12, array: array, then: "thenArgument")}',
            ['array' => [10, 11, 12]],
            'thenArgument',
        ];
        yield 'inline syntax, then argument, float value and array given' => [
            '{f:contains(value: 1.2, array: array, then: "thenArgument")}',
            ['array' => [1, 1.1, 1.2]],
            'thenArgument',
        ];
        yield 'inline syntax, then argument, float value and mixed array given' => [
            '{f:contains(value: 1.2, array: array, then: "thenArgument")}',
            ['array' => [1, 2, 1.2, [47 => 11]]],
            'thenArgument',
        ];
    }

    #[DataProvider('renderValidDataProvider')]
    #[Test]
    public function renderValid(string $template, array $variables, $expected): void
    {
        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        self::assertSame($expected, $view->render());

        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        self::assertSame($expected, $view->render());
    }

    public static function renderWithInvalidArgumentsDataProvider(): iterable
    {
        yield 'invalid parameter, string and array provided' => [
            'arguments' => [
                'value' => 'Wo',
                'string' => 'Hello, World!',
                'array' => ['Hello', 'World!'],
            ],
            'src' => '<f:contains value="{value}" string="{string}" array="{array}" />',
            'expectedExceptionCode' => 1754978400,
        ];
        yield 'invalid parameter, empty string and empty array provided' => [
            'arguments' => [
                'value' => 'Wo',
                'string' => '',
                'array' => [],
            ],
            'src' => '<f:contains value="{value}" string="{string}" array="{array}" />',
            'expectedExceptionCode' => 1754978400,
        ];
        yield 'invalid parameter, no string and no array provided' => [
            'arguments' => [
                'value' => 'Wo',
            ],
            'src' => '<f:contains value="{value}" />',
            'expectedExceptionCode' => 1754978400,
        ];
        yield 'invalid parameter, value is not a scalar type' => [
            'arguments' => [
                'value' => new \stdClass(),
                'string' => 'Hello, World!',
            ],
            'src' => '<f:contains value="{value}" string="{string}" />',
            'expectedExceptionCode' => 1754978401,
        ];
        yield 'invalid parameter, array with string' => [
            'arguments' => [
                'value' => 'World',
                'array' => 'Hello, World!',
            ],
            'src' => '<f:contains value="{value}" array="{array}" />',
            'expectedExceptionCode' => 1256475113,
        ];
        yield 'invalid parameter, string with empty array' => [
            'arguments' => [
                'value' => 'World',
                'string' => [],
            ],
            'src' => '<f:contains value="{value}" string="{string}" />',
            'expectedExceptionCode' => 1754978400,
        ];
        yield 'invalid parameter, array with empty string' => [
            'arguments' => [
                'value' => 'World',
                'array' => '',
            ],
            'src' => '<f:contains value="{value}" string="{string}" />',
            'expectedExceptionCode' => 1754978400,
        ];
        yield 'invalid parameter, string with none string' => [
            'arguments' => [
                'value' => 'World',
                'string' => 12,
            ],
            'src' => '<f:contains value="{value}" string="{string}" />',
            'expectedExceptionCode' => 1754978404,
        ];

        yield 'invalid parameter, array with none iterable' => [
            'arguments' => [
                'value' => 'World',
                'array' => new ArrayAccessExample(['foo' => 'bar']),
            ],
            'src' => '<f:contains value="{value}" array="{array}" />',
            'expectedExceptionCode' => 1754978402,
        ];
    }

    #[DataProvider('renderWithInvalidArgumentsDataProvider')]
    #[Test]
    public function renderInvalidArguments(array $arguments, string $src, int $expectedExceptionCode): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode($expectedExceptionCode);

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
