<?php
namespace TYPO3\Fluid\Tests\Unit\Core\Variables;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3\Fluid\Tests\UnitTestCase;

/**
 * Testcase for TemplateVariableContainer
 */
class StandardVariableProviderTest extends UnitTestCase {

	/**
	 * @var StandardVariableProvider
	 */
	protected $variableProvider;

	/**
	 */
	public function setUp() {
		$this->variableProvider = new StandardVariableProvider();
	}

	/**
	 */
	public function tearDown() {
		unset($this->variableProvider);
	}

	/**
	 * @test
	 */
	public function testUnsetAsArrayAccess() {
		$this->variableProvider->add('variable', 'test');
		unset($this->variableProvider['variable']);
		$this->assertFalse($this->variableProvider->exists('variable'));
	}

	/**
	 * @test
	 */
	public function addedObjectsCanBeRetrievedAgain() {
		$object = 'StringObject';
		$this->variableProvider->add('variable', $object);
		$this->assertSame($this->variableProvider->get('variable'), $object, 'The retrieved object from the context is not the same as the stored object.');
	}

	/**
	 * @test
	 */
	public function addedObjectsCanBeRetrievedAgainUsingArrayAccess() {
		$object = 'StringObject';
		$this->variableProvider['variable'] = $object;
		$this->assertSame($this->variableProvider->get('variable'), $object);
		$this->assertSame($this->variableProvider['variable'], $object);
	}

	/**
	 * @test
	 */
	public function addedObjectsExistInArray() {
		$object = 'StringObject';
		$this->variableProvider->add('variable', $object);
		$this->assertTrue($this->variableProvider->exists('variable'));
		$this->assertTrue(isset($this->variableProvider['variable']));
	}

	/**
	 * @test
	 */
	public function addedObjectsExistInAllIdentifiers() {
		$object = 'StringObject';
		$this->variableProvider->add('variable', $object);
		$this->assertEquals($this->variableProvider->getAllIdentifiers(), array('variable'), 'Added key is not visible in getAllIdentifiers');
	}

	/**
	 * @test
	 */
	public function gettingNonexistentValueReturnsNull() {
		$result = $this->variableProvider->get('nonexistent');
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function removeReallyRemovesVariables() {
		$this->variableProvider->add('variable', 'string1');
		$this->variableProvider->remove('variable');
		$result = $this->variableProvider->get('variable');
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function getAllShouldReturnAllVariables() {
		$this->variableProvider->add('name', 'Simon');
		$this->assertSame(array('name' => 'Simon'), $this->variableProvider->getAll());
	}

	/**
	 * @test
	 */
	public function testSleepReturnsExpectedPropertyNames() {
		$subject = new StandardVariableProvider();
		$properties = $subject->__sleep();
		$this->assertContains('variables', $properties);
	}

}
