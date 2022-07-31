<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\ViewHelpers\CountViewHelper;

class CountViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @var CountViewHelper&MockObject
     */
    protected $viewHelper;

    public function setUp(): void
    {
        parent::setUp();
        $this->viewHelper = $this->getAccessibleMock(CountViewHelper::class, ['renderChildren']);
    }

    /**
     * @test
     */
    public function renderReturnsNumberOfElementsInAnArray()
    {
        $this->viewHelper->expects(self::never())->method('renderChildren');
        $expectedResult = 3;
        $this->arguments = ['subject' => ['foo', 'bar', 'Baz']];
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
        $actualResult = $this->viewHelper->initializeArgumentsAndRender();
        self::assertSame($expectedResult, $actualResult);
    }

    /**
     * @test
     */
    public function renderReturnsNumberOfElementsInAnArrayObject()
    {
        $this->viewHelper->expects(self::never())->method('renderChildren');
        $expectedResult = 2;
        $this->arguments = ['subject' => new \ArrayObject(['foo', 'bar'])];
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
        $actualResult = $this->viewHelper->initializeArgumentsAndRender();
        self::assertSame($expectedResult, $actualResult);
    }

    /**
     * @test
     */
    public function renderReturnsZeroIfGivenArrayIsEmpty()
    {
        $this->viewHelper->expects(self::never())->method('renderChildren');
        $expectedResult = 0;
        $this->arguments = ['subject' => []];
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
        $actualResult = $this->viewHelper->render();
        self::assertSame($expectedResult, $actualResult);
    }

    /**
     * @test
     */
    public function renderUsesChildrenAsSubjectIfGivenSubjectIsNull()
    {
        $this->viewHelper->expects(self::once())->method('renderChildren')
            ->willReturn(['foo', 'baz', 'bar']);
        $expectedResult = 3;
        $this->arguments = ['subject' => null];
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
        $actualResult = $this->viewHelper->initializeArgumentsAndRender();
        self::assertSame($expectedResult, $actualResult);
    }

    /**
     * @test
     */
    public function renderReturnsZeroIfGivenSubjectIsNullAndRenderChildrenReturnsNull()
    {
        $this->viewHelper->expects(self::once())->method('renderChildren')
            ->willReturn(null);
        $this->viewHelper->setArguments(['subject' => null]);
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
        $expectedResult = 0;
        $actualResult = $this->viewHelper->initializeArgumentsAndRender();
        self::assertSame($expectedResult, $actualResult);
    }

    /**
     * @test
     */
    public function renderThrowsExceptionIfGivenSubjectIsNotCountable()
    {
        $this->viewHelper->expects(self::never())->method('renderChildren');
        $this->viewHelper->setRenderingContext(new RenderingContextFixture());
        $object = new \stdClass();
        $this->viewHelper->setArguments(['subject' => $object]);
        $this->setExpectedException(\InvalidArgumentException::class);
        $this->viewHelper->initializeArgumentsAndRender();
    }
}
