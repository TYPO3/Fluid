<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\ViewHelpers\CommentViewHelper;

/**
 * Testcase for CommentViewHelper
 */
class CommentViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @test
     */
    public function testRenderReturnsNull()
    {
        $instance = new CommentViewHelper();
        $result = $instance->render();
        $this->assertNull($result);
    }
}
