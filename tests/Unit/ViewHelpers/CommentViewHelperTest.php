<?php
namespace NamelessCoder\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase;
use NamelessCoder\Fluid\ViewHelpers\CommentViewHelper;

/**
 * Testcase for CommentViewHelper
 */
class CommentViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @test
	 */
	public function testRenderReturnsNull() {
		$instance = new CommentViewHelper();
		$result = $instance->render();
		$this->assertNull($result);
	}

}
