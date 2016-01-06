<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\ViewHelpers\DefaultCaseViewHelper;

/**
 * Testcase for DefaultCaseViewHelper
 */
class DefaultCaseViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @test
	 */
	public function testThrowsExceptionIfUsedOutsideSwitch() {
		$viewHelper = new DefaultCaseViewHelper();
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$this->setExpectedException(Exception::class);
		$viewHelper->render();
	}

	/**
	 * @test
	 */
	public function testCallsRenderChildrenWhenUsedInsideSwitch() {
		$viewHelper = $this->getAccessibleMock(DefaultCaseViewHelper::class, array('renderChildren'));
		$viewHelper->expects($this->once())->method('renderChildren');
		$renderingContext = $this->getMock(RenderingContext::class, array('getViewHelperVariableContainer'), array(), '', FALSE);
		$variableContainer = $this->getMock(ViewHelperVariableContainer::class, array('exists'));
		$variableContainer->expects($this->once())->method('exists')->willReturn(TRUE);
		$renderingContext->expects($this->once())->method('getViewHelperVariableContainer')->willReturn($variableContainer);
		$viewHelper->_set('renderingContext', $renderingContext);
		$viewHelper->render();
	}

}
