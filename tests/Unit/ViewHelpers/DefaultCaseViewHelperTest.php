<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\ViewHelpers\DebugViewHelper;
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
		$this->setExpectedException('TYPO3Fluid\\Fluid\\Core\\ViewHelper\\Exception');
		$viewHelper->render();
	}

}
