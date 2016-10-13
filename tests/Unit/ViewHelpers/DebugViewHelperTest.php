<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithoutToString;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\ViewHelpers\DebugViewHelper;

/**
 * Testcase for DebugViewHelper
 */
class DebugViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @test
     */
    public function testInitializeArgumentsRegistersExpectedArguments()
    {
        $instance = $this->getMock(DebugViewHelper::class, ['registerArgument']);
        $instance->expects($this->at(0))->method('registerArgument')->with('typeOnly', 'boolean', $this->anything(), false, false);
        $instance->setRenderingContext(new RenderingContextFixture());
        $instance->initializeArguments();
    }

    /**
     * @dataProvider getRenderTestValues
     * @param mixed $value
     * @param array $arguments
     * @param string $expected
     */
    public function testRender($value, array $arguments, $expected)
    {
        $instance = $this->getMock(DebugViewHelper::class, ['renderChildren']);
        $instance->expects($this->once())->method('renderChildren')->willReturn($value);
        $instance->setArguments($arguments);
        $instance->setRenderingContext(new RenderingContextFixture());
        $result = $instance->render();
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getRenderTestValues()
    {
        return [
            ['test', ['typeOnly' => false, 'html' => false, 'levels' => 1], "string 'test'" . PHP_EOL],
            ['test', ['typeOnly' => true, 'html' => false, 'levels' => 1], 'string'],
            [
                'test<strong>bold</strong>',
                ['typeOnly' => false, 'html' => true, 'levels' => 1],
                '<code>string = \'test&lt;strong&gt;bold&lt;/strong&gt;\'</code>'
            ],
            [
                ['nested' => 'test<strong>bold</strong>'],
                ['typeOnly' => false, 'html' => true, 'levels' => 1],
                '<code>array</code><ul><li>nested: <code>string = \'test&lt;strong&gt;bold&lt;/strong&gt;\'</code></li></ul>'
            ],
            [
                ['foo' => 'bar'],
                ['typeOnly' => false, 'html' => true, 'levels' => 2],
                '<code>array</code><ul><li>foo: <code>string = \'bar\'</code></li></ul>'
            ],
            [
                new \ArrayObject(['foo' => 'bar']),
                ['typeOnly' => false, 'html' => true, 'levels' => 2],
                '<code>ArrayObject</code><ul><li>foo: <code>string = \'bar\'</code></li></ul>'
            ],
            [
                new \ArrayIterator(['foo' => 'bar']),
                ['typeOnly' => false, 'html' => true, 'levels' => 2],
                '<code>ArrayIterator</code><ul><li>foo: <code>string = \'bar\'</code></li></ul>'
            ],
            [
                ['foo' => 'bar'],
                ['typeOnly' => false, 'html' => false, 'levels' => 3],
                'array: ' . PHP_EOL . '  "foo": string \'bar\'' . PHP_EOL
            ],
            [
                new \ArrayObject(['foo' => 'bar']),
                ['typeOnly' => false, 'html' => false, 'levels' => 3],
                'ArrayObject: ' . PHP_EOL . '  "foo": string \'bar\'' . PHP_EOL
            ],
            [
                new \ArrayIterator(['foo' => 'bar']),
                ['typeOnly' => false, 'html' => false, 'levels' => 3],
                'ArrayIterator: ' . PHP_EOL . '  "foo": string \'bar\'' . PHP_EOL
            ],
            [
                new UserWithoutToString('username'),
                ['typeOnly' => false, 'html' => false, 'levels' => 3],
                UserWithoutToString::class . ': ' . PHP_EOL . '  "name": string \'username\'' . PHP_EOL
            ],
            [
                null,
                ['typeOnly' => false, 'html' => false, 'levels' => 3],
                'null' . PHP_EOL
            ],
            [
                \DateTime::createFromFormat('U', '1468328915'),
                ['typeOnly' => false, 'html' => false, 'levels' => 3],
                'DateTime: ' . PHP_EOL . '  "class": string \'DateTime\'' . PHP_EOL .
                '  "ISO8601": string \'2016-07-12T13:08:35+0000\'' . PHP_EOL . '  "UNIXTIME": integer 1468328915' . PHP_EOL
            ]
        ];
    }
}
