<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\ViewHelpers\CaseViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\DefaultCaseViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\SwitchViewHelper;

/**
 * Testcase for SwitchViewHelper
 */
class SwitchViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @var SwitchViewHelper
     */
    protected $viewHelper;

    /**
     * @var ViewHelperNode
     */
    protected $viewHelperNode;

    public function setUp(): void
    {
        parent::setUp();
        $this->viewHelper = $this->getMockBuilder(SwitchViewHelper::class)->setMethods(['renderChildren'])->getMock();
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
    }

    /**
     * @test
     */
    public function viewHelperInitializesArguments(): void
    {
        $this->assertNotEmpty($this->viewHelper->prepareArguments());
    }

    /**
     * @test
     */
    public function renderSetsSwitchExpressionInViewHelperVariableContainer(): void
    {
        $switchExpression = new \stdClass();
        $this->viewHelper->setArguments(['expression' => $switchExpression]);
        $output = $this->viewHelper->initializeArgumentsAndRender();
        $this->assertEquals('', $output);
    }

    /**
     * @test
     */
    public function renderRemovesSwitchExpressionFromViewHelperVariableContainerAfterInvocation(): void
    {
        $this->viewHelper->setArguments(['expression' => 'switchExpression']);
        $output = $this->viewHelper->initializeArgumentsAndRender();
        $this->assertEquals('', $output);
    }

    /**
     * @param array $childNodes
     * @param array $variables
     * @param mixed $expected
     * @test
     * @dataProvider getRetrieveContentFromChildNodesTestValues
     */
    public function retrieveContentFromChildNodesProcessesChildNodesCorrectly(array $childNodes, array $variables, $expected): void
    {
        $instance = $this->getAccessibleMock(SwitchViewHelper::class, ['dummy']);
        $context = new RenderingContextFixture();
        $context->getViewHelperVariableContainer()->addOrUpdate(SwitchViewHelper::class, 'break', false);
        foreach ($variables as $name => $value) {
            $context->getViewHelperVariableContainer()->addOrUpdate(SwitchViewHelper::class, $name, $value);
        }
        $instance->_set('viewHelperVariableContainer', $context->getViewHelperVariableContainer());
        $instance->_set('renderingContext', $context);
        $method = new \ReflectionMethod(SwitchViewHelper::class, 'retrieveContentFromChildNodes');
        $method->setAccessible(true);
        $result = $method->invokeArgs($instance, [$childNodes]);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getRetrieveContentFromChildNodesTestValues(): array
    {
        $matchingNode = $this->getMock(ViewHelperNode::class, ['evaluate', 'getViewHelperClassName'], [], false, false);
        $matchingNode->method('getViewHelperClassName')->willReturn(CaseViewHelper::class);
        $matchingNode->method('evaluate')->willReturn('foo');
        $notMatchingNode = $this->getMock(ViewHelperNode::class, ['evaluate', 'getViewHelperClassName'], [], false, false);
        $notMatchingNode->method('getViewHelperClassName')->willReturn(CaseViewHelper::class);
        $notMatchingNode->method('evaluate')->willReturn('');
        $notMatchingNode->method('getViewHelperClassName')->willReturn(CaseViewHelper::class);
        $defaultCaseNode = $this->getMock(ViewHelperNode::class, ['evaluate', 'getViewHelperClassName'], [], false, false);
        $defaultCaseNode->method('evaluate')->willReturn('default');
        $defaultCaseNode->method('getViewHelperClassName')->willReturn(DefaultCaseViewHelper::class);
        $textNode = $this->getMock(TextNode::class, [], [], false, false);
        $objectAccessorNode = $this->getMock(ObjectAccessorNode::class, [], [], false, false);
        return [
            'empty switch' => [[], ['switchExpression' => false], null],
            'single case matching' => [[clone $matchingNode], ['switchExpression' => 'foo'], 'foo'],
            'two case without break' => [[clone $matchingNode, clone $notMatchingNode], ['switchExpression' => 'foo'], ''],
            'single case not matching with default last' => [[clone $matchingNode, clone $defaultCaseNode], ['switchExpression' => 'bar'], 'default'],
            'skips non-ViewHelper nodes' => [[$textNode, $objectAccessorNode, clone $matchingNode], ['switchExpression' => 'foo'], 'foo']
        ];
    }

    /**
     * @test
     */
    public function retrieveContentFromChildNodesReturnsBreaksOnBreak(): void
    {
        $instance = $this->getAccessibleMock(SwitchViewHelper::class, ['dummy']);
        $context = new RenderingContextFixture();
        $context->getViewHelperVariableContainer()->addOrUpdate(SwitchViewHelper::class, 'switchExpression', 'foo');
        $context->getViewHelperVariableContainer()->addOrUpdate(SwitchViewHelper::class, 'break', false);
        $instance->_set('viewHelperVariableContainer', $context->getViewHelperVariableContainer());
        $instance->_set('renderingContext', $context);
        $matchingCaseViewHelper = new CaseViewHelper();
        $matchingCaseViewHelper->setRenderChildrenClosure(function (): string {
            return 'foo-childcontent';
        });
        $breakingMatchingCaseNode = $this->getAccessibleMock(ViewHelperNode::class, ['getViewHelperClassName', 'getUninitializedViewHelper'], [], '', false);
        $breakingMatchingCaseNode->_set('arguments', ['value' => 'foo']);
        $breakingMatchingCaseNode->_set('uninitializedViewHelper', $matchingCaseViewHelper);
        $breakingMatchingCaseNode->method('getViewHelperClassName')->willReturn(CaseViewHelper::class);
        $defaultCaseNode = $this->getMock(ViewHelperNode::class, ['getViewHelperClassName', 'evaluate'], [], false, false);
        $defaultCaseNode->method('getViewHelperClassName')->willReturn(DefaultCaseViewHelper::class);
        $defaultCaseNode->expects($this->never())->method('evaluate');

        $method = new \ReflectionMethod(SwitchViewHelper::class, 'retrieveContentFromChildNodes');
        $method->setAccessible(true);
        $result = $method->invokeArgs($instance, [[$breakingMatchingCaseNode, $defaultCaseNode]]);
        $this->assertEquals('foo-childcontent', $result);
    }
}
