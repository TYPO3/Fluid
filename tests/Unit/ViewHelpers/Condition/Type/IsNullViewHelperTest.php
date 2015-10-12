<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Condition\Type;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase;

/**
 * IsNullViewHelperTest
 */
class IsNullViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @test
	 */
	public function rendersThenChildIfVariableIsNull() {
		$arguments = array(
			'value' => NULL,
			'then' => 'then',
			'else' => 'else'
		);
		$result = $this->executeViewHelper($arguments);
		$this->assertEquals('then', $result);
	}

	/**
	 * @test
	 */
	public function rendersThenChildIfVariableIsNullStatic() {
		$arguments = array(
			'value' => NULL,
			'then' => 'then',
			'else' => 'else'
		);
		$staticResult = $this->executeViewHelperStatic($arguments);
		$this->assertEquals('then', $staticResult);
	}

	/**
	 * @test
	 */
	public function rendersElseChildIfVariableIsNotNull() {
		$arguments = array(
			'value' => TRUE,
			'then' => 'then',
			'else' => 'else'
		);
		$result = $this->executeViewHelper($arguments);
		$this->assertEquals('else', $result);
	}

	/**
	 * @test
	 */
	public function rendersElseChildIfVariableIsNotNullStatic() {
		$arguments = array(
			'value' => TRUE,
			'then' => 'then',
			'else' => 'else'
		);
		$staticResult = $this->executeViewHelperStatic($arguments);
		$this->assertEquals('else', $staticResult);
	}

}
