<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\ViewHelpers\CountViewHelper;

/**
 * Testcase for CountViewHelper
 */
class CountViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @var CountViewHelper
     */
    protected $viewHelper;

    public function setUp()
    {
        parent::setUp();
        $this->viewHelper = $this->getAccessibleMock(CountViewHelper::class, ['renderChildren']);
    }

    /**
     * @test
     */
    public function renderReturnsNumberOfElementsInAnArray()
    {
        $this->viewHelper->expects($this->never())->method('renderChildren');
        $expectedResult = 3;
        $this->arguments = ['subject' => ['foo', 'bar', 'Baz']];
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
        $actualResult = $this->viewHelper->initializeArgumentsAndRender();
        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @test
     */
    public function renderReturnsNumberOfElementsInAnArrayObject()
    {
        $this->viewHelper->expects($this->never())->method('renderChildren');
        $expectedResult = 2;
        $this->arguments = ['subject' => new \ArrayObject(['foo', 'bar'])];
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
        $actualResult = $this->viewHelper->initializeArgumentsAndRender();
        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @test
     */
    public function renderReturnsZeroIfGivenArrayIsEmpty()
    {
        $this->viewHelper->expects($this->never())->method('renderChildren');
        $expectedResult = 0;
        $this->arguments = ['subject' => []];
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
        $actualResult = $this->viewHelper->render();
        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @test
     */
    public function renderUsesChildrenAsSubjectIfGivenSubjectIsNull()
    {
        $this->viewHelper->expects($this->once())->method('renderChildren')
            ->will($this->returnValue(['foo', 'baz', 'bar']));
        $expectedResult = 3;
        $this->arguments = ['subject' => null];
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
        $actualResult = $this->viewHelper->initializeArgumentsAndRender();
        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @test
     */
    public function renderReturnsZeroIfGivenSubjectIsNullAndRenderChildrenReturnsNull()
    {
        $this->viewHelper->expects($this->once())->method('renderChildren')
            ->will($this->returnValue(null));
        $this->viewHelper->setArguments(['subject' => null]);
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
        $expectedResult = 0;
        $actualResult = $this->viewHelper->initializeArgumentsAndRender();
        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function renderThrowsExceptionIfGivenSubjectIsNotCountable()
    {
        $object = new \stdClass();
        $this->viewHelper->setArguments(['subject' => $object]);
        $this->viewHelper->initializeArgumentsAndRender();
    }
}
