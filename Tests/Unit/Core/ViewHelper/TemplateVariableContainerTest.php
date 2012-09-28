<?php
namespace TYPO3\Fluid\Tests\Unit\Core\ViewHelper;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Testcase for TemplateVariableContainer
 *
 */
class TemplateVariableContainerTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @var \TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer
	 */
	protected $variableContainer;

	/**
	 */
	public function setUp() {
		$this->variableContainer = new \TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer();
	}

	/**
	 */
	public function tearDown() {
		unset($this->variableContainer);
	}

	/**
	 * @test
	 */
	public function addedObjectsCanBeRetrievedAgain() {
		$object = "StringObject";
		$this->variableContainer->add("variable", $object);
		$this->assertSame($this->variableContainer->get('variable'), $object, 'The retrieved object from the context is not the same as the stored object.');
	}

	/**
	 * @test
	 */
	public function addedObjectsCanBeRetrievedAgainUsingArrayAccess() {
		$object = "StringObject";
		$this->variableContainer['variable'] = $object;
		$this->assertSame($this->variableContainer->get('variable'), $object);
		$this->assertSame($this->variableContainer['variable'], $object);
	}

	/**
	 * @test
	 */
	public function addedObjectsExistInArray() {
		$object = "StringObject";
		$this->variableContainer->add("variable", $object);
		$this->assertTrue($this->variableContainer->exists('variable'));
		$this->assertTrue(isset($this->variableContainer['variable']));
	}

	/**
	 * @test
	 */
	public function addedObjectsExistInAllIdentifiers() {
		$object = "StringObject";
		$this->variableContainer->add("variable", $object);
		$this->assertEquals($this->variableContainer->getAllIdentifiers(), array('variable'), 'Added key is not visible in getAllIdentifiers');
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception\InvalidVariableException
	 */
	public function duplicateIdentifiersThrowException() {
		$this->variableContainer->add('variable', 'string1');
		$this->variableContainer['variable'] = 'string2';
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception\InvalidVariableException
	 */
	public function addingReservedIdentifiersThrowException() {
		$this->variableContainer->add('TrUe', 'someValue');
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception\InvalidVariableException
	 */
	public function gettingNonexistentValueThrowsException() {
		$this->variableContainer->get('nonexistent');
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception\InvalidVariableException
	 */
	public function deletingNonexistentValueThrowsException() {
		$this->variableContainer->remove('nonexistent');
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception\InvalidVariableException
	 */
	public function removeReallyRemovesVariables() {
		$this->variableContainer->add('variable', 'string1');
		$this->variableContainer->remove('variable');
		$this->variableContainer->get('variable');
	}

	/**
	 * @test
	 */
	public function getAllShouldReturnAllVariables() {
		$this->variableContainer->add('name', 'Simon');
		$this->assertSame(array('name' => 'Simon'), $this->variableContainer->get('_all'));
	}

	/**
	 * Data provider for reserved variable names and what they're representing
	 * @return array Signature: $identifier, $expectedValue
	 */
	public function reservedVariableNameDataProvider() {
		return array(
			array('true', TRUE), array('false', FALSE),
			array('on', TRUE), array('off', FALSE),
			array('yes', TRUE), array('no', FALSE),
			array('_all', array())
		);
	}

	/**
	 * @test
	 * @param string $identifier
	 * @param mixed $expected
	 * @dataProvider reservedVariableNameDataProvider
	 */
	public function gettingAReservedVariableMatchesItsExpectation($identifier, $expected) {
		$this->assertSame($this->variableContainer->get($identifier), $expected);
	}

	/**
	 * @test
	 * @param string $identifier
	 * @dataProvider reservedVariableNameDataProvider
	 * @expectedException TYPO3\Fluid\Core\ViewHelper\Exception\InvalidVariableException
	 */
	public function addingVariableWhichIsReservedThrowsException($identifier) {
		$this->variableContainer->add($identifier, 'foo');
	}

	/**
	 * @test
	 * @param string $identifier
	 * @dataProvider reservedVariableNameDataProvider
	 */
	public function reservedVariableNameIsAlwaysConsideredExisting($identifier) {
		$this->assertTrue($this->variableContainer->exists($identifier), sprintf('The reserved variable "%s" should be considered existing, but is not.', $identifier));
	}

	/**
	 * @test
	 * @param string $identifier
	 * @dataProvider reservedVariableNameDataProvider
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception\InvalidVariableException
	 **/
	public function attemptToGetReservedVariableInUncoveredLetterCaseThrowsException($identifier) {
		$this->variableContainer->get(strtoupper($identifier));
	}

	/**
	 * @test
	 * @param string $identifier
	 * @dataProvider reservedVariableNameDataProvider
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception\InvalidVariableException
	 **/
	public function attemptToSetReservedVariableInUncoveredLetterCaseThrowsException($identifier) {
		$this->variableContainer->add(strtoupper($identifier), 'foo');
	}
}

?>