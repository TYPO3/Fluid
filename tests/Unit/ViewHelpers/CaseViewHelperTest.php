<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */
use TYPO3Fluid\Fluid\ViewHelpers\CaseViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\SwitchViewHelper;

/**
 * Testcase for CaseViewHelper
 */
class CaseViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @var CaseViewHelper
     */
    protected $viewHelper;

    public function setUp()
    {
        parent::setUp();
        $this->viewHelper = $this->getMock(CaseViewHelper::class, ['renderChildren']);
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
    }

    /**
     * @test
     */
    public function viewHelperInitializesArguments()
    {
        $this->viewHelper->initializeArguments();
        $this->assertAttributeNotEmpty('argumentDefinitions', $this->viewHelper);
    }

    /**
     * @test
     * @expectedException \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function renderThrowsExceptionIfSwitchExpressionIsNotSetInViewHelperVariableContainer()
    {
        $this->viewHelper->setArguments(['value' => 'foo']);
        $this->viewHelper->initializeArgumentsAndRender();
    }

    /**
     * @test
     */
    public function renderReturnsChildNodesIfTheSpecifiedValueIsEqualToTheSwitchExpression()
    {
        $this->viewHelperVariableContainer->addOrUpdate(SwitchViewHelper::class, 'switchExpression', 'someValue');
        $renderedChildNodes = 'ChildNodes';
        $this->viewHelper->setArguments(['value' => 'someValue']);
        $this->viewHelper->expects($this->once())->method('renderChildren')->willReturn($renderedChildNodes);
        $this->assertSame($renderedChildNodes, $this->viewHelper->render());
    }

    /**
     * @test
     */
    public function renderReturnsAnEmptyStringIfTheSpecifiedValueIsNotEqualToTheSwitchExpression()
    {
        $this->viewHelperVariableContainer->addOrUpdate(SwitchViewHelper::class, 'switchExpression', 'someValue');
        $this->viewHelper->setArguments(['value' => 'someOtherValue']);
        $this->assertSame('', $this->viewHelper->initializeArgumentsAndRender());
    }
}
