<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollection;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
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

    public function setUp(): void
    {
        parent::setUp();
        $this->viewHelper = $this->getMockBuilder(SwitchViewHelper::class)->setMethods(['dummy'])->getMock();
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
        $context = new RenderingContextFixture();
        $variableContainer = $this->getMockBuilder(ViewHelperVariableContainer::class)->setMethods(['addOrUpdate'])->getMock();
        $variableContainer->expects($this->at(0))->method('addOrUpdate')->with(SwitchViewHelper::class, 'switchExpression', 'switchExpression');
        $variableContainer->expects($this->at(1))->method('addOrUpdate')->with(SwitchViewHelper::class, 'break', false);
        $context->setViewHelperVariableContainer($variableContainer);
        $this->viewHelper->setRenderingContext($context);
        $this->viewHelper->setArguments(['expression' => 'switchExpression']);
        $output = $this->viewHelper->initializeArgumentsAndRender();
        $this->assertEquals('', $output);
    }

    /**
     * @test
     */
    public function renderRemovesSwitchExpressionFromViewHelperVariableContainerAfterInvocation(): void
    {
        $context = new RenderingContextFixture();
        $variableContainer = $this->getMockBuilder(ViewHelperVariableContainer::class)->setMethods(['remove'])->getMock();
        $variableContainer->expects($this->at(0))->method('remove')->with(SwitchViewHelper::class, 'switchExpression');
        $variableContainer->expects($this->at(1))->method('remove')->with(SwitchViewHelper::class, 'break');
        $context->setViewHelperVariableContainer($variableContainer);
        $output = $this->viewHelper->execute($context, (new ArgumentCollection())->assignAll(['expression' => 'switchExpression']));
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
        $context = new RenderingContextFixture();
        $context->getVariableProvider()->setSource($variables);
        $instance = new SwitchViewHelper();

        foreach ($childNodes as $childNode) {
            $instance->addChild($childNode);
        }

        $result = $instance->execute($context, (new ArgumentCollection())->assignAll(['expression' => $variables['switchExpression']]));
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getRetrieveContentFromChildNodesTestValues(): array
    {
        $context = new RenderingContextFixture();
        $matchingNode = (new CaseViewHelper())->addChild(new TextNode('foo'))->onOpen($context, (new ArgumentCollection())->assignAll(['value' => 'foo']));

        $notMatchingNode = (new CaseViewHelper())->addChild(new TextNode(''))->onOpen($context, (new ArgumentCollection())->assignAll(['value' => 'bar']));

        $defaultCaseNode = (new DefaultCaseViewHelper())->addChild(new TextNode('default'));

        $textNode = new TextNode('TEXT');
        $objectAccessorNode = new ObjectAccessorNode('void');

        return [
            'empty switch' => [[], ['switchExpression' => false], null],
            'single case matching' => [[$matchingNode], ['switchExpression' => 'foo'], 'foo'],
            'two case with matching first' => [[$matchingNode, $notMatchingNode], ['switchExpression' => 'foo'], 'foo'],
            'two case with matching last' => [[$notMatchingNode, $matchingNode], ['switchExpression' => 'foo'], 'foo'],
            'single case not matching with default last' => [[$notMatchingNode, $defaultCaseNode], ['switchExpression' => 'foo'], 'default'],
            'skips non-ViewHelper nodes' => [[$textNode, $objectAccessorNode, $matchingNode], ['switchExpression' => 'foo'], 'foo']
        ];
    }

    /**
     * @test
     */
    public function retrieveContentFromChildNodesBreaksOnBreak(): void
    {
        $context = new RenderingContextFixture();

        $instance = (new SwitchViewHelper())->onOpen($context, (new ArgumentCollection())->assignAll(['expression' => 'foo']));

        $matchingCaseViewHelper = (new CaseViewHelper())->addChild(new TextNode('foo-childcontent'))->onOpen($context, (new ArgumentCollection())->assignAll(['value' => 'foo']));

        $untouchedViewHelper = $this->getMockBuilder(DefaultCaseViewHelper::class)->setMethods(['evaluate'])->getMock();
        $untouchedViewHelper->expects($this->never())->method('evaluate');

        $instance->addChild($matchingCaseViewHelper);
        $instance->addChild($untouchedViewHelper);

        $result = $instance->evaluate($context);
        $this->assertEquals('foo-childcontent', $result);
    }
}
