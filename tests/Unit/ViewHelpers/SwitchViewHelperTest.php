<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\View\TemplateView;
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

    public function setUp()
    {
        parent::setUp();
        $this->viewHelper = $this->getMock(SwitchViewHelper::class, ['renderChildren']);
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
    }

    /**
     * @test
     */
    public function renderSetsSwitchExpressionInViewHelperVariableContainer()
    {
        $switchExpression = new \stdClass();
        $this->viewHelper->setArguments(['expression' => $switchExpression]);
        $this->viewHelper->initializeArgumentsAndRender();
    }

    /**
     * @test
     */
    public function renderRemovesSwitchExpressionFromViewHelperVariableContainerAfterInvocation()
    {
        $this->viewHelper->setArguments(['expression' => 'switchExpression']);
        $this->viewHelper->initializeArgumentsAndRender();
    }

    /**
     * @param NodeInterface[] $childNodes
     * @param array $variables
     * @param mixed $expected
     * @test
     * @dataProvider getRetrieveContentFromChildNodesTestValues
     */
    public function retrieveContentFromChildNodesProcessesChildNodesCorrectly(array $childNodes, array $variables, $expected)
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
    public function getRetrieveContentFromChildNodesTestValues()
    {
        $matchingNode = $this->getMock(ViewHelperNode::class, ['evaluate', 'getViewHelperClassName'], [], '', false);
        $matchingNode->method('getViewHelperClassName')->willReturn(CaseViewHelper::class);
        $matchingNode->method('evaluate')->willReturn('foo');
        $notMatchingNode = $this->getMock(ViewHelperNode::class, ['evaluate', 'getViewHelperClassName'], [], '', false);
        $notMatchingNode->method('getViewHelperClassName')->willReturn(CaseViewHelper::class);
        $notMatchingNode->method('evaluate')->willReturn('');
        $notMatchingNode->method('getViewHelperClassName')->willReturn(CaseViewHelper::class);
        $defaultCaseNode = $this->getMock(ViewHelperNode::class, ['evaluate', 'getViewHelperClassName'], [], '', false);
        $defaultCaseNode->method('evaluate')->willReturn('default');
        $defaultCaseNode->method('getViewHelperClassName')->willReturn(DefaultCaseViewHelper::class);
        $textNode = $this->getMock(TextNode::class, [], [], '', false);
        $objectAccessorNode = $this->getMock(ObjectAccessorNode::class, [], [], '', false);
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
    public function retrieveContentFromChildNodesReturnsBreaksOnBreak()
    {
        $instance = $this->getAccessibleMock(SwitchViewHelper::class, ['dummy']);
        $context = new RenderingContextFixture();
        $context->getViewHelperVariableContainer()->addOrUpdate(SwitchViewHelper::class, 'switchExpression', 'foo');
        $context->getViewHelperVariableContainer()->addOrUpdate(SwitchViewHelper::class, 'break', false);
        $instance->_set('viewHelperVariableContainer', $context->getViewHelperVariableContainer());
        $instance->_set('renderingContext', $context);
        $matchingCaseViewHelper = new CaseViewHelper();
        $matchingCaseViewHelper->setRenderChildrenClosure(function () {
            return 'foo-childcontent';
        });
        $breakingMatchingCaseNode = $this->getAccessibleMock(ViewHelperNode::class, ['getViewHelperClassName', 'getUninitializedViewHelper'], [], '', false);
        $breakingMatchingCaseNode->_set('arguments', ['value' => 'foo']);
        $breakingMatchingCaseNode->_set('uninitializedViewHelper', $matchingCaseViewHelper);
        $breakingMatchingCaseNode->method('getViewHelperClassName')->willReturn(CaseViewHelper::class);
        $defaultCaseNode = $this->getMock(ViewHelperNode::class, ['getViewHelperClassName', 'evaluate'], [], '', false);
        $defaultCaseNode->method('getViewHelperClassName')->willReturn(DefaultCaseViewHelper::class);
        $defaultCaseNode->expects($this->never())->method('evaluate');

        $method = new \ReflectionMethod(SwitchViewHelper::class, 'retrieveContentFromChildNodes');
        $method->setAccessible(true);
        $result = $method->invokeArgs($instance, [[$breakingMatchingCaseNode, $defaultCaseNode]]);
        $this->assertEquals('foo-childcontent', $result);
    }

    /**
     * @param ViewHelperNode $node
     * @param string $expectedCode
     * @param string $expectedInitialization
     * @test
     * @dataProvider getCompileTestValues
     */
    public function compileGeneratesExpectedPhpCode(ViewHelperNode $node, $expectedCode, $expectedInitialization)
    {
        $viewHelper = new SwitchViewHelper();
        $compiler = new TemplateCompiler();
        $code = $viewHelper->compile('$arguments', 'closure', $initializationCode, $node, $compiler);
        $this->assertEquals($expectedCode, $code);
        $this->assertEquals($expectedInitialization, $initializationCode);
    }

    /**
     * @return array
     */
    public function getCompileTestValues()
    {
        $renderingContext = new RenderingContext($this->getMock(TemplateView::class), ['dummy'], [], '', false);
        $parsingState = new ParsingState();
        $emptySwitchNode = new ViewHelperNode(
            $renderingContext,
            'f',
            'switch',
            ['expression' => new TextNode('test-expression')],
            $parsingState
        );
        $withDefaultCaseOnly = clone $emptySwitchNode;
        $withDefaultCaseOnly->addChildNode(new ViewHelperNode($renderingContext, 'f', 'defaultCase', [], $parsingState));
        $withSingleCaseOnly = clone $emptySwitchNode;
        $withSingleCaseOnly->addChildNode(new ViewHelperNode($renderingContext, 'f', 'case', ['value' => 'foo'], $parsingState));
        return [
            'Empty switch statement' => [
                $emptySwitchNode,
                'call_user_func_array(function($arguments) use ($renderingContext, $self) {' . PHP_EOL .
                'switch ($arguments[\'expression\']) {' .
                PHP_EOL . '}' . PHP_EOL .
                '}, array($arguments))',
                ''
            ],
            'With default case only' => [
                $withDefaultCaseOnly,
                'call_user_func_array(function($arguments) use ($renderingContext, $self) {' . PHP_EOL .
                'switch ($arguments[\'expression\']) {' . PHP_EOL .
                'default: return call_user_func(function() use ($renderingContext, $self) {' . PHP_EOL .
                'return NULL;' . PHP_EOL .
                '});' . PHP_EOL .
                '}' . PHP_EOL . '}, array($arguments))',
                ''
            ],
            'With single case only' => [
                $withSingleCaseOnly,
                'call_user_func_array(function($arguments) use ($renderingContext, $self) {' . PHP_EOL .
                'switch ($arguments[\'expression\']) {' . PHP_EOL .
                'case call_user_func(function() use ($renderingContext, $self) {' . PHP_EOL .
                '$argument = unserialize(\'s:3:"foo";\'); return $argument->evaluate($renderingContext);' . PHP_EOL .
                '}): return call_user_func(function() use ($renderingContext, $self) {' . PHP_EOL .
                'return NULL;' . PHP_EOL .
                '});' . PHP_EOL .
                '}' . PHP_EOL . '}, array($arguments))',
                ''
            ],
        ];
    }
}
