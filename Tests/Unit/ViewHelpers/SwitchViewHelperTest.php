<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase;

require_once(__DIR__ . '/ViewHelperBaseTestcase.php');

/**
 * Testcase for SwitchViewHelper
 */
class SwitchViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @var \TYPO3\Fluid\ViewHelpers\SwitchViewHelper
	 */
	protected $viewHelper;

	public function setUp() {
		parent::setUp();
		$this->viewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\SwitchViewHelper', array('renderChildren'));
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
		$this->viewHelper->initializeArguments();
	}

	/**
	 * @test
	 */
	public function renderSetsSwitchExpressionInViewHelperVariableContainer() {
		$switchExpression = new \stdClass();
		$this->viewHelperVariableContainer->expects($this->at(2))->method('addOrUpdate')->with('TYPO3\Fluid\ViewHelpers\SwitchViewHelper', 'switchExpression', $switchExpression);
		$this->viewHelper->render($switchExpression);
	}

	/**
	 * @test
	 */
	public function renderRemovesSwitchExpressionFromViewHelperVariableContainerAfterInvocation() {
		$this->viewHelperVariableContainer->expects($this->at(4))->method('remove')->with('TYPO3\Fluid\ViewHelpers\SwitchViewHelper', 'switchExpression');
		$this->viewHelper->render('switchExpression');
	}
}
