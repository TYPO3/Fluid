<?php
namespace NamelessCoder\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Testcase for IfViewHelper
 */
class IfViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @var \NamelessCoder\Fluid\ViewHelpers\IfViewHelper
	 */
	protected $viewHelper;

	/**
	 * @var \NamelessCoder\Fluid\Core\ViewHelper\Arguments
	 */
	protected $mockArguments;

	public function setUp() {
		parent::setUp();
		$this->viewHelper = $this->getAccessibleMock('NamelessCoder\Fluid\ViewHelpers\IfViewHelper', array('renderThenChild', 'renderElseChild'));
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
		$this->viewHelper->initializeArguments();
	}

	/**
	 * @test
	 */
	public function viewHelperRendersThenChildIfConditionIsTrue() {
		$this->viewHelper->expects($this->at(0))->method('renderThenChild')->will($this->returnValue('foo'));

		$this->viewHelper->setArguments(array('condition' => TRUE));
		$actualResult = $this->viewHelper->render();
		$this->assertEquals('foo', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperRendersElseChildIfConditionIsFalse() {
		$this->viewHelper->expects($this->at(0))->method('renderElseChild')->will($this->returnValue('foo'));

		$this->viewHelper->setArguments(array('condition' => FALSE));
		$actualResult = $this->viewHelper->render();
		$this->assertEquals('foo', $actualResult);
	}
}
