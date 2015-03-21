<?php
namespace TYPO3\Fluid\Tests\Unit\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer;
use TYPO3\Fluid\Tests\UnitTestCase;

/**
 * Testcase for TemplateVariableContainer
 */
class TemplateVariableContainerTest extends UnitTestCase {

	/**
	 * @var TemplateVariableContainer
	 */
	protected $variableContainer;

	/**
	 */
	public function setUp() {
		$this->variableContainer = new TemplateVariableContainer();
	}

	/**
	 */
	public function tearDown() {
		unset($this->variableContainer);
	}

	/**
	 * @test
	 */
	public function testUnsetAsArrayAccess() {
		$this->variableContainer->add('variable', 'test');
		unset($this->variableContainer['variable']);
		$this->assertFalse($this->variableContainer->exists('variable'));
	}

	/**
	 * @test
	 */
	public function addedObjectsCanBeRetrievedAgain() {
		$object = 'StringObject';
		$this->variableContainer->add('variable', $object);
		$this->assertSame($this->variableContainer->get('variable'), $object, 'The retrieved object from the context is not the same as the stored object.');
	}

	/**
	 * @test
	 */
	public function addedObjectsCanBeRetrievedAgainUsingArrayAccess() {
		$object = 'StringObject';
		$this->variableContainer['variable'] = $object;
		$this->assertSame($this->variableContainer->get('variable'), $object);
		$this->assertSame($this->variableContainer['variable'], $object);
	}

	/**
	 * @test
	 */
	public function addedObjectsExistInArray() {
		$object = 'StringObject';
		$this->variableContainer->add('variable', $object);
		$this->assertTrue($this->variableContainer->exists('variable'));
		$this->assertTrue(isset($this->variableContainer['variable']));
	}

	/**
	 * @test
	 */
	public function addedObjectsExistInAllIdentifiers() {
		$object = 'StringObject';
		$this->variableContainer->add('variable', $object);
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
		$this->assertSame(array('name' => 'Simon'), $this->variableContainer->getAll());
	}

	/**
	 * @test
	 */
	public function testSleepReturnsExpectedPropertyNames() {
		$subject = new TemplateVariableContainer();
		$properties = $subject->__sleep();
		$this->assertContains('variables', $properties);
	}

}
