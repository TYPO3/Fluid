<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\Exception as ParserException;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Fixtures\TestViewHelper;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\Fixtures\ChildNodeAccessFacetViewHelper;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for \TYPO3Fluid\CMS\Fluid\Core\Parser\SyntaxTree\ViewHelperNode
 */
class ViewHelperNodeTest extends UnitTestCase
{

    /**
     * @var RenderingContext
     */
    protected $renderingContext;

    /**
     * @var TemplateVariableContainer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $templateVariableContainer;

    /**
     * @var ViewHelperResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockViewHelperResolver;

    /**
     * Setup fixture
     */
    public function setUp()
    {
        $this->renderingContext = new RenderingContextFixture();
        $this->mockViewHelperResolver = $this->getMock(ViewHelperResolver::class, ['resolveViewHelperClassName', 'createViewHelperInstanceFromClassName', 'getArgumentDefinitionsForViewHelper']);
        $this->mockViewHelperResolver->expects($this->any())->method('resolveViewHelperClassName')->with('f', 'vh')->willReturn(TestViewHelper::class);
        $this->mockViewHelperResolver->expects($this->any())->method('createViewHelperInstanceFromClassName')->with(TestViewHelper::class)->willReturn(new TestViewHelper());
        $this->mockViewHelperResolver->expects($this->any())->method('getArgumentDefinitionsForViewHelper')->willReturn([
            'foo' => new ArgumentDefinition('foo', 'string', 'Dummy required argument', true)
        ]);
        $this->renderingContext->setViewHelperResolver($this->mockViewHelperResolver);
    }

    /**
     * @test
     */
    public function constructorSetsViewHelperAndArguments()
    {
        $arguments = ['foo' => 'bar'];
        /** @var ViewHelperNode|\PHPUnit_Framework_MockObject_MockObject $viewHelperNode */
        $viewHelperNode = new ViewHelperNode($this->renderingContext, 'f', 'vh', $arguments, new ParsingState());

        $this->assertAttributeEquals($arguments, 'arguments', $viewHelperNode);
    }

    /**
     * @test
     */
    public function testEvaluateCallsInvoker()
    {
        $invoker = $this->getMock(ViewHelperInvoker::class, ['invoke']);
        $invoker->expects($this->once())->method('invoke')->willReturn('test');
        $this->renderingContext->setViewHelperInvoker($invoker);
        $node = new ViewHelperNode($this->renderingContext, 'f', 'vh', ['foo' => 'bar'], new ParsingState());
        $result = $node->evaluate($this->renderingContext);
        $this->assertEquals('test', $result);
    }

    /**
     * @test
     */
    public function testThrowsExceptionOnMissingRequiredArgument()
    {
        $this->setExpectedException(ParserException::class);
        new ViewHelperNode($this->renderingContext, 'f', 'vh', ['notfoo' => false], new ParsingState());
    }

    /**
     * @test
     * @expectedException \TYPO3Fluid\Fluid\Core\Parser\Exception
     */
    public function abortIfRequiredArgumentsAreMissingThrowsException()
    {
        $expected = [
            new ArgumentDefinition('firstArgument', 'string', '', false),
            new ArgumentDefinition('secondArgument', 'string', '', true)
        ];

        $templateParser = $this->getAccessibleMock(ViewHelperNode::class, ['dummy'], [], '', false);

        $templateParser->_call('abortIfRequiredArgumentsAreMissing', $expected, []);
    }

    /**
     * @test
     */
    public function abortIfRequiredArgumentsAreMissingDoesNotThrowExceptionIfRequiredArgumentExists()
    {
        $expectedArguments = [
            new ArgumentDefinition('name1', 'string', 'desc', false),
            new ArgumentDefinition('name2', 'string', 'desc', true)
        ];
        $actualArguments = [
            'name2' => 'bla'
        ];

        $mockTemplateParser = $this->getAccessibleMock(ViewHelperNode::class, ['dummy'], [], '', false);

        $mockTemplateParser->_call('abortIfRequiredArgumentsAreMissing', $expectedArguments, $actualArguments);
        // dummy assertion to avoid "did not perform any assertions" error
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function booleanArgumentsMustBeConvertedIntoBooleanNodes()
    {
        $argumentDefinitions = [
            'var1' => new ArgumentDefinition('var1', 'bool', 'desc', false),
            'var2' => new ArgumentDefinition('var2', 'boolean', 'desc', false)
        ];
        $argumentsObjectTree = [
            'var1' => new TextNode('true'),
            'var2' => new TextNode('true')
        ];

        $mockTemplateParser = $this->getAccessibleMock(ViewHelperNode::class, ['dummy'], [], '', false);

        $mockTemplateParser->_callRef('rewriteBooleanNodesInArgumentsObjectTree', $argumentDefinitions, $argumentsObjectTree);

        $this->assertEquals($argumentsObjectTree, [
            'var1' => new BooleanNode(new TextNode('true')),
            'var2' => new BooleanNode(new TextNode('true'))
        ]);
    }
}
