<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Testcase for CaseViewHelper
 */
class CaseViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @var \TYPO3\Fluid\ViewHelpers\CaseViewHelper
	 */
	protected $viewHelper;

	public function setUp() {
		parent::setUp();
		$this->viewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\CaseViewHelper', array('renderChildren'));
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception
	 */
	public function renderThrowsExceptionIfSwitchExpressionIsNotSetInViewHelperVariableContainer() {
		$this->viewHelper->setArguments(array('value' => 'foo'));
		$this->viewHelper->initializeArgumentsAndRender();
	}

	/**
	 * @test
	 */
	public function renderReturnsChildNodesIfTheSpecifiedValueIsEqualToTheSwitchExpression() {
		$this->viewHelperVariableContainer->addOrUpdate('TYPO3\Fluid\ViewHelpers\SwitchViewHelper', 'switchExpression', 'someValue');
		$renderedChildNodes = 'ChildNodes';
		$this->viewHelper->setArguments(array('value' => 'someValue'));
		$this->viewHelper->expects($this->once())->method('renderChildren')->willReturn($renderedChildNodes);
		$this->assertSame($renderedChildNodes, $this->viewHelper->render());
	}

	/**
	 * @test
	 */
	public function renderReturnsAnEmptyStringIfTheSpecifiedValueIsNotEqualToTheSwitchExpression() {
		$this->viewHelperVariableContainer->addOrUpdate('TYPO3\Fluid\ViewHelpers\SwitchViewHelper', 'switchExpression', 'someValue');
		$this->viewHelper->setArguments(array('value' => 'someOtherValue'));
		$this->assertSame('', $this->viewHelper->initializeArgumentsAndRender());
	}

}
