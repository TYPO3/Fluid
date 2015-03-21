<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\ViewHelpers\DebugViewHelper;
use TYPO3\Fluid\ViewHelpers\DefaultCaseViewHelper;

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
		$this->setExpectedException('TYPO3\\Fluid\\Core\\ViewHelper\\Exception');
		$viewHelper->render();
	}

}
