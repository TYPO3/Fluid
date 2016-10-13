<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\ViewHelpers\CycleViewHelper;

/**
 * Testcase for CycleViewHelper
 */
class CycleViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @var CycleViewHelper
     */
    protected $viewHelper;

    public function setUp()
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
        $this->viewHelper->render();
    }

    /**
     * @test
     * @expectedException \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function renderThrowsExceptionWhenPassingObjectsToValuesThatAreNotTraversable()
    {
        $object = new \stdClass();
        $this->viewHelper->setArguments(['values' => $object, 'as' => 'innerVariable']);
        $this->viewHelper->render();
    }

    /**
     * @test
     */
    public function renderReturnsChildNodesIfValuesIsNull()
    {
        $this->viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue('Child nodes'));
        $this->viewHelper->setArguments(['values' => null, 'as' => 'foo']);
        $this->assertEquals('Child nodes', $this->viewHelper->render(null, 'foo'));
    }

    /**
     * @test
     */
    public function renderReturnsChildNodesIfValuesIsAnEmptyArray()
    {
        $this->viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue('Child nodes'));
        $this->viewHelper->setArguments(['values' => [], 'as' => 'foo']);
        $this->assertEquals('Child nodes', $this->viewHelper->render());
    }

    /**
     * @test
     */
    public function renderIteratesThroughElementsOfTraversableObjects()
    {
        $traversableObject = new \ArrayObject(['key1' => 'value1', 'key2' => 'value2']);
        $this->viewHelper->setArguments(['values' => $traversableObject, 'as' => 'innerVariable']);
        $this->viewHelper->render();
        $this->viewHelper->render();
        $this->viewHelper->render();
    }
}
