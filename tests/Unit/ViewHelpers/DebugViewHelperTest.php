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
class DebugViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @test
	 */
	public function testInitializeArgumentsRegistersExpectedArguments() {
		$instance = $this->getMock(DebugViewHelper::class, array('registerArgument'));
		$instance->expects($this->at(0))->method('registerArgument')->with('typeOnly', 'boolean', $this->anything(), FALSE, FALSE);
		$instance->setRenderingContext(new RenderingContextFixture());
		$instance->initializeArguments();
	}

	/**
	 * @dataProvider getRenderTestValues
	 * @param mixed $value
	 * @param array $arguments
	 * @param string $expected
	 */
	public function testRender($value, array $arguments, $expected) {
		$instance = $this->getMock(DebugViewHelper::class, array('renderChildren'));
		$instance->expects($this->once())->method('renderChildren')->willReturn($value);
		$instance->setArguments($arguments);
		$instance->setRenderingContext(new RenderingContextFixture());
		$result = $instance->render();
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return array
	 */
	public function getRenderTestValues() {
		return array(
			array('test', array('typeOnly' => FALSE, 'html' => FALSE, 'levels' => 1), "string 'test'" . PHP_EOL),
			array('test', array('typeOnly' => TRUE, 'html' => FALSE, 'levels' => 1), 'string'),
			array(
				'test<strong>bold</strong>',
				array('typeOnly' => FALSE, 'html' => TRUE, 'levels' => 1),
				'<code>string = \'test&lt;strong&gt;bold&lt;/strong&gt;\'</code>'
			),
			array(
				array('nested' => 'test<strong>bold</strong>'),
				array('typeOnly' => FALSE, 'html' => TRUE, 'levels' => 1),
				'<code>array</code><ul><li>nested: <code>string = \'test&lt;strong&gt;bold&lt;/strong&gt;\'</code></li></ul>'
			),
			array(
				array('foo' => 'bar'),
				array('typeOnly' => FALSE, 'html' => TRUE, 'levels' => 2),
				'<code>array</code><ul><li>foo: <code>string = \'bar\'</code></li></ul>'
			),
			array(
				new \ArrayObject(array('foo' => 'bar')),
				array('typeOnly' => FALSE, 'html' => TRUE, 'levels' => 2),
				'<code>ArrayObject</code><ul><li>foo: <code>string = \'bar\'</code></li></ul>'
			),
			array(
				new \ArrayIterator(array('foo' => 'bar')),
				array('typeOnly' => FALSE, 'html' => TRUE, 'levels' => 2),
				'<code>ArrayIterator</code><ul><li>foo: <code>string = \'bar\'</code></li></ul>'
			),
			array(
				array('foo' => 'bar'),
				array('typeOnly' => FALSE, 'html' => FALSE, 'levels' => 3),
				'array: ' . PHP_EOL . '  "foo": string \'bar\'' . PHP_EOL
			),
			array(
				new \ArrayObject(array('foo' => 'bar')),
				array('typeOnly' => FALSE, 'html' => FALSE, 'levels' => 3),
				'ArrayObject: ' . PHP_EOL . '  "foo": string \'bar\'' . PHP_EOL
			),
			array(
				new \ArrayIterator(array('foo' => 'bar')),
				array('typeOnly' => FALSE, 'html' => FALSE, 'levels' => 3),
				'ArrayIterator: ' . PHP_EOL . '  "foo": string \'bar\'' . PHP_EOL
			),
			array(
				new UserWithoutToString('username'),
				array('typeOnly' => FALSE, 'html' => FALSE, 'levels' => 3),
				UserWithoutToString::class . ': ' . PHP_EOL . '  "name": string \'username\'' . PHP_EOL
			),
			array(
				NULL,
				array('typeOnly' => FALSE, 'html' => FALSE, 'levels' => 3),
				'null' . PHP_EOL
			),
			array(
				\DateTime::createFromFormat('U', '1468328915'),
				array('typeOnly' => FALSE, 'html' => FALSE, 'levels' => 3),
				'DateTime: ' . PHP_EOL . '  "class": string \'DateTime\'' . PHP_EOL .
				'  "ISO8601": string \'2016-07-12T13:08:35+0000\'' . PHP_EOL . '  "UNIXTIME": integer 1468328915' . PHP_EOL
			)
		);
	}

}
