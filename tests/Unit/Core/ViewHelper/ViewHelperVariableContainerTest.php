<?php
namespace NamelessCoder\Fluid\Tests\Unit\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\Tests\UnitTestCase;
use NamelessCoder\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use NamelessCoder\Fluid\View\TemplatePaths;

/**
 * Testcase for AbstractViewHelper
 */
class ViewHelperVariableContainerTest extends UnitTestCase {

	/**
	 * @var ViewHelperVariableContainer
	 */
	protected $viewHelperVariableContainer;

	protected function setUp() {
		$this->viewHelperVariableContainer = new ViewHelperVariableContainer();
	}

	/**
	 * @test
	 */
	public function storedDataCanBeReadOutAgain() {
		$variable = 'Hello world';
		$this->assertFalse($this->viewHelperVariableContainer->exists('NamelessCoder\Fluid\ViewHelpers\TestViewHelper', 'test'));
		$this->viewHelperVariableContainer->add('NamelessCoder\Fluid\ViewHelpers\TestViewHelper', 'test', $variable);
		$this->assertTrue($this->viewHelperVariableContainer->exists('NamelessCoder\Fluid\ViewHelpers\TestViewHelper', 'test'));

		$this->assertEquals($variable, $this->viewHelperVariableContainer->get('NamelessCoder\Fluid\ViewHelpers\TestViewHelper', 'test'));
	}

	/**
	 * @test
	 * @expectedException \NamelessCoder\Fluid\Core\ViewHelper\Exception\InvalidVariableException
	 */
	public function gettingNonNonExistentValueThrowsException() {
		$this->viewHelperVariableContainer->get('NamelessCoder\Fluid\ViewHelper\NonExistent', 'nonExistentKey');
	}

	/**
	 * @test
	 * @expectedException \NamelessCoder\Fluid\Core\ViewHelper\Exception\InvalidVariableException
	 */
	public function settingKeyWhichIsAlreadyStoredThrowsException() {
		$this->viewHelperVariableContainer->add('NamelessCoder\Fluid\ViewHelper\NonExistent', 'nonExistentKey', 'value1');
		$this->viewHelperVariableContainer->add('NamelessCoder\Fluid\ViewHelper\NonExistent', 'nonExistentKey', 'value2');
	}

	/**
	 * @test
	 */
	public function addOrUpdateSetsAKeyIfItDoesNotExistYet() {
		$this->viewHelperVariableContainer->add('NamelessCoder\Fluid\ViewHelper\NonExistent', 'nonExistentKey', 'value1');
		$this->assertEquals($this->viewHelperVariableContainer->get('NamelessCoder\Fluid\ViewHelper\NonExistent', 'nonExistentKey'), 'value1');
	}

	/**
	 * @test
	 */
	public function addOrUpdateOverridesAnExistingKey() {
		$this->viewHelperVariableContainer->add('NamelessCoder\Fluid\ViewHelper\NonExistent', 'someKey', 'value1');
		$this->viewHelperVariableContainer->addOrUpdate('NamelessCoder\Fluid\ViewHelper\NonExistent', 'someKey', 'value2');
		$this->assertEquals($this->viewHelperVariableContainer->get('NamelessCoder\Fluid\ViewHelper\NonExistent', 'someKey'), 'value2');
	}

	/**
	 * @test
	 */
	public function aSetValueCanBeRemovedAgain() {
		$this->viewHelperVariableContainer->add('NamelessCoder\Fluid\ViewHelper\NonExistent', 'nonExistentKey', 'value1');
		$this->viewHelperVariableContainer->remove('NamelessCoder\Fluid\ViewHelper\NonExistent', 'nonExistentKey');
		$this->assertFalse($this->viewHelperVariableContainer->exists('NamelessCoder\Fluid\ViewHelper\NonExistent', 'nonExistentKey'));
	}

	/**
	 * @test
	 * @expectedException \NamelessCoder\Fluid\Core\ViewHelper\Exception\InvalidVariableException
	 */
	public function removingNonExistentKeyThrowsException() {
		$this->viewHelperVariableContainer->remove('NamelessCoder\Fluid\ViewHelper\NonExistent', 'nonExistentKey');
	}

	/**
	 * @test
	 */
	public function existsReturnsFalseIfTheSpecifiedKeyDoesNotExist() {
		$this->assertFalse($this->viewHelperVariableContainer->exists('NamelessCoder\Fluid\ViewHelper\NonExistent', 'nonExistentKey'));
	}

	/**
	 * @test
	 */
	public function existsReturnsTrueIfTheSpecifiedKeyExists() {
		$this->viewHelperVariableContainer->add('NamelessCoder\Fluid\ViewHelper\NonExistent', 'someKey', 'someValue');
		$this->assertTrue($this->viewHelperVariableContainer->exists('NamelessCoder\Fluid\ViewHelper\NonExistent', 'someKey'));
	}

	/**
	 * @test
	 */
	public function existsReturnsTrueIfTheSpecifiedKeyExistsAndIsNull() {
		$this->viewHelperVariableContainer->add('NamelessCoder\Fluid\ViewHelper\NonExistent', 'someKey', NULL);
		$this->assertTrue($this->viewHelperVariableContainer->exists('NamelessCoder\Fluid\ViewHelper\NonExistent', 'someKey'));
	}

	/**
	 * @test
	 */
	public function viewCanBeReadOutAgain() {
		$view = $this->getMockForAbstractClass('NamelessCoder\Fluid\View\AbstractTemplateView', array(new TemplatePaths()));
		$this->viewHelperVariableContainer->setView($view);
		$this->assertSame($view, $this->viewHelperVariableContainer->getView());
	}

	/**
	 * @test
	 */
	public function testSleepReturnsExpectedPropertyNames() {
		$subject = new ViewHelperVariableContainer();
		$properties = $subject->__sleep();
		$this->assertContains('objects', $properties);
	}

}
