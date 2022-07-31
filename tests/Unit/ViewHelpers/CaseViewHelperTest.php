<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3Fluid\Fluid\ViewHelpers\CaseViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\SwitchViewHelper;

class CaseViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @var CaseViewHelper&MockObject
     */
    protected $viewHelper;

    public function setUp(): void
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
        self::assertAttributeNotEmpty('argumentDefinitions', $this->viewHelper);
    }

    /**
     * @test
     */
    public function renderThrowsExceptionIfSwitchExpressionIsNotSetInViewHelperVariableContainer()
    {
        $this->expectException(\TYPO3Fluid\Fluid\Core\ViewHelper\Exception::class);

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
        $this->viewHelper->expects(self::once())->method('renderChildren')->willReturn($renderedChildNodes);
        self::assertSame($renderedChildNodes, $this->viewHelper->render());
    }

    /**
     * @test
     */
    public function renderReturnsAnEmptyStringIfTheSpecifiedValueIsNotEqualToTheSwitchExpression()
    {
        $this->viewHelperVariableContainer->addOrUpdate(SwitchViewHelper::class, 'switchExpression', 'someValue');
        $this->viewHelper->setArguments(['value' => 'someOtherValue']);
        self::assertSame('', $this->viewHelper->initializeArgumentsAndRender());
    }
}
