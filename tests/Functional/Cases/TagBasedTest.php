<?php

namespace TYPO3Fluid\Fluid\Tests\Functional\Cases;

use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers\TagBasedTestViewHelper;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

class TagBasedTest extends UnitTestCase
{
    public function testTagBasedViewHelperWithAdditionalAttributesArray()
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

    public function testTagBasedViewHelperWithDataArray()
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

    public function testTagBasedViewHelperWithAriaArray()
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

    public function testTagBasedViewHelperWithDataPrefixedArgument()
    {
        $invoker = new ViewHelperInvoker();
        $viewHelper = new TagBasedTestViewHelper();
        $arguments = [
            'data-foo' => 'bar',
        ];
        $result = $invoker->invoke($viewHelper, $arguments, new RenderingContextFixture());
        self::assertSame('<div data-foo="bar" />', $result);
    }

    public function testTagBasedViewHelperWithAriaPrefixedArgument()
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
    public function testTagBasedViewHelperWithDataArrayAndPrefixedArgument(array $arguments)
    {
        $invoker = new ViewHelperInvoker();
        $viewHelper = new TagBasedTestViewHelper();
        $result = $invoker->invoke($viewHelper, $arguments, new RenderingContextFixture());
        self::assertSame('<div data-foo="attribute" />', $result);
    }

    public function tagBasedViewHelperWithDataArrayAndPrefixedArgumentProvider(): array
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
    public function testTagBasedViewHelperWithAriaArrayAndPrefixedArgument(array $arguments)
    {
        $invoker = new ViewHelperInvoker();
        $viewHelper = new TagBasedTestViewHelper();
        $result = $invoker->invoke($viewHelper, $arguments, new RenderingContextFixture());
        self::assertSame('<div aria-controls="attribute" />', $result);
    }

    public function tagBasedViewHelperWithAriaArrayAndPrefixedArgumentProvider(): array
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
