<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\ViewHelpers\ElseViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\ThenViewHelper;

/**
 * Testcase for Condition ViewHelper
 */
class AbstractConditionViewHelperTest extends UnitTestCase
{
    public function getStandardTestValues(): array
    {
        return [];
    }

    /**
     * @var AbstractConditionViewHelper|MockObject
     */
    protected $viewHelper;

    public function setUp(): void
    {
        parent::setUp();
        $this->viewHelper = $this->getAccessibleMock(AbstractConditionViewHelper::class, ['getChildren', 'evaluateChildNodes', 'condition']);
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function defaultConditionIsTrue(): void
    {
        $context = new RenderingContextFixture();
        $viewHelper = $this->getMockBuilder(AbstractConditionViewHelper::class)->getMockForAbstractClass();
        $viewHelper->getArguments()->assignAll(['then' => 'yes']);
        $this->assertSame('yes', $viewHelper->evaluate($context));
    }

    /**
     * @test
     */
    public function renderThenChildReturnsAllChildrenIfNoThenViewHelperChildExists(): void
    {
        $this->viewHelper->expects($this->any())->method('evaluateChildNodes')->will($this->returnValue('foo'));
        $this->viewHelper->expects($this->any())->method('getChildren')->will($this->returnValue([]));
        $this->viewHelper->expects($this->any())->method('condition')->will($this->returnValue(true));

        $context = new RenderingContextFixture();
        $actualResult = $this->viewHelper->onOpen($context)->evaluate($context);
        $this->assertEquals('foo', $actualResult);
    }

    /**
     * @test
     */
    public function renderThenChildReturnsThenViewHelperChildIfConditionIsTrueAndThenViewHelperChildExists(): void
    {
        $mockThenViewHelperNode = $this->getMock(ThenViewHelper::class, ['evaluate'], [], false, false);
        $mockThenViewHelperNode->expects($this->once())->method('evaluate')->will($this->returnValue('ThenViewHelperResults'));
        $this->viewHelper->expects($this->any())->method('getChildren')->will($this->returnValue([$mockThenViewHelperNode]));
        $this->viewHelper->expects($this->any())->method('condition')->will($this->returnValue(true));

        $context = new RenderingContextFixture();
        $actualResult = $this->viewHelper->onOpen($context)->evaluate($context);
        $this->assertEquals('ThenViewHelperResults', $actualResult);
    }

    /**
     * @test
     */
    public function renderThenChildReturnsValueOfThenArgumentIfItIsSpecified(): void
    {
        $this->viewHelper->expects($this->any())->method('condition')->will($this->returnValue(true));
        $arguments = [
            'then' => 'ThenArgument',
        ];

        $this->viewHelper->getArguments()->assignAll($arguments);
        $context = new RenderingContextFixture();
        $actualResult = $this->viewHelper->onOpen($context)->evaluate($context);
        $this->assertEquals('ThenArgument', $actualResult);
    }

    /**
     * @test
     */
    public function renderThenChildReturnsEmptyStringIfChildNodesOnlyContainElseViewHelper(): void
    {
        $mockElseViewHelperNode = $this->getMock(ElseViewHelper::class, ['evaluate'], [], false, false);
        $this->viewHelper->expects($this->any())->method('getChildren')->will($this->returnValue([$mockElseViewHelperNode]));
        $this->viewHelper->expects($this->any())->method('condition')->will($this->returnValue(true));
        $this->viewHelper->expects($this->never())->method('evaluateChildNodes')->will($this->returnValue('Child nodes'));

        $context = new RenderingContextFixture();
        $actualResult = $this->viewHelper->onOpen($context)->evaluate($context);
        $this->assertEquals('', $actualResult);
    }

    /**
     * @test
     */
    public function renderElseChildReturnsEmptyStringIfConditionIsFalseAndNoElseViewHelperChildExists(): void
    {
        $this->viewHelper->expects($this->any())->method('getChildren')->will($this->returnValue([]));
        $actualResult = $this->viewHelper->_call('renderElseChild');
        $this->assertEquals(null, $actualResult);
    }

    /**
     * @test
     */
    public function renderElseChildRendersElseViewHelperChildIfConditionIsFalseAndNoThenViewHelperChildExists(): void
    {
        $mockElseViewHelperNode = $this->getMock(ElseViewHelper::class, ['evaluate'], [], false, false);
        $this->viewHelper->expects($this->any())->method('condition')->will($this->returnValue(false));
        $mockElseViewHelperNode->expects($this->once())->method('evaluate')->will($this->returnValue('ElseViewHelperResults'));
        $this->viewHelper->expects($this->any())->method('getChildren')->will($this->returnValue([$mockElseViewHelperNode]));
        $arguments = [
            'condition' => false,
        ];

        $context = new RenderingContextFixture();
        $this->viewHelper->getArguments()->assignAll($arguments);
        $actualResult = $this->viewHelper->onOpen($context)->evaluate($context);
        $this->assertEquals('ElseViewHelperResults', $actualResult);
    }

    /**
     * @test
     */
    public function renderElseChildReturnsEmptyStringIfConditionIsFalseAndElseViewHelperChildIfArgumentConditionIsFalseToo(): void
    {
        $context = new RenderingContextFixture();
        $mockElseViewHelperNode = $this->getMock(ElseViewHelper::class, ['evaluate'], [], false, false);
        $mockElseViewHelperNode->getArguments()->assignAll(['if' => false]);
        $mockElseViewHelperNode->onOpen($context);
        $mockElseViewHelperNode->expects($this->never())->method('evaluate');


        $this->viewHelper->expects($this->any())->method('condition')->will($this->returnValue(false));
        $this->viewHelper->expects($this->any())->method('getChildren')->will($this->returnValue([$mockElseViewHelperNode]));

        $actualResult = $this->viewHelper->onOpen($context)->evaluate($context);
        $this->assertEquals(null, $actualResult);
    }

    /**
     * @test
     */
    public function thenArgumentHasPriorityOverChildNodesIfConditionIsTrue(): void
    {
        $this->viewHelper->expects($this->any())->method('condition')->will($this->returnValue(true));
        $this->viewHelper->expects($this->never())->method('getChildren');
        $arguments = [
            'then' => 'ThenArgument',
        ];

        $context = new RenderingContextFixture();
        $this->viewHelper->getArguments()->assignAll($arguments);
        $actualResult = $this->viewHelper->onOpen($context)->evaluate($context);
        $this->assertEquals('ThenArgument', $actualResult);
    }

    /**
     * @test
     */
    public function renderReturnsValueOfElseArgumentIfConditionIsFalse(): void
    {
        $arguments['else'] = 'ElseArgument';

        $context = new RenderingContextFixture();
        $this->viewHelper->getArguments()->assignAll($arguments);
        $actualResult = $this->viewHelper->onOpen($context)->evaluate($context);
        $this->assertEquals('ElseArgument', $actualResult);
    }

    /**
     * @test
     */
    public function elseArgumentHasPriorityOverChildNodesIfConditionIsFalse(): void
    {
        $mockElseViewHelperNode = $this->getMock(ElseViewHelper::class, ['evaluate'], [], false, false);
        $mockElseViewHelperNode->expects($this->never())->method('evaluate');
        $this->viewHelper->expects($this->any())->method('condition')->will($this->returnValue(false));

        $arguments['else'] = 'ElseArgument';

        $context = new RenderingContextFixture();
        $this->viewHelper->getArguments()->assignAll($arguments);
        $actualResult = $this->viewHelper->onOpen($context)->evaluate($context);
        $this->assertEquals('ElseArgument', $actualResult);
    }
}
