<?php
namespace NamelessCoder\Fluid\Tests\Unit\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\Core\ViewHelper\ArgumentDefinition;
use NamelessCoder\Fluid\Tests\UnitTestCase;

/**
 * Testcase for \NamelessCoder\Fluid\Core\ViewHelper\ArgumentDefinition
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
