<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Condition\Type;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase;

/**
 * IsInstanceOfViewHelperTest
 */
class IsInstanceOfViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @test
	 */
	public function rendersThenChildIfConditionMatched() {
		$dateTime = new \DateTime('now');
		$arguments = array(
			'then' => 'then',
			'else' => 'else',
			'value' => $dateTime,
			'class' => 'DateTime'
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
			'value' => 1,
			'class' => 'DateTime'
		);
		$result = $this->executeViewHelper($arguments);
		$this->assertEquals('else', $result);

		$staticResult = $this->executeViewHelperStatic($arguments);
		$this->assertEquals($result, $staticResult, 'The regular viewHelper output doesn\'t match the static output!');
	}

}
