<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Format;

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\ViewHelpers\Format\RawViewHelper;

class RawViewHelperTest extends UnitTestCase
{
    /**
     * @var RawViewHelper&MockObject
     */
    protected $viewHelper;

    public function setUp(): void
    {
        $this->viewHelper = $this->getMock(RawViewHelper::class, ['renderChildren']);
        $this->viewHelper->setRenderingContext(new RenderingContextFixture());
    }

    /**
     * @test
     */
    public function viewHelperDeactivatesEscapingInterceptor()
    {
        self::assertFalse($this->viewHelper->isOutputEscapingEnabled());
    }

    /**
     * @test
     */
    public function renderReturnsUnmodifiedValueIfSpecified()
    {
        $value = 'input value " & äöüß@';
        $this->viewHelper->expects(self::never())->method('renderChildren');
        $this->viewHelper->setArguments(['value' => $value]);
        $actualResult = $this->viewHelper->render();
        self::assertEquals($value, $actualResult);
    }

    /**
     * @test
     */
    public function renderReturnsUnmodifiedChildNodesIfNoValueIsSpecified()
    {
        $childNodes = 'input value " & äöüß@';
        $this->viewHelper->expects(self::once())->method('renderChildren')->willReturn($childNodes);
        $actualResult = $this->viewHelper->render();
        self::assertEquals($childNodes, $actualResult);
    }
}
