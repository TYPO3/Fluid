<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Cases;

use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;
use TYPO3Fluid\Fluid\Tests\BaseTestCase;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers\TagBasedTestViewHelper;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\Fixtures\RenderingContextFixture;

class TagBasedTest extends BaseTestCase
{
    public function testTagBasedViewHelperWithAdditionalAttributesArray(): void
    {
        $invoker = new ViewHelperInvoker();
        $viewHelper = new TagBasedTestViewHelper();
        $arguments = [
            'additionalAttributes' => [
                'foo' => 'bar',
            ],
        ];
        $result = $invoker->invoke($viewHelper, $arguments, new RenderingContextFixture());
        self::assertSame('<div foo="bar" />', $result);
    }

    public function testTagBasedViewHelperWithDataArray(): void
    {
        $invoker = new ViewHelperInvoker();
        $viewHelper = new TagBasedTestViewHelper();
        $arguments = [
            'data' => [
                'foo' => 'bar',
            ],
        ];
        $result = $invoker->invoke($viewHelper, $arguments, new RenderingContextFixture());
        self::assertSame('<div data-foo="bar" />', $result);
    }

    public function testTagBasedViewHelperWithAriaArray(): void
    {
        $invoker = new ViewHelperInvoker();
        $viewHelper = new TagBasedTestViewHelper();
        $arguments = [
            'aria' => [
                'controls' => 'foo',
            ],
        ];
        $result = $invoker->invoke($viewHelper, $arguments, new RenderingContextFixture());
        self::assertSame('<div aria-controls="foo" />', $result);
    }

    public function testTagBasedViewHelperWithDataPrefixedArgument(): void
    {
        $invoker = new ViewHelperInvoker();
        $viewHelper = new TagBasedTestViewHelper();
        $arguments = [
            'data-foo' => 'bar',
        ];
        $result = $invoker->invoke($viewHelper, $arguments, new RenderingContextFixture());
        self::assertSame('<div data-foo="bar" />', $result);
    }

    public function testTagBasedViewHelperWithAriaPrefixedArgument(): void
    {
        $invoker = new ViewHelperInvoker();
        $viewHelper = new TagBasedTestViewHelper();
        $arguments = [
            'aria-controls' => 'foo',
        ];
        $result = $invoker->invoke($viewHelper, $arguments, new RenderingContextFixture());
        self::assertSame('<div aria-controls="foo" />', $result);
    }

    /**
     * @dataProvider tagBasedViewHelperWithDataArrayAndPrefixedArgumentProvider
     */
    public function testTagBasedViewHelperWithDataArrayAndPrefixedArgument(array $arguments): void
    {
        $invoker = new ViewHelperInvoker();
        $viewHelper = new TagBasedTestViewHelper();
        $result = $invoker->invoke($viewHelper, $arguments, new RenderingContextFixture());
        self::assertSame('<div data-foo="attribute" />', $result);
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
     * @dataProvider tagBasedViewHelperWithAriaArrayAndPrefixedArgumentProvider
     */
    public function testTagBasedViewHelperWithAriaArrayAndPrefixedArgument(array $arguments): void
    {
        $invoker = new ViewHelperInvoker();
        $viewHelper = new TagBasedTestViewHelper();
        $result = $invoker->invoke($viewHelper, $arguments, new RenderingContextFixture());
        self::assertSame('<div aria-controls="attribute" />', $result);
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
}
