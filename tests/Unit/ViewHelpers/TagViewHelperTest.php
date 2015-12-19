<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * TagViewHelperTest
 */
class TagViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @test
	 * @dataProvider getRenderTagTestValues
	 * @param array $arguments
	 * @param mixed $content
	 * @param string $expected
	 */
	public function renderTag(array $arguments, $content, $expected) {
		$result = $this->executeViewHelperUsingTagContent('Text', $content, $arguments);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return array
	 */
	public function getRenderTagTestValues() {
		return array(
			array(array('name' => 'div'), 'test', '<div>test</div>'),
			array(array('name' => 'div', 'class' => 'test'), 'test', '<div class="test">test</div>'),
			array(array('name' => 'div', 'hideIfEmpty' => TRUE), '', ''),
		);
	}

}
