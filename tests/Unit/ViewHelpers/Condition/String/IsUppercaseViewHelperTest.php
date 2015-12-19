<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Condition\String;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase;

/**
 * IsUppercaseViewHelperTest
 */
class IsUppercaseViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @test
	 */
	public function rendersThenChildIfFirstCharacterIsUppercase() {
		$arguments = array(
			'then' => 'then',
			'else' => 'else',
			'string' => 'Foobar',
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
	public function rendersThenChildIfAllCharactersAreUppercase() {
		$arguments = array(
			'then' => 'then',
			'else' => 'else',
			'string' => 'FOOBAR',
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
	public function rendersElseChildIfFirstCharacterIsNotUppercase() {
		$arguments = array(
			'then' => 'then',
			'else' => 'else',
			'string' => 'fooBar',
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
	public function rendersElseChildIfAllCharactersAreNotUppercase() {
		$arguments = array(
			'then' => 'then',
			'else' => 'else',
			'string' => 'FooBar',
			'fullString' => TRUE
		);
		$result = $this->executeViewHelper($arguments);
		$this->assertEquals('else', $result);

		$staticResult = $this->executeViewHelperStatic($arguments);
		$this->assertEquals($result, $staticResult, 'The regular viewHelper output doesn\'t match the static output!');
	}

}
