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
use TYPO3Fluid\Fluid\View\TemplateView;

final class JoinViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderValidDataProvider(): \Generator
    {
        yield 'template with named layout' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '<f:join value="{value}" />',
            'expectation' => '',
        ];
        yield 'empty value inline' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '{value -> f:join()}',
            'expectation' => '',
        ];
        yield 'empty attribute value, with separator' => [
            'arguments' => [
                'value' => [],
                'separator' => ',',
            ],
            'src' => '<f:join value="{value}" separator="{separator}" />',
            'expectation' => '',
        ];
        yield 'empty attribute value, with separatorLast' => [
            'arguments' => [
                'value' => [],
                'separatorLast' => ' and ',
            ],
            'src' => '<f:join value="{value}" separatorLast="{separatorLast}" />',
            'expectation' => '',
        ];
        yield 'value attribute' => [
            'arguments' => [
                'value' => [1, 2, 3],
            ],
            'src' => '<f:join value="{value}" />',
            'expectation' => '123',
        ];
        yield 'value attribute inline array' => [
            'arguments' => [
                'value' => [1, 2, 3],
            ],
            'src' => '<f:join value="{0: \'1\', 1: \'2\', 2: \'3\'}" />',
            'expectation' => '123',
        ];
        yield 'value inline' => [
            'arguments' => [
                'value' => [1, 2, 3],
            ],
            'src' => '{value -> f:join()}',
            'expectation' => '123',
        ];
        yield 'value inline and argument' => [
            'arguments' => [
                'valueInline' => [1, 2, 3],
                'valueArgument' => [3, 2, 1]
            ],
            'src' => '{valueInline -> f:join(value: valueArgument)}',
            'expectation' => '321',
        ];
        yield 'value and separator set' => [
            'arguments' => [
                'value' => [1, 2, 3],
                'separator' => ', ',
            ],
            'src' => '<f:join value="{value}" separator="{separator}" />',
            'expectation' => '1, 2, 3',
        ];
        yield 'value and separatorLast set' => [
            'arguments' => [
                'value' => [1, 2, 3],
                'separatorLast' => ' and ',
            ],
            'src' => '<f:join value="{value}" separatorLast="{separatorLast}" />',
            'expectation' => '12 and 3',
        ];
        yield 'value, separator and separatorLast set' => [
            'arguments' => [
                'value' => [1, 2, 3],
                'separator' => ', ',
                'separatorLast' => ' and ',
            ],
            'src' => '<f:join value="{value}" separator="{separator}" separatorLast="{separatorLast}" />',
            'expectation' => '1, 2 and 3',
        ];
    }

    #[DataProvider('renderValidDataProvider')]
    #[Test]
    public function renderValid(array $arguments, string $src, string $expectation): void
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
            'arguments' => [
            ],
            'src' => '<f:join>SOME TEXT</f:join>',
        ];
        yield 'invalid string attribute' => [
            'arguments' => [
                'value' => 'string',
            ],
            'src' => '<f:join value="string" />',
        ];
        yield 'invalid string inline' => [
            'arguments' => [
                'value' => 'string',
            ],
            'src' => '{value -> f:join()}',
        ];
    }

    #[DataProvider('renderInvalidDataProvider')]
    #[Test]
    public function renderInvalid(array $arguments, string $src): void
    {
        $this->expectException(\InvalidArgumentException::class);

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
