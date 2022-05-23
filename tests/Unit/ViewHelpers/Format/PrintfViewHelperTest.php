<?php

namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Format;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase;
use TYPO3Fluid\Fluid\ViewHelpers\Format\PrintfViewHelper;

/**
 * Test for \TYPO3Fluid\Fluid\ViewHelpers\Format\PrintfViewHelper
 */
class PrintfViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @var \TYPO3Fluid\Fluid\ViewHelpers\Format\PrintfViewHelper
     */
    protected $viewHelper;

    public function setUp(): void
    {
        parent::setUp();
        $this->viewHelper = $this->getMock(PrintfViewHelper::class, ['renderChildren']);
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
    }

    /**
     * @test
     */
    public function viewHelperCanUseArrayAsArgument()
    {
        $this->viewHelper->expects(self::once())->method('renderChildren')->willReturn('%04d-%02d-%02d');
        $this->viewHelper->setArguments(['value' => null, 'arguments' => ['year' => 2009, 'month' => 4, 'day' => 5]]);
        $actualResult = $this->viewHelper->initializeArgumentsAndRender();
        self::assertEquals('2009-04-05', $actualResult);
    }

    /**
     * @test
     */
    public function viewHelperCanSwapMultipleArguments()
    {
        $this->viewHelper->expects(self::once())->method('renderChildren')->willReturn('%2$s %1$d %3$s %2$s');
        $this->viewHelper->setArguments(['value' => null, 'arguments' => [123, 'foo', 'bar']]);
        $actualResult = $this->viewHelper->initializeArgumentsAndRender();
        self::assertEquals('foo 123 bar foo', $actualResult);
    }
}
