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
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\IterableExample;
use TYPO3Fluid\Fluid\View\TemplateView;

final class LastViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderValidDataProvider(): iterable
    {
        yield 'empty value' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '<f:last value="{value}" />',
            'expectation' => null,
        ];
        yield 'empty value inline' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '{value -> f:last()}',
            'expectation' => null,
        ];
        yield 'single item' => [
            'arguments' => [
                'value' => [1],
            ],
            'src' => '<f:last value="{value}" />',
            'expectation' => 1,
        ];
        yield 'multiple items' => [
            'arguments' => [
                'value' => ['first', 'second', 'third'],
            ],
            'src' => '<f:last value="{value}" />',
            'expectation' => 'third',
        ];
        yield 'value inline as iterable' => [
            'arguments' => [
                'value' => new IterableExample(['first', 'second', 'third']),
            ],
            'src' => '{value -> f:last()}',
            'expectation' => 'third',
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
            'src' => '<f:last>SOME TEXT</f:last>',
        ];
        yield 'invalid string inline' => [
            'arguments' => [
                'value' => 'string',
            ],
            'src' => '{value -> f:last()}',
        ];
        yield 'arrayaccess inline' => [
            'arguments' => [
                'value' => new ArrayAccessExample(['foo' => 'bar']),
            ],
            'src' => '{value -> f:last()}',
        ];
    }

    #[DataProvider('renderInvalidDataProvider')]
    #[Test]
    public function renderInvalid(array $arguments, string $src): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1712221620);

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
