<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases;

use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;
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
        $this->assertSame('<div foo="bar" />', $result);
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
        $this->assertSame('<div data-foo="bar" />', $result);
    }

    public function testTagBasedViewHelperWithDataPrefixedArgument()
    {
        $invoker = new ViewHelperInvoker();
        $viewHelper = new TagBasedTestViewHelper();
        $arguments = [
            'data-foo' => 'bar',
        ];
        $result = $invoker->invoke($viewHelper, $arguments, new RenderingContextFixture());
        $this->assertSame('<div data-foo="bar" />', $result);
    }

    /**
     * @dataProvider tagBasedViewHelperWithDataArrayAndPrefixedArgumentProvider
     */
    public function testTagBasedViewHelperWithDataArrayAndPrefixedArgument(array $arguments)
    {
        $invoker = new ViewHelperInvoker();
        $viewHelper = new TagBasedTestViewHelper();
        $result = $invoker->invoke($viewHelper, $arguments, new RenderingContextFixture());
        $this->assertSame('<div data-foo="attribute" />', $result);
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
}
