<?php
namespace NamelessCoder\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\ViewHelpers\DebugViewHelper;
use NamelessCoder\Fluid\ViewHelpers\DefaultCaseViewHelper;

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
		$this->setExpectedException('NamelessCoder\\Fluid\\Core\\ViewHelper\\Exception');
		$viewHelper->render();
	}

}
