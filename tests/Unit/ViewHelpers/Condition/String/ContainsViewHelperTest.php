<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Condition\String;

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
	 * @test
	 */
	public function rendersThenChildIfConditionMatched() {
		$arguments = array(
			'then' => 'then',
			'else' => 'else',
			'haystack' => 'foobar',
			'needle' => 'bar'
		);
		$result = $this->executeViewHelper($arguments);
		$this->assertEquals('then', $result);
	}

	/**
	 * @test
	 */
	public function rendersThenChildIfConditionMatchedStatic() {
		$arguments = array(
			'then' => 'then',
			'else' => 'else',
			'haystack' => 'foobar',
			'needle' => 'bar'
		);
		$staticResult = $this->executeViewHelperStatic($arguments);
		$this->assertEquals('then', $staticResult);
	}

	/**
	 * @test
	 */
	public function rendersElseChildIfConditionNotMatched() {
		$arguments = array(
			'then' => 'then',
			'else' => 'else',
			'haystack' => 'foobar',
			'needle' => 'baz'
		);
		$result = $this->executeViewHelper($arguments);
		$this->assertEquals('else', $result);
	}

	/**
	 * @test
	 */
	public function rendersElseChildIfConditionNotMatchedStatic() {
		$arguments = array(
			'then' => 'then',
			'else' => 'else',
			'haystack' => 'foobar',
			'needle' => 'baz'
		);
		$staticResult = $this->executeViewHelperStatic($arguments);
		$this->assertEquals('else', $staticResult);
	}

}
