<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\View\TemplatePaths;

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
		$this->assertFalse($this->viewHelperVariableContainer->exists('TYPO3Fluid\Fluid\ViewHelpers\TestViewHelper', 'test'));
		$this->viewHelperVariableContainer->add('TYPO3Fluid\Fluid\ViewHelpers\TestViewHelper', 'test', $variable);
		$this->assertTrue($this->viewHelperVariableContainer->exists('TYPO3Fluid\Fluid\ViewHelpers\TestViewHelper', 'test'));

		$this->assertEquals($variable, $this->viewHelperVariableContainer->get('TYPO3Fluid\Fluid\ViewHelpers\TestViewHelper', 'test'));
	}

	/**
	 * @test
	 */
	public function addOrUpdateSetsAKeyIfItDoesNotExistYet() {
		$this->viewHelperVariableContainer->add('TYPO3Fluid\Fluid\ViewHelper\NonExistent', 'nonExistentKey', 'value1');
		$this->assertEquals($this->viewHelperVariableContainer->get('TYPO3Fluid\Fluid\ViewHelper\NonExistent', 'nonExistentKey'), 'value1');
	}

	/**
	 * @test
	 */
	public function addOrUpdateOverridesAnExistingKey() {
		$this->viewHelperVariableContainer->add('TYPO3Fluid\Fluid\ViewHelper\NonExistent', 'someKey', 'value1');
		$this->viewHelperVariableContainer->addOrUpdate('TYPO3Fluid\Fluid\ViewHelper\NonExistent', 'someKey', 'value2');
		$this->assertEquals($this->viewHelperVariableContainer->get('TYPO3Fluid\Fluid\ViewHelper\NonExistent', 'someKey'), 'value2');
	}

	/**
	 * @test
	 */
	public function aSetValueCanBeRemovedAgain() {
		$this->viewHelperVariableContainer->add('TYPO3Fluid\Fluid\ViewHelper\NonExistent', 'nonExistentKey', 'value1');
		$this->viewHelperVariableContainer->remove('TYPO3Fluid\Fluid\ViewHelper\NonExistent', 'nonExistentKey');
		$this->assertFalse($this->viewHelperVariableContainer->exists('TYPO3Fluid\Fluid\ViewHelper\NonExistent', 'nonExistentKey'));
	}

	/**
	 * @test
	 */
	public function existsReturnsFalseIfTheSpecifiedKeyDoesNotExist() {
		$this->assertFalse($this->viewHelperVariableContainer->exists('TYPO3Fluid\Fluid\ViewHelper\NonExistent', 'nonExistentKey'));
	}

	/**
	 * @test
	 */
	public function existsReturnsTrueIfTheSpecifiedKeyExists() {
		$this->viewHelperVariableContainer->add('TYPO3Fluid\Fluid\ViewHelper\NonExistent', 'someKey', 'someValue');
		$this->assertTrue($this->viewHelperVariableContainer->exists('TYPO3Fluid\Fluid\ViewHelper\NonExistent', 'someKey'));
	}

	/**
	 * @test
	 */
	public function existsReturnsTrueIfTheSpecifiedKeyExistsAndIsNull() {
		$this->viewHelperVariableContainer->add('TYPO3Fluid\Fluid\ViewHelper\NonExistent', 'someKey', NULL);
		$this->assertTrue($this->viewHelperVariableContainer->exists('TYPO3Fluid\Fluid\ViewHelper\NonExistent', 'someKey'));
	}

	/**
	 * @test
	 */
	public function viewCanBeReadOutAgain() {
		$view = $this->getMockForAbstractClass('TYPO3Fluid\Fluid\View\AbstractTemplateView', array(new TemplatePaths()));
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

	/**
	 * @test
	 */
	public function testGetReturnsDefaultIfRequestedVariableDoesNotExist() {
		$subject = new ViewHelperVariableContainer();
		$this->assertEquals('test', $subject->get('foo', 'bar', 'test'));
	}

}
