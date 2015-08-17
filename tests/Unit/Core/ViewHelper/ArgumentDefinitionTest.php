<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for \TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition
 */
class ArgumentDefinitionTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function objectStoresDataCorrectly() {
		$name = 'This is a name';
		$description = 'Example desc';
		$type = 'string';
		$isRequired = TRUE;
		$isMethodParameter = TRUE;
		$argumentDefinition = new ArgumentDefinition($name, $type, $description, $isRequired, NULL);

		$this->assertEquals($argumentDefinition->getName(), $name, 'Name could not be retrieved correctly.');
		$this->assertEquals($argumentDefinition->getDescription(), $description, 'Description could not be retrieved correctly.');
		$this->assertEquals($argumentDefinition->getType(), $type, 'Type could not be retrieved correctly');
		$this->assertEquals($argumentDefinition->isRequired(), $isRequired, 'Required flag could not be retrieved correctly.');
	}
}
