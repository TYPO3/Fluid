<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */
use TYPO3Fluid\Fluid\ViewHelpers\IfViewHelper;

/**
 * Testcase for IfViewHelper
 */
class IfViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @var \TYPO3Fluid\Fluid\ViewHelpers\IfViewHelper
     */
    protected $viewHelper;

    /**
     * @var \TYPO3Fluid\Fluid\Core\ViewHelper\Arguments
     */
    protected $mockArguments;

    public function setUp()
    {
        parent::setUp();
        $this->viewHelper = $this->getAccessibleMock(IfViewHelper::class, ['renderThenChild', 'renderElseChild']);
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
        $this->viewHelper->initializeArguments();
    }

    /**
     * @test
     */
    public function viewHelperRendersThenChildIfConditionIsTrue()
    {
        $this->viewHelper->expects($this->at(0))->method('renderThenChild')->will($this->returnValue('foo'));

        $this->viewHelper->setArguments(['condition' => true]);
        $actualResult = $this->viewHelper->render();
        $this->assertEquals('foo', $actualResult);
    }

    /**
     * @test
     */
    public function viewHelperRendersElseChildIfConditionIsFalse()
    {
        $this->viewHelper->expects($this->at(0))->method('renderElseChild')->will($this->returnValue('foo'));

        $this->viewHelper->setArguments(['condition' => false]);
        $actualResult = $this->viewHelper->render();
        $this->assertEquals('foo', $actualResult);
    }
}
