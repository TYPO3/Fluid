<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Condition\Type;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase;

/**
 * IsBooleanViewHelperTest
 */
class IsBooleanViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @test
	 */
	public function rendersThenChildIfConditionMatched() {
		$arguments = array(
			'then' => 'then',
			'else' => 'else',
			'value' => TRUE
		);
		$result = $this->executeViewHelper($arguments);
		$this->assertEquals('then', $result);

		$staticResult = $this->executeViewHelperStatic($arguments);
		$this->assertEquals($result, $staticResult, 'The regular viewHelper output doesn\'t match the static output!');
	}

	/**
	 * @test
	 */
	public function rendersElseChildIfConditionNotMatched() {
		$arguments = array(
			'then' => 'then',
			'else' => 'else',
			'value' => new \stdClass()
		);
		$result = $this->executeViewHelper($arguments);
		$this->assertEquals('else', $result);

		$staticResult = $this->executeViewHelperStatic($arguments);
		$this->assertEquals($result, $staticResult, 'The regular viewHelper output doesn\'t match the static output!');
	}

}
