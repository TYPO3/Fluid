<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase;
use TYPO3Fluid\Fluid\ViewHelpers\ElseViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\ThenViewHelper;

/**
 * Testcase for Condition ViewHelper
 */
class AbstractConditionViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @var AbstractConditionViewHelper|MockObject
     */
    protected $viewHelper;

    public function setUp(): void
    {
        parent::setUp();
        $this->viewHelper = $this->getAccessibleMock(AbstractConditionViewHelper::class, ['getChildNodes', 'renderChildren', 'hasArgument']);
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
    }

    /**
     * @test
     */
    public function renderThenChildReturnsAllChildrenIfNoThenViewHelperChildExists(): void
    {
        $this->viewHelper->expects($this->any())->method('renderChildren')->will($this->returnValue('foo'));
        $this->viewHelper->expects($this->any())->method('getChildNodes')->will($this->returnValue([]));

        $actualResult = $this->viewHelper->_call('renderThenChild');
        $this->assertEquals('foo', $actualResult);
    }

    /**
     * @test
     */
    public function renderThenChildReturnsThenViewHelperChildIfConditionIsTrueAndThenViewHelperChildExists(): void
    {
        $mockThenViewHelperNode = $this->getMock(ThenViewHelper::class, ['evaluate'], [], false, false);
        $mockThenViewHelperNode->expects($this->once())->method('evaluate')->with($this->renderingContext)->will($this->returnValue('ThenViewHelperResults'));
        $this->viewHelper->expects($this->any())->method('getChildNodes')->will($this->returnValue([$mockThenViewHelperNode]));

        $actualResult = $this->viewHelper->_call('renderThenChild');
        $this->assertEquals('ThenViewHelperResults', $actualResult);
    }

    /**
     * @test
     */
    public function renderThenChildReturnsValueOfThenArgumentIfItIsSpecified(): void
    {
        $this->viewHelper->expects($this->atLeastOnce())->method('hasArgument')->with('then')->will($this->returnValue(true));
        $this->arguments['then'] = 'ThenArgument';
        $this->injectDependenciesIntoViewHelper($this->viewHelper);

        $actualResult = $this->viewHelper->_call('renderThenChild');
        $this->assertEquals('ThenArgument', $actualResult);
    }

    /**
     * @test
     */
    public function renderThenChildReturnsEmptyStringIfChildNodesOnlyContainElseViewHelper(): void
    {
        $mockElseViewHelperNode = $this->getMock(ElseViewHelper::class, ['evaluate'], [], false, false);
        $this->viewHelper->expects($this->any())->method('getChildNodes')->will($this->returnValue([$mockElseViewHelperNode]));
        $this->viewHelper->expects($this->never())->method('renderChildren')->will($this->returnValue('Child nodes'));

        $actualResult = $this->viewHelper->_call('renderThenChild');
        $this->assertEquals('', $actualResult);
    }

    /**
     * @test
     */
    public function renderElseChildReturnsEmptyStringIfConditionIsFalseAndNoElseViewHelperChildExists(): void
    {
        $this->viewHelper->expects($this->any())->method('getChildNodes')->will($this->returnValue([]));
        $actualResult = $this->viewHelper->_call('renderElseChild');
        $this->assertEquals('', $actualResult);
    }

    /**
     * @test
     */
    public function renderElseChildRendersElseViewHelperChildIfConditionIsFalseAndNoThenViewHelperChildExists(): void
    {
        $mockElseViewHelperNode = $this->getMock(ElseViewHelper::class, ['evaluate', 'setRenderingContext'], [], false, false);
        $mockElseViewHelperNode->expects($this->once())->method('evaluate')->with($this->renderingContext)->will($this->returnValue('ElseViewHelperResults'));
        $this->viewHelper->expects($this->any())->method('getChildNodes')->will($this->returnValue([$mockElseViewHelperNode]));

        $actualResult = $this->viewHelper->_call('renderElseChild');
        $this->assertEquals('ElseViewHelperResults', $actualResult);
    }

    /**
     * @test
     */
    public function renderElseChildReturnsEmptyStringIfConditionIsFalseAndElseViewHelperChildIfArgumentConditionIsFalseToo(): void
    {
        $mockElseViewHelperNode = $this->getMock(ElseViewHelper::class, ['getViewHelperClassName', 'getParsedArguments', 'evaluate'], [], false, false);
        $mockElseViewHelperNode->expects($this->once())->method('getParsedArguments')->will($this->returnValue(['if' => false]));
        $mockElseViewHelperNode->expects($this->never())->method('evaluate');

        $this->viewHelper->expects($this->any())->method('getChildNodes')->will($this->returnValue([$mockElseViewHelperNode]));

        $this->injectDependenciesIntoViewHelper($this->viewHelper);

        $actualResult = $this->viewHelper->_call('renderElseChild');
        $this->assertEquals('', $actualResult);
    }

    /**
     * @test
     */
    public function thenArgumentHasPriorityOverChildNodesIfConditionIsTrue(): void
    {
        $mockThenViewHelperNode = $this->getMock(ThenViewHelper::class, ['evaluate', 'setRenderingContext'], [], false, false);
        $mockThenViewHelperNode->expects($this->never())->method('evaluate');

        $this->viewHelper->expects($this->atLeastOnce())->method('hasArgument')->with('then')->will($this->returnValue(true));
        $this->arguments['then'] = 'ThenArgument';

        $this->injectDependenciesIntoViewHelper($this->viewHelper);

        $actualResult = $this->viewHelper->_call('renderThenChild');
        $this->assertEquals('ThenArgument', $actualResult);
    }

    /**
     * @test
     */
    public function renderReturnsValueOfElseArgumentIfConditionIsFalse(): void
    {
        $this->viewHelper->expects($this->atLeastOnce())->method('hasArgument')->with('else')->will($this->returnValue(true));
        $this->arguments['else'] = 'ElseArgument';
        $this->injectDependenciesIntoViewHelper($this->viewHelper);

        $actualResult = $this->viewHelper->_call('renderElseChild');
        $this->assertEquals('ElseArgument', $actualResult);
    }

    /**
     * @test
     */
    public function elseArgumentHasPriorityOverChildNodesIfConditionIsFalse(): void
    {
        $mockElseViewHelperNode = $this->getMock(ElseViewHelper::class, ['evaluate', 'setRenderingContext'], [], false, false);
        $mockElseViewHelperNode->expects($this->never())->method('evaluate');

        $this->viewHelper->expects($this->atLeastOnce())->method('hasArgument')->with('else')->will($this->returnValue(true));
        $this->arguments['else'] = 'ElseArgument';
        $this->injectDependenciesIntoViewHelper($this->viewHelper);

        $actualResult = $this->viewHelper->_call('renderElseChild');
        $this->assertEquals('ElseArgument', $actualResult);
    }
}
