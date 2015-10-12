<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Condition\Iterator;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase;

/**
 * ContainsViewHelperTest
 */
class ContainsViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @dataProvider getPositiveTestValues
	 * @param mixed $haystack
	 * @param mixed $needle
	 */
	public function testRendersThen($haystack, $needle) {
		$arguments = array(
			'haystack' => $haystack,
			'needle' => $needle,
			'then' => 'then'
		);
		$result = $this->executeViewHelper($arguments);
		$this->assertEquals('then', $result);
	}

	/**
	 * @dataProvider getPositiveTestValues
	 * @param mixed $haystack
	 * @param mixed $needle
	 */
	public function testRendersThenStatic($haystack, $needle) {
		$arguments = array(
			'haystack' => $haystack,
			'needle' => $needle,
			'then' => 'then'
		);
		$staticResult = $this->executeViewHelperStatic($arguments);
		$this->assertEquals('then', $staticResult);
	}

	/**
	 * @return array
	 */
	public function getPositiveTestValues() {
		return array(
			array(array('foo'), 'foo'),
			array(new \ArrayIterator(array('foo')), 'foo'),
			array('foo,bar', 'foo'),
		);
	}

	/**
	 * @dataProvider getNegativeTestValues
	 * @param mixed $haystack
	 * @param mixed $needle
	 */
	public function testRendersElse($haystack, $needle) {
		$arguments = array(
			'haystack' => $haystack,
			'needle' => $needle,
			'else' => 'else'
		);
		$result = $this->executeViewHelper($arguments);
		$this->assertEquals('else', $result);
	}

	/**
	 * @dataProvider getNegativeTestValues
	 * @param mixed $haystack
	 * @param mixed $needle
	 */
	public function testRendersElseStatic($haystack, $needle) {
		$arguments = array(
			'haystack' => $haystack,
			'needle' => $needle,
			'else' => 'else'
		);
		$staticResult = $this->executeViewHelperStatic($arguments);
		$this->assertEquals('else', $staticResult);
	}

	/**
	 * @return array
	 */
	public function getNegativeTestValues() {
		return array(
			array(array('foo'), 'bar'),
			array(new \ArrayIterator(array('foo')), 'bar'),
			array('foo,baz', 'bar'),
		);
	}

}
