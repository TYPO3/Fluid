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
    public function renderReturnsUnmodifiedValueIfSpecified()
    {
        $value = 'input value " & äöüß@';
        $this->viewHelper->expects($this->never())->method('renderChildren');
        $this->viewHelper->setArguments(['value' => $value]);
        $actualResult = $this->viewHelper->render();
        $this->assertEquals($value, $actualResult);
    }

    /**
     * @test
     */
    public function renderReturnsUnmodifiedChildNodesIfNoValueIsSpecified()
    {
        $childNodes = 'input value " & äöüß@';
        $this->viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue($childNodes));
        $actualResult = $this->viewHelper->render();
        $this->assertEquals($childNodes, $actualResult);
    }
}
