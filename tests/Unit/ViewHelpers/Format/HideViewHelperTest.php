<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Format;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\ViewHelpers\Format\HideViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\Format\RawViewHelper;

/**
 * Test for \TYPO3Fluid\Fluid\ViewHelpers\Format\RawViewHelper
 */
class HideViewHelperTest extends UnitTestCase
{
    /**
     * @test
     */
    public function executesButDoesNotOutput()
    {
        $object = new \stdClass();
        $object->touched = false;
        $closure = function() use ($object) {
            $object->touched = true;
            return 'mustnotbeseen';
        };
        $actualResult = HideViewHelper::renderStatic([], $closure, new RenderingContextFixture());
        $this->assertNull($actualResult, 'HideViewHelper caused vislble output');
        $this->assertTrue($object->touched, 'HideViewHelper did not execute renderchildren closure');
    }
}
