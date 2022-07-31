<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\ViewHelpers\CycleViewHelper;

class CycleViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @var CycleViewHelper&MockObject
     */
    protected $viewHelper;

    public function setUp(): void
    {
        parent::setUp();
        $this->viewHelper = $this->getMock(CycleViewHelper::class, ['renderChildren']);
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
        $this->viewHelper->initializeArguments();
    }

    /**
     * @test
     */
    public function renderAddsCurrentValueToTemplateVariableContainerAndRemovesItAfterRendering()
    {
        $values = ['bar', 'Fluid'];
        $this->viewHelper->setArguments(['values' => $values, 'as' => 'innerVariable']);
        $output = $this->viewHelper->render();
        self::assertEquals('', $output);
    }

    /**
     * @test
     */
    public function renderThrowsExceptionWhenPassingObjectsToValuesThatAreNotTraversable()
    {
        $this->expectException(Exception::class);
        $object = new \stdClass();
        $this->viewHelper->setArguments(['values' => $object, 'as' => 'innerVariable']);
        $this->viewHelper->render();
    }

    /**
     * @test
     */
    public function renderReturnsChildNodesIfValuesIsNull()
    {
        $this->viewHelper->expects(self::once())->method('renderChildren')->willReturn('Child nodes');
        $this->viewHelper->setArguments(['values' => null, 'as' => 'foo']);
        self::assertEquals('Child nodes', $this->viewHelper->render());
    }

    /**
     * @test
     */
    public function renderReturnsChildNodesIfValuesIsAnEmptyArray()
    {
        $this->viewHelper->expects(self::once())->method('renderChildren')->willReturn('Child nodes');
        $this->viewHelper->setArguments(['values' => [], 'as' => 'foo']);
        self::assertEquals('Child nodes', $this->viewHelper->render());
    }

    /**
     * @test
     */
    public function renderIteratesThroughElementsOfTraversableObjects()
    {
        $traversableObject = new \ArrayObject(['key1' => 'value1', 'key2' => 'value2']);
        $this->viewHelper->setArguments(['values' => $traversableObject, 'as' => 'innerVariable']);
        $o1 = $this->viewHelper->render();
        $o2 = $this->viewHelper->render();
        $o3 = $this->viewHelper->render();
        self::assertEquals($o1, $o2);
        self::assertEquals($o2, $o3);
    }
}
