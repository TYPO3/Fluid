<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Condition\String;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase;

/**
 * PregViewHelperTest
 */
class PregViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @test
	 */
	public function rendersThenChildIfConditionMatched() {
		$arguments = array(
			'then' => 'then',
			'else' => 'else',
			'string' => 'foo123bar',
			'pattern' => '/([0-9]+)/i'
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
			'string' => 'foobar',
			'pattern' => '/[0-9]+/i'
		);
		$result = $this->executeViewHelper($arguments);
		$this->assertEquals('else', $result);

		$staticResult = $this->executeViewHelperStatic($arguments);
		$this->assertEquals($result, $staticResult, 'The regular viewHelper output doesn\'t match the static output!');
	}

	/**
	 * @test
	 */
	public function rendersThenChildIfConditionMatchedAndGlobalEnabled() {
		$arguments = array(
			'then' => 'then',
			'else' => 'else',
			'string' => 'foo123bar',
			'pattern' => '/([0-9]+)/i'
		);
		$result = $this->executeViewHelper($arguments);
		$this->assertEquals('then', $result);

		$staticResult = $this->executeViewHelperStatic($arguments);
		$this->assertEquals($result, $staticResult, 'The regular viewHelper output doesn\'t match the static output!');
	}

	/**
	 * @test
	 */
	public function rendersElseChildIfConditionNotMatchedAndGlobalEnabled() {
		$arguments = array(
			'then' => 'then',
			'else' => 'else',
			'string' => 'foobar',
			'pattern' => '/[0-9]+/i',
			'global' => TRUE
		);
		$result = $this->executeViewHelper($arguments);
		$this->assertEquals('else', $result);

		$staticResult = $this->executeViewHelperStatic($arguments);
		$this->assertEquals($result, $staticResult, 'The regular viewHelper output doesn\'t match the static output!');
	}

}
