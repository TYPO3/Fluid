<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Condition\String;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase;

/**
 * IsLowercaseViewHelperTest
 */
class IsLowercaseViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @test
	 */
	public function rendersThenChildIfFirstCharacterIsLowercase() {
		$arguments = array(
			'then' => 'then',
			'else' => 'else',
			'string' => 'foobar',
			'fullString' => FALSE
		);
		$result = $this->executeViewHelper($arguments);
		$this->assertEquals('then', $result);

		$staticResult = $this->executeViewHelperStatic($arguments);
		$this->assertEquals($result, $staticResult, 'The regular viewHelper output doesn\'t match the static output!');
	}

	/**
	 * @test
	 */
	public function rendersThenChildIfAllCharactersAreLowercase() {
		$arguments = array(
			'then' => 'then',
			'else' => 'else',
			'string' => 'foobar',
			'fullString' => TRUE
		);
		$result = $this->executeViewHelper($arguments);
		$this->assertEquals('then', $result);

		$staticResult = $this->executeViewHelperStatic($arguments);
		$this->assertEquals($result, $staticResult, 'The regular viewHelper output doesn\'t match the static output!');
	}

	/**
	 * @test
	 */
	public function rendersElseChildIfFirstCharacterIsNotLowercase() {
		$arguments = array(
			'then' => 'then',
			'else' => 'else',
			'string' => 'FooBar',
			'fullString' => FALSE
		);
		$result = $this->executeViewHelper($arguments);
		$this->assertEquals('else', $result);

		$staticResult = $this->executeViewHelperStatic($arguments);
		$this->assertEquals($result, $staticResult, 'The regular viewHelper output doesn\'t match the static output!');
	}

	/**
	 * @test
	 */
	public function rendersElseChildIfAllCharactersAreNotLowercase() {
		$arguments = array(
			'then' => 'then',
			'else' => 'else',
			'string' => 'fooBar',
			'fullString' => TRUE
		);
		$result = $this->executeViewHelper($arguments);
		$this->assertEquals('else', $result);

		$staticResult = $this->executeViewHelperStatic($arguments);
		$this->assertEquals($result, $staticResult, 'The regular viewHelper output doesn\'t match the static output!');
	}

}
