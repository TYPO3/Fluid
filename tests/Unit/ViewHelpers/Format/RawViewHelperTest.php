<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Format;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\ViewHelpers\Format\RawViewHelper;

/**
 * Test for \TYPO3Fluid\Fluid\ViewHelpers\Format\RawViewHelper
 */
class RawViewHelperTest extends UnitTestCase
{

    /**
     * @var RawViewHelper
     */
    protected $viewHelper;

    public function setUp()
    {
        $this->viewHelper = $this->getMock(RawViewHelper::class, ['renderChildren']);
        $this->viewHelper->setRenderingContext(new RenderingContextFixture());
    }

    /**
     * @test
     */
    public function viewHelperDeactivatesEscapingInterceptor()
    {
        $this->assertFalse($this->viewHelper->isOutputEscapingEnabled());
    }

    /**
     * @test
     */
    public function renderReturnsUnmodifiedValue()
    {
        $value = 'input value " & äöüß@';
        $actualResult = RawViewHelper::renderStatic(['value' => $value], function() use ($value) { return $value; }, new RenderingContextFixture());
        $this->assertEquals($value, $actualResult);
    }
}
