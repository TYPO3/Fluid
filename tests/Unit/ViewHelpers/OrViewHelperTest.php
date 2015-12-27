<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\ViewHelpers\OrViewHelper;

/**
 * Class OrViewHelperTest
 */
class OrViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @test
	 */
	public function testInitializeArguments() {
		$instance = $this->getMock(OrViewHelper::class, array('registerArgument'));
		$instance->expects($this->at(0))->method('registerArgument')->with('content', 'mixed', $this->anything(), FALSE, '');
		$instance->expects($this->at(1))->method('registerArgument')->with('alternative', 'mixed', $this->anything(), FALSE, '');
		$instance->expects($this->at(2))->method('registerArgument')->with('arguments', 'array', $this->anything(), FALSE, NULL);
		$instance->initializeArguments();
	}

	/**
	 * @test
	 * @dataProvider getRenderTestValues
	 * @param array $arguments
	 * @param mixed $expected
	 */
	public function testRender($arguments, $expected) {
		$instance = $this->getMock(OrViewHelper::class, array('renderChildren'));
		$instance->expects($this->exactly((integer) empty($arguments['content'])))->method('renderChildren')->willReturn($arguments['content']);
		$instance->setArguments($arguments);
		$result = $instance->render();
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return array
	 */
	public function getRenderTestValues() {
		return array(
			array(array('arguments' => NULL, 'content' => 'alt', 'alternative' => 'alternative'), 'alt'),
			array(array('arguments' => NULL, 'content' => '1', 'alternative' => 'alternative'), '1'),
		);
	}

	/**
	 * @test
	 * @dataProvider getRenderAlternativeTestValues
	 * @param array $arguments
	 * @param mixed $expected
	 */
	public function testRenderAlternative($arguments, $expected) {
		$instance = $this->getMock(OrViewHelper::class, array('renderChildren'));
		$instance->expects($this->once())->method('renderChildren')->willReturn(NULL);
		$arguments['content'] = NULL;
		$instance->setArguments($arguments);
		$result = $instance->render();
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return array
	 */
	public function getRenderAlternativeTestValues() {
		return array(
			array(array('arguments' => NULL, 'alternative' => 'alternative'), 'alternative'),
			array(array('arguments' => array('abc'), 'alternative' => 'alternative %s alt'), 'alternative abc alt'),
		);
	}

}
