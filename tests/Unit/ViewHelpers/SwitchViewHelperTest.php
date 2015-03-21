<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\ViewHelpers\SwitchViewHelper;

/**
 * Testcase for SwitchViewHelper
 */
class SwitchViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @var SwitchViewHelper
	 */
	protected $viewHelper;

	public function setUp() {
		parent::setUp();
		$this->viewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\SwitchViewHelper', array('renderChildren'));
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
	}

	/**
	 * @test
	 */
	public function renderSetsSwitchExpressionInViewHelperVariableContainer() {
		$switchExpression = new \stdClass();
		$this->viewHelper->setArguments(array('expression' => $switchExpression));
		$this->viewHelper->initializeArgumentsAndRender();
	}

	/**
	 * @test
	 */
	public function renderRemovesSwitchExpressionFromViewHelperVariableContainerAfterInvocation() {
		$this->viewHelper->setArguments(array('expression' => 'switchExpression'));
		$this->viewHelper->initializeArgumentsAndRender();
	}
}
