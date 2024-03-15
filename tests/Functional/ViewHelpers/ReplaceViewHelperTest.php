<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers;

use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\ArrayAccessExample;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\IterableExample;
use TYPO3Fluid\Fluid\View\TemplateView;

final class ReplaceViewHelperTest extends AbstractFunctionalTestCase
{
    public static function throwsExceptionForInvalidArgumentDataProvider(): iterable
    {
        yield 'without value' => [
            '<f:replace search="foo" replace="bar" />',
            [],
            1710441987,
            'A stringable value must be provided.',
        ];
        yield 'array as value' => [
            '{value -> f:replace(search: \'foo\', replace: \'bar\')}',
            ['value' => [1, 2, 3]],
            1710441987,
            'A stringable value must be provided.',
        ];
        yield 'no search and non-array replace' => [
            '<f:replace value="abc" replace="a" />',
            [],
            1710441988,
            'Argument "replace" must be iterable to be used without "search" argument, "string" given instead.',
        ];
        yield 'no search and replace as arrayaccess' => [
            '<f:replace value="abc" replace="{replace}" />',
            ['replace' => new ArrayAccessExample(['foo' => 'bar', 'abc' => 'def'])],
            1710441988,
            'Argument "replace" must be iterable to be used without "search" argument, "' . ArrayAccessExample::class . '" given instead.',
        ];
        yield 'search as arrayaccess' => [
            '<f:replace value="{value}" search="{search}" replace="{replace}" />',
            [
                'value' => 'test foo abc',
                'search' => new ArrayAccessExample(['foo', 'abc']),
                'replace' => ['bar', 'def'],
            ],
            1710441989,
            'Argument "search" must be either iterable or scalar, "' . ArrayAccessExample::class . '" given instead.',
        ];
        yield 'replace as arrayaccess' => [
            '<f:replace value="{value}" search="{search}" replace="{replace}" />',
            [
                'value' => 'test foo abc',
                'search' => ['foo', 'abc'],
                'replace' => new ArrayAccessExample(['bar', 'def']),
            ],
            1710441990,
            'Argument "replace" must be either iterable or scalar, "' . ArrayAccessExample::class . '" given instead.',
        ];
        yield 'non-matching search and replace count' => [
            '<f:replace value="abc" search="{0: \'a\', 1: \'b\'}" replace="{0: \'c\'}" />',
            [],
            1710441991,
            'Count of "search" and "replace" arguments must be the same.',
        ];
    }

    /**
     * @test
     * @dataProvider throwsExceptionForInvalidArgumentDataProvider
     */
    public function throwsExceptionForInvalidArgument(string $template, array $variables, int $exceptionCode, string $exceptionMessage): void
    {
        self::expectExceptionCode($exceptionCode);
        self::expectExceptionMessage($exceptionMessage);
        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        $view->render();
    }

    public static function renderDataProvider(): iterable
    {
        yield 'search and replace as strings' => [
            '<f:replace value="{value}" search="foo" replace="bar" />',
            ['value' => 'test foo abc'],
            'test bar abc',
        ];
        yield 'value from tag content' => [
            '{value -> f:replace(search: \'foo\', replace: \'bar\')}',
            ['value' => 'test foo abc'],
            'test bar abc',
        ];
        yield 'replace as empty string' => [
            '<f:replace value="{value}" search="foo" replace="" />',
            ['value' => 'test foo abc'],
            'test  abc',
        ];
        yield 'search and replace as array' => [
            '<f:replace value="{value}" search="{0: \'foo\', 1: \'abc\'}" replace="{0: \'bar\', 1: \'def\'}" />',
            ['value' => 'test foo abc'],
            'test bar def',
        ];
        yield 'search and replace as iterator' => [
            '<f:replace value="{value}" search="{search}" replace="{replace}" />',
            [
                'value' => 'test foo abc',
                'search' => new IterableExample(['foo', 'abc']),
                'replace' => new IterableExample(['bar', 'def']),
            ],
            'test bar def',
        ];
        yield 'replace key-value pairs' => [
            '<f:replace value="{value}" replace="{\'foo\': \'bar\', \'abc\': \'def\'}" />',
            ['value' => 'test foo abc'],
            'test bar def',
        ];
        yield 'replace key-value pairs as iterator' => [
            '<f:replace value="{value}" replace="{replace}" />',
            ['value' => 'test foo abc', 'replace' => new IterableExample(['foo' => 'bar', 'abc' => 'def'])],
            'test bar def',
        ];
        yield 'search and replace in empty string' => [
            '<f:replace value="{value}" search="foo" replace="bar" />',
            ['value' => ''],
            '',
        ];
    }

    /**
     * @test
     * @dataProvider renderDataProvider
     */
    public function render(string $template, array $variables, string $expected): void
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
}
