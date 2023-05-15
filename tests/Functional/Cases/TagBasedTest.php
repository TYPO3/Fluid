<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Cases;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;
use TYPO3Fluid\Fluid\Tests\BaseTestCase;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers\TagBasedTestViewHelper;

final class TagBasedTest extends BaseTestCase
{
    /**
     * @test
     */
    public function tagBasedViewHelperWithAdditionalAttributesArray(): void
    {
        $invoker = new ViewHelperInvoker();
        $viewHelper = new TagBasedTestViewHelper();
        $arguments = [
            'additionalAttributes' => [
                'foo' => 'bar',
            ],
        ];
        $result = $invoker->invoke($viewHelper, $arguments, new RenderingContext());
        self::assertSame('<div foo="bar" />', $result);
    }

    /**
     * @test
     */
    public function tagBasedViewHelperWithDataArray(): void
    {
        $invoker = new ViewHelperInvoker();
        $viewHelper = new TagBasedTestViewHelper();
        $arguments = [
            'data' => [
                'foo' => 'bar',
            ],
        ];
        $result = $invoker->invoke($viewHelper, $arguments, new RenderingContext());
        self::assertSame('<div data-foo="bar" />', $result);
    }

    /**
     * @test
     */
    public function tagBasedViewHelperWithAriaArray(): void
    {
        $invoker = new ViewHelperInvoker();
        $viewHelper = new TagBasedTestViewHelper();
        $arguments = [
            'aria' => [
                'controls' => 'foo',
            ],
        ];
        $result = $invoker->invoke($viewHelper, $arguments, new RenderingContext());
        self::assertSame('<div aria-controls="foo" />', $result);
    }

    /**
     * @test
     */
    public function tagBasedViewHelperWithDataPrefixedArgument(): void
    {
        $invoker = new ViewHelperInvoker();
        $viewHelper = new TagBasedTestViewHelper();
        $arguments = [
            'data-foo' => 'bar',
        ];
        $result = $invoker->invoke($viewHelper, $arguments, new RenderingContext());
        self::assertSame('<div data-foo="bar" />', $result);
    }

    /**
     * @test
     */
    public function tagBasedViewHelperWithAriaPrefixedArgument(): void
    {
        $invoker = new ViewHelperInvoker();
        $viewHelper = new TagBasedTestViewHelper();
        $arguments = [
            'aria-controls' => 'foo',
        ];
        $result = $invoker->invoke($viewHelper, $arguments, new RenderingContext());
        self::assertSame('<div aria-controls="foo" />', $result);
    }

    public static function tagBasedViewHelperWithDataArrayAndPrefixedArgumentProvider(): array
    {
        return [
            'data before attribute' => [
                [
                    'data' => [
                        'foo' => 'data',
                    ],
                    'data-foo' => 'attribute',
                ],
            ],
            'attribute before data' => [
                [
                    'data-foo' => 'attribute',
                    'data' => [
                        'foo' => 'data',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider tagBasedViewHelperWithDataArrayAndPrefixedArgumentProvider
     * @test
     */
    public function tagBasedViewHelperWithDataArrayAndPrefixedArgument(array $arguments): void
    {
        $invoker = new ViewHelperInvoker();
        $viewHelper = new TagBasedTestViewHelper();
        $result = $invoker->invoke($viewHelper, $arguments, new RenderingContext());
        self::assertSame('<div data-foo="attribute" />', $result);
    }

    public static function tagBasedViewHelperWithAriaArrayAndPrefixedArgumentProvider(): array
    {
        return [
            'aria before attribute' => [
                [
                    'aria' => [
                        'controls' => 'aria',
                    ],
                    'aria-controls' => 'attribute',
                ],
            ],
            'attribute before aria' => [
                [
                    'aria-controls' => 'attribute',
                    'aria' => [
                        'controls' => 'aria',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider tagBasedViewHelperWithAriaArrayAndPrefixedArgumentProvider
     * @test
     */
    public function tagBasedViewHelperWithAriaArrayAndPrefixedArgument(array $arguments): void
    {
        $invoker = new ViewHelperInvoker();
        $viewHelper = new TagBasedTestViewHelper();
        $result = $invoker->invoke($viewHelper, $arguments, new RenderingContext());
        self::assertSame('<div aria-controls="attribute" />', $result);
    }
}
