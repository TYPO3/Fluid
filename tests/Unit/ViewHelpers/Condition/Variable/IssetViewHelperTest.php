<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Condition\Variable;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase;

/**
 * IssetViewHelperTest
 */
class IssetViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @test
	 */
	public function rendersThenChildIfVariableIsSet() {
		$arguments = array(
			'name' => 'test',
			'then' => 'then',
			'else' => 'else'
		);
		$variables = array(
			'test' => TRUE
		);
		$result = $this->executeViewHelper($arguments, $variables);
		$this->assertEquals($arguments['then'], $result);

		$staticResult = $this->executeViewHelperStatic($arguments, $variables);
		$this->assertEquals($result, $staticResult, 'The regular viewHelper output doesn\'t match the static output!');
	}

	/**
	 * @test
	 */
	public function rendersElseChildIfVariableIsNotSet() {
		$arguments = array(
			'name' => 'test',
			'then' => 'then',
			'else' => 'else'
		);
		$variables = array();
		$result = $this->executeViewHelper($arguments, $variables);
		$this->assertEquals($arguments['else'], $result);

		$staticResult = $this->executeViewHelperStatic($arguments, $variables);
		$this->assertEquals($result, $staticResult, 'The regular viewHelper output doesn\'t match the static output!');
	}

}
