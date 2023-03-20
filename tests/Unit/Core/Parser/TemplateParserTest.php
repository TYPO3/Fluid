<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser;

use TYPO3Fluid\Fluid\Core\Compiler\StopCompilingException;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\Configuration;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\InterceptorInterface;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\AbstractNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NumericNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessorInterface;
use TYPO3Fluid\Fluid\Core\Parser\UnknownNamespaceException;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\ViewHelpers\CommentViewHelper;

/**
 * Testcase for TemplateParser.
 *
 * This is to at least half a system test, as it compares rendered results to
 * expectations, and does not strictly check the parsing...
 */
class TemplateParserTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testInitializeViewHelperAndAddItToStackReturnsFalseIfNamespaceIgnored(): void
    {
        $resolver = $this->getMock(ViewHelperResolver::class, ['isNamespaceIgnored']);
        $resolver->expects(self::once())->method('isNamespaceIgnored')->willReturn(true);
        $context = new RenderingContextFixture();
        $context->setViewHelperResolver($resolver);
        $templateParser = new TemplateParser();
        $templateParser->setRenderingContext($context);
        $method = new \ReflectionMethod($templateParser, 'initializeViewHelperAndAddItToStack');
        $method->setAccessible(true);
        $result = $method->invokeArgs($templateParser, [new ParsingState(), 'f', 'render', []]);
        self::assertNull($result);
    }

    /**
     * @test
     */
    public function testInitializeViewHelperAndAddItToStackThrowsExceptionIfNamespaceInvalid(): void
    {
        $this->expectException(UnknownNamespaceException::class);
        $resolver = $this->getMock(ViewHelperResolver::class, ['isNamespaceIgnored', 'isNamespaceValid']);
        $resolver->expects(self::once())->method('isNamespaceIgnored')->willReturn(false);
        $resolver->expects(self::once())->method('isNamespaceValid')->willReturn(false);
        $context = new RenderingContextFixture();
        $context->setViewHelperResolver($resolver);
        $templateParser = new TemplateParser();
        $templateParser->setRenderingContext($context);
        $method = new \ReflectionMethod($templateParser, 'initializeViewHelperAndAddItToStack');
        $method->setAccessible(true);
        $result = $method->invokeArgs($templateParser, [new ParsingState(), 'f', 'render', []]);
        self::assertNull($result);
    }

    /**
     * @test
     */
    public function testClosingViewHelperTagHandlerReturnsFalseIfNamespaceIgnored(): void
    {
        $resolver = $this->getMock(ViewHelperResolver::class, ['isNamespaceIgnored']);
        $resolver->expects(self::once())->method('isNamespaceIgnored')->willReturn(true);
        $context = new RenderingContextFixture();
        $context->setViewHelperResolver($resolver);
        $templateParser = new TemplateParser();
        $templateParser->setRenderingContext($context);
        $method = new \ReflectionMethod($templateParser, 'closingViewHelperTagHandler');
        $method->setAccessible(true);
        $result = $method->invokeArgs($templateParser, [new ParsingState(), 'f', 'render']);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function testClosingViewHelperTagHandlerThrowsExceptionIfNamespaceInvalid(): void
    {
        $this->expectException(UnknownNamespaceException::class);
        $resolver = $this->getMock(ViewHelperResolver::class, ['isNamespaceValid', 'isNamespaceIgnored']);
        $resolver->expects(self::once())->method('isNamespaceIgnored')->willReturn(false);
        $resolver->expects(self::once())->method('isNamespaceValid')->willReturn(false);
        $context = new RenderingContextFixture();
        $context->setViewHelperResolver($resolver);
        $templateParser = new TemplateParser();
        $templateParser->setRenderingContext($context);
        $method = new \ReflectionMethod($templateParser, 'closingViewHelperTagHandler');
        $method->setAccessible(true);
        $result = $method->invokeArgs($templateParser, [new ParsingState(), 'f', 'render']);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function testPublicGetAndSetEscapingEnabledWorks(): void
    {
        $subject = new TemplateParser();
        $default = $subject->isEscapingEnabled();
        $subject->setEscapingEnabled(!$default);
        self::assertAttributeSame(!$default, 'escapingEnabled', $subject);
    }

    /**
     * @test
     */
    public function testBuildObjectTreeThrowsExceptionOnUnclosedViewHelperTag(): void
    {
        $this->expectException(Exception::class);
        $renderingContext = new RenderingContextFixture();
        $renderingContext->setVariableProvider(new StandardVariableProvider());
        $templateParser = new TemplateParser();
        $templateParser->setRenderingContext($renderingContext);
        $method = new \ReflectionMethod($templateParser, 'buildObjectTree');
        $method->setAccessible(true);
        $method->invokeArgs($templateParser, [['<f:render>'], TemplateParser::CONTEXT_INSIDE_VIEWHELPER_ARGUMENTS]);
    }

    /**
     * @test
     */
    public function testParseCallsPreProcessOnTemplateProcessors(): void
    {
        $templateParser = new TemplateParser();
        $processor1 = $this->getMockForAbstractClass(TemplateProcessorInterface::class, [], '', false, false, true, ['preProcessSource']);
        $processor2 = clone $processor1;
        $processor1->expects(self::once())->method('preProcessSource')->with('source1')->willReturn('source2');
        $processor2->expects(self::once())->method('preProcesssource')->with('source2')->willReturn('final');
        $context = new RenderingContextFixture();
        $context->setTemplateProcessors([$processor1, $processor2]);
        $context->setVariableProvider(new StandardVariableProvider());
        $templateParser->setRenderingContext($context);
        $result = $templateParser->parse('source1')->render($context);
        self::assertEquals('final', $result);
    }

    /**
     * @test
     */
    public function getOrParseAndStoreTemplateSetsAndStoresUncompilableStateInCache(): void
    {
        $parsedTemplate = new ParsingState();
        $parsedTemplate->setCompilable(true);
        $templateParser = $this->getMock(TemplateParser::class, ['parse']);
        $templateParser->expects(self::once())->method('parse')->willReturn($parsedTemplate);
        $context = new RenderingContextFixture();
        $compiler = $this->getMock(TemplateCompiler::class, ['store', 'get', 'has']);
        $compiler->expects(self::never())->method('get');
        $compiler->expects(self::atLeastOnce())->method('has')->willReturn(false);
        $compiler->expects(self::atLeastOnce())->method('store')->willReturnOnConsecutiveCalls(
            self::throwException(new StopCompilingException()),
            true
        );
        $context->setTemplateCompiler($compiler);
        $context->setVariableProvider(new StandardVariableProvider());
        $templateParser->setRenderingContext($context);
        $result = $templateParser->getOrParseAndStoreTemplate('fake-foo-baz', function ($a, $b) {
            return 'test';
        });
        self::assertSame($parsedTemplate, $result);
        self::assertFalse($parsedTemplate->isCompilable());
    }

    /**
     * @test
     */
    public function parseThrowsExceptionWhenStringArgumentMissing(): void
    {
        $this->expectException(\Exception::class);
        $templateParser = new TemplateParser();
        $templateParser->parse(123);
    }

    public static function quotedStrings(): array
    {
        return [
            ['"no quotes here"', 'no quotes here'],
            ["'no quotes here'", 'no quotes here'],
            ["'this \"string\" had \\'quotes\\' in it'", 'this "string" had \'quotes\' in it'],
            ['"this \\"string\\" had \'quotes\' in it"', 'this "string" had \'quotes\' in it'],
            ['"a weird \"string\" \'with\' *freaky* \\\\stuff', 'a weird "string" \'with\' *freaky* \\stuff'],
            ['\'\\\'escaped quoted string in string\\\'\'', '\'escaped quoted string in string\'']
        ];
    }

    /**
     * @dataProvider quotedStrings
     * @test
     */
    public function unquoteStringReturnsUnquotedStrings(string $quoted, string $unquoted): void
    {
        $templateParser = $this->getAccessibleMock(TemplateParser::class, []);
        self::assertEquals($unquoted, $templateParser->_call('unquoteString', $quoted));
    }

    public static function templatesToSplit()
    {
        return [
            ['TemplateParserTestFixture01-shorthand'],
            ['TemplateParserTestFixture06'],
            ['TemplateParserTestFixture14']
        ];
    }

    /**
     * @dataProvider templatesToSplit
     * @test
     */
    public function splitTemplateAtDynamicTagsReturnsCorrectlySplitTemplate(string $templateName): void
    {
        $template = file_get_contents(__DIR__ . '/Fixtures/' . $templateName . '.html');
        $expectedResult = require __DIR__ . '/Fixtures/' . $templateName . '-split.php';
        $templateParser = $this->getAccessibleMock(TemplateParser::class, []);
        self::assertSame($expectedResult, $templateParser->_call('splitTemplateAtDynamicTags', $template), 'Filed for ' . $templateName);
    }

    /**
     * @test
     */
    public function buildObjectTreeCreatesRootNodeAndSetsUpParsingState(): void
    {
        $context = new RenderingContextFixture();
        $context->setVariableProvider(new StandardVariableProvider());
        $templateParser = $this->getAccessibleMock(TemplateParser::class, []);
        $templateParser->setRenderingContext($context);
        $result = $templateParser->_call('buildObjectTree', [], TemplateParser::CONTEXT_OUTSIDE_VIEWHELPER_ARGUMENTS);
        self::assertInstanceOf(ParsingState::class, $result);
    }

    /**
     * @test
     */
    public function buildObjectTreeDelegatesHandlingOfTemplateElements(): void
    {
        $templateParser = $this->getAccessibleMock(
            TemplateParser::class,
            [
                'textHandler',
                'openingViewHelperTagHandler',
                'closingViewHelperTagHandler',
                'textAndShorthandSyntaxHandler'
            ]
        );
        $context = new RenderingContextFixture();
        $context->setVariableProvider(new StandardVariableProvider());
        $templateParser->setRenderingContext($context);
        $splitTemplate = $templateParser->_call('splitTemplateAtDynamicTags', 'The first part is simple<![CDATA[<f:for each="{a: {a: 0, b: 2, c: 4}}" as="array"><f:for each="{array}" as="value">{value} </f:for>]]><f:format.printf arguments="{number : 362525200}">%.3e</f:format.printf>and here goes some {text} that could have {shorthand}');
        $result = $templateParser->_call('buildObjectTree', $splitTemplate, TemplateParser::CONTEXT_OUTSIDE_VIEWHELPER_ARGUMENTS);
        self::assertInstanceOf(ParsingState::class, $result);
    }

    /**
     * @test
     */
    public function openingViewHelperTagHandlerDelegatesViewHelperInitialization(): void
    {
        $mockState = $this->getMock(ParsingState::class);
        $mockState->expects(self::never())->method('popNodeFromStack');
        $templateParser = $this->getAccessibleMock(
            TemplateParser::class,
            ['parseArguments', 'initializeViewHelperAndAddItToStack']
        );
        $context = new RenderingContextFixture();
        $resolver = $this->getMockBuilder(ViewHelperResolver::class)->onlyMethods(['isNamespaceValid', 'resolveViewHelperClassName'])->getMock();
        $resolver->expects(self::once())->method('isNamespaceValid')->with('namespaceIdentifier')->willReturn(true);
        $resolver->expects(self::once())->method('resolveViewHelperClassName')->with('namespaceIdentifier')->willReturn(CommentViewHelper::class);
        $context->setViewHelperResolver($resolver);
        $templateParser->setRenderingContext($context);
        $templateParser->expects(self::once())->method('parseArguments')
            ->with(['arguments'])->willReturn(['parsedArguments']);
        $templateParser->expects(self::once())->method('initializeViewHelperAndAddItToStack')
            ->with($mockState, 'namespaceIdentifier', 'methodIdentifier', ['parsedArguments']);

        $templateParser->_call('openingViewHelperTagHandler', $mockState, 'namespaceIdentifier', 'methodIdentifier', ['arguments'], false, '');
    }

    /**
     * @test
     */
    public function openingViewHelperTagHandlerPopsNodeFromStackForSelfClosingTags(): void
    {
        $mockState = $this->getMock(ParsingState::class);
        $mockState->expects(self::once())->method('popNodeFromStack')->willReturn($this->getMock(NodeInterface::class));
        $mockState->expects(self::once())->method('getNodeFromStack')->willReturn($this->getMock(NodeInterface::class));

        $resolver = $this->getMockBuilder(ViewHelperResolver::class)->onlyMethods(['isNamespaceValid', 'isNamespaceIgnored', 'resolveViewHelperClassName'])->getMock();
        $resolver->expects(self::once())->method('isNamespaceIgnored')->with('')->willReturn(false);
        $resolver->expects(self::once())->method('isNamespaceValid')->with('')->willReturn(true);
        $resolver->expects(self::once())->method('resolveViewHelperClassName')->willReturn(new CommentViewHelper());

        $context = new RenderingContextFixture();
        $context->setViewHelperResolver($resolver);

        $templateParser = $this->getAccessibleMock(
            TemplateParser::class,
            ['parseArguments', 'initializeViewHelperAndAddItToStack']
        );
        $templateParser->setRenderingContext($context);
        $node = $this->getMock(ViewHelperNode::class, [], [], false, false);
        $templateParser->expects(self::once())->method('initializeViewHelperAndAddItToStack')->willReturn($node);

        $templateParser->_call('openingViewHelperTagHandler', $mockState, '', '', [], true, '');
    }

    /**
     * @__test
     */
    public function initializeViewHelperAndAddItToStackThrowsExceptionIfViewHelperClassNameIsWronglyCased()
    {
        $this->expectException(\Exception::class);

        $mockState = $this->getMock(ParsingState::class);

        $templateParser = $this->getAccessibleMock(
            TemplateParser::class,
            [
                'abortIfUnregisteredArgumentsExist',
                'abortIfRequiredArgumentsAreMissing',
                'rewriteBooleanNodesInArgumentsObjectTree'
            ]
        );

        $templateParser->_call('initializeViewHelperAndAddItToStack', $mockState, 'f', 'wRongLyCased', ['arguments']);
    }

    /**
     * @test
     */
    public function closingViewHelperTagHandlerThrowsExceptionBecauseOfClosingTagWhichWasNeverOpened(): void
    {
        $this->expectException(\Exception::class);

        $mockNodeOnStack = $this->getMock(NodeInterface::class, [], [], false, false);
        $mockState = $this->getMock(ParsingState::class);
        $mockState->expects(self::once())->method('popNodeFromStack')->willReturn($mockNodeOnStack);

        $templateParser = $this->getAccessibleMock(TemplateParser::class, []);
        $templateParser->_set('renderingContext', new RenderingContextFixture());

        $templateParser->_call('closingViewHelperTagHandler', $mockState, 'f', 'render');
    }

    /**
     * @test
     */
    public function closingViewHelperTagHandlerThrowsExceptionBecauseOfWrongTagNesting(): void
    {
        $this->expectException(\Exception::class);

        $mockState = $this->getMock(ParsingState::class);
        $templateParser = $this->getAccessibleMock(TemplateParser::class, []);
        $templateParser->_set('renderingContext', new RenderingContextFixture());
        $templateParser->_call('closingViewHelperTagHandler', $mockState, 'f', 'render');
    }

    /**
     * @test
     */
    public function objectAccessorHandlerCallsInitializeViewHelperAndAddItToStackIfViewHelperSyntaxIsPresent(): void
    {
        $mockState = $this->getMock(ParsingState::class);
        $mockState->expects(self::exactly(2))->method('popNodeFromStack')
            ->willReturn($this->getMock(NodeInterface::class));
        $mockState->expects(self::exactly(2))->method('getNodeFromStack')
            ->willReturn($this->getMock(NodeInterface::class));

        $templateParser = $this->getAccessibleMock(
            TemplateParser::class,
            ['recursiveArrayHandler', 'initializeViewHelperAndAddItToStack']
        );
        $templateParser->setRenderingContext(new RenderingContextFixture());
        $templateParser->expects(self::atLeastOnce())->method('recursiveArrayHandler')
            ->with($mockState, 'arguments: {0: \'foo\'}')->willReturn(['arguments' => ['foo']]);
        $series = [
            [$mockState, 'f', 'format.printf', ['arguments' => ['foo']]],
            [$mockState, 'f', 'debug', []],
        ];
        $templateParser->expects(self::atLeastOnce())->method('initializeViewHelperAndAddItToStack')->willReturnCallback(function (...$args) use (&$series): bool {
            $expectedArgs = array_shift($series);
            self::assertSame($expectedArgs[0], $args[0]);
            self::assertSame($expectedArgs[1], $args[1]);
            self::assertSame($expectedArgs[2], $args[2]);
            self::assertSame($expectedArgs[3], $args[3]);
            return true;
        });
        $templateParser->_call('objectAccessorHandler', $mockState, '', '', 'f:debug() -> f:format.printf(arguments: {0: \'foo\'})', '');
    }

    /**
     * @test
     */
    public function objectAccessorHandlerCreatesObjectAccessorNodeWithExpectedValueAndAddsItToStack(): void
    {
        $mockNodeOnStack = $this->getMockForAbstractClass(AbstractNode::class, [], '', false, false, false, ['addChildNode']);
        $mockNodeOnStack->expects(self::once())->method('addChildNode')->with(self::anything());
        $mockState = $this->getMock(ParsingState::class);
        $mockState->expects(self::once())->method('getNodeFromStack')->willReturn($mockNodeOnStack);

        $templateParser = $this->getAccessibleMock(TemplateParser::class, []);

        $templateParser->_call('objectAccessorHandler', $mockState, 'objectAccessorString', '', '', '');
    }

    /**
     * @test
     */
    public function valuesFromObjectAccessorsAreRunThroughEscapingInterceptorsByDefault(): void
    {
        $objectAccessorNodeInterceptor = $this->getMock(InterceptorInterface::class);
        $objectAccessorNodeInterceptor->expects(self::once())->method('process')
            ->with(self::anything())->willReturnArgument(0);

        $parserConfiguration = $this->getMock(Configuration::class);
        $parserConfiguration->expects(self::any())->method('getInterceptors')->willReturn([]);
        $parserConfiguration->expects(self::once())->method('getEscapingInterceptors')
            ->with(InterceptorInterface::INTERCEPT_OBJECTACCESSOR)
            ->willReturn([$objectAccessorNodeInterceptor]);

        $mockNodeOnStack = $this->getMockForAbstractClass(AbstractNode::class, [], '', false, false);
        $mockState = $this->getMock(ParsingState::class);
        $mockState->expects(self::once())->method('getNodeFromStack')->willReturn($mockNodeOnStack);

        $templateParser = $this->getAccessibleMock(TemplateParser::class, []);
        $templateParser->_set('configuration', $parserConfiguration);

        $templateParser->_call('objectAccessorHandler', $mockState, 'objectAccessorString', '', '', '');
    }

    /**
     * @test
     */
    public function valuesFromObjectAccessorsAreNotRunThroughEscapingInterceptorsIfEscapingIsDisabled(): void
    {
        $parserConfiguration = $this->getMock(Configuration::class);
        $parserConfiguration->expects(self::any())->method('getInterceptors')->willReturn([]);
        $parserConfiguration->expects(self::never())->method('getEscapingInterceptors');

        $mockNodeOnStack = $this->getMockForAbstractClass(AbstractNode::class, [], '', false, false);
        $mockState = $this->getMock(ParsingState::class);
        $mockState->expects(self::once())->method('getNodeFromStack')->willReturn($mockNodeOnStack);

        $templateParser = $this->getAccessibleMock(TemplateParser::class, []);
        $templateParser->_set('configuration', $parserConfiguration);
        $templateParser->_set('escapingEnabled', false);

        $templateParser->_call('objectAccessorHandler', $mockState, 'objectAccessorString', '', '', '');
    }

    /**
     * @test
     */
    public function valuesFromObjectAccessorsAreRunThroughInterceptors(): void
    {
        $objectAccessorNodeInterceptor = $this->getMock(InterceptorInterface::class);
        $objectAccessorNodeInterceptor->expects(self::once())->method('process')
            ->with(self::anything())->willReturnArgument(0);

        $parserConfiguration = $this->getMock(Configuration::class);
        $parserConfiguration->expects(self::any())->method('getEscapingInterceptors')->willReturn([]);
        $parserConfiguration->expects(self::once())->method('getInterceptors')
            ->with(InterceptorInterface::INTERCEPT_OBJECTACCESSOR)->willReturn([$objectAccessorNodeInterceptor]);

        $mockNodeOnStack = $this->getMockForAbstractClass(AbstractNode::class, [], '', false, false);
        $mockState = $this->getMock(ParsingState::class);
        $mockState->expects(self::once())->method('getNodeFromStack')->willReturn($mockNodeOnStack);

        $templateParser = $this->getAccessibleMock(TemplateParser::class, []);
        $templateParser->_set('configuration', $parserConfiguration);
        $templateParser->_set('escapingEnabled', false);

        $templateParser->_call('objectAccessorHandler', $mockState, 'objectAccessorString', '', '', '');
    }

    public static function argumentsStrings(): array
    {
        return [
            ['a="2"', ['a' => '2']],
            ['a="2" b="foobar \' with \\" quotes"', ['a' => '2', 'b' => 'foobar \' with " quotes']],
            [' arguments="{number : 362525200}"', ['arguments' => '{number : 362525200}']]
        ];
    }

    /**
     * @test
     * @dataProvider argumentsStrings
     */
    public function parseArgumentsWorksAsExpected(string $argumentsString, array $expected): void
    {
        $context = new RenderingContextFixture();
        $viewHelper = $this->getMockBuilder(CommentViewHelper::class)->onlyMethods(['validateAdditionalArguments'])->getMock();
        $viewHelper->expects(self::once())->method('validateAdditionalArguments');

        $templateParser = $this->getAccessibleMock(TemplateParser::class, ['buildArgumentObjectTree']);
        $templateParser->setRenderingContext($context);
        $templateParser->expects(self::any())->method('buildArgumentObjectTree')->willReturnArgument(0);

        self::assertSame($expected, $templateParser->_call('parseArguments', $argumentsString, $viewHelper));
    }

    /**
     * @test
     */
    public function buildArgumentObjectTreeReturnsTextNodeForSimplyString(): void
    {
        $templateParser = $this->getAccessibleMock(TemplateParser::class, []);
        $this->assertInstanceof(TextNode::class, $templateParser->_call('buildArgumentObjectTree', 'a very plain string'));
    }

    /**
     * @test
     */
    public function buildArgumentObjectTreeBuildsObjectTreeForComlexString(): void
    {
        $objectTree = $this->getMock(ParsingState::class);
        $objectTree->expects(self::once())->method('getRootNode')->willReturn('theRootNode');

        $templateParser = $this->getAccessibleMock(
            TemplateParser::class,
            ['splitTemplateAtDynamicTags', 'buildObjectTree']
        );
        $templateParser->expects(self::atLeastOnce())->method('splitTemplateAtDynamicTags')
            ->with('a <very> {complex} string')->willReturn(['split string']);
        $templateParser->expects(self::atLeastOnce())->method('buildObjectTree')
            ->with(['split string'])->willReturn($objectTree);

        self::assertEquals('theRootNode', $templateParser->_call('buildArgumentObjectTree', 'a <very> {complex} string'));
    }

    /**
     * @test
     */
    public function textAndShorthandSyntaxHandlerDelegatesAppropriately(): void
    {
        $mockState = $this->getMock(ParsingState::class, ['getNodeFromStack']);
        $mockState->expects(self::any())->method('getNodeFromStack')->willReturn(new RootNode());

        $templateParser = $this->getMock(
            TemplateParser::class,
            ['objectAccessorHandler', 'arrayHandler', 'textHandler']
        );
        $context = new RenderingContextFixture();
        $templateParser->setRenderingContext($context);
        $series = [
            [$mockState, ' '],
            [$mockState, ' "fishy" is \'going\' ']
        ];
        $templateParser->expects(self::atLeastOnce())->method('textHandler')->willReturnCallback(function (...$args) use (&$series): void {
            [$expectedArgOne, $expectedArgTwo] = array_shift($series);
            self::assertSame($expectedArgOne, $args[0]);
            self::assertSame($expectedArgTwo, $args[1]);
        });
        $templateParser->expects(self::atLeastOnce())->method('objectAccessorHandler')->with($mockState, 'someThing.absolutely', '', '', '');
        $templateParser->expects(self::atLeastOnce())->method('arrayHandler')->with($mockState, self::anything());

        $text = ' {someThing.absolutely} "fishy" is \'going\' {on: "here"}';
        $method = new \ReflectionMethod(TemplateParser::class, 'textAndShorthandSyntaxHandler');
        $method->setAccessible(true);
        $method->invokeArgs($templateParser, [$mockState, $text, TemplateParser::CONTEXT_INSIDE_VIEWHELPER_ARGUMENTS]);
    }

    /**
     * @test
     */
    public function arrayHandlerAddsArrayNodeWithProperContentToStack(): void
    {
        $mockNodeOnStack = $this->getMockForAbstractClass(AbstractNode::class, [], '', false, false, false, ['addChildNode']);
        $mockNodeOnStack->expects(self::once())->method('addChildNode')->with(self::anything());
        $mockState = $this->getMock(ParsingState::class);
        $mockState->expects(self::once())->method('getNodeFromStack')->willReturn($mockNodeOnStack);

        $templateParser = $this->getAccessibleMock(
            TemplateParser::class,
            ['recursiveArrayHandler']
        );
        $templateParser->expects(self::any())->method('recursiveArrayHandler')
            ->with(['arrayText'])->willReturn('processedArrayText');

        $templateParser->_call('arrayHandler', $mockState, ['arrayText']);
    }

    /**
     * @test
     */
    public function textNodesAreRunThroughEscapingInterceptorsByDefault(): void
    {
        $textInterceptor = $this->getMock(InterceptorInterface::class);
        $textInterceptor->expects(self::once())->method('process')->with(self::anything())->willReturnArgument(0);

        $parserConfiguration = $this->getMock(Configuration::class);
        $parserConfiguration->expects(self::once())->method('getEscapingInterceptors')
            ->with(InterceptorInterface::INTERCEPT_TEXT)->willReturn([$textInterceptor]);
        $parserConfiguration->expects(self::any())->method('getInterceptors')->willReturn([]);

        $mockNodeOnStack = $this->getMockForAbstractClass(AbstractNode::class, [], '', false, false, false, ['addChildNode']);
        $mockNodeOnStack->expects(self::once())->method('addChildNode')->with(self::anything());
        $mockState = $this->getMock(ParsingState::class);
        $mockState->expects(self::once())->method('getNodeFromStack')->willReturn($mockNodeOnStack);

        $templateParser = $this->getAccessibleMock(TemplateParser::class, ['splitTemplateAtDynamicTags', 'buildObjectTree']);
        $templateParser->_set('configuration', $parserConfiguration);

        $templateParser->_call('textHandler', $mockState, 'string');
    }

    /**
     * @test
     */
    public function textNodesAreNotRunThroughEscapingInterceptorsIfEscapingIsDisabled(): void
    {
        $parserConfiguration = $this->getMock(Configuration::class);
        $parserConfiguration->expects(self::never())->method('getEscapingInterceptors');
        $parserConfiguration->expects(self::any())->method('getInterceptors')->willReturn([]);

        $mockNodeOnStack = $this->getMockForAbstractClass(AbstractNode::class, [], '', false, false, false, ['addChildNode']);
        $mockNodeOnStack->expects(self::once())->method('addChildNode')->with(self::anything());
        $mockState = $this->getMock(ParsingState::class);
        $mockState->expects(self::once())->method('getNodeFromStack')->willReturn($mockNodeOnStack);

        $templateParser = $this->getAccessibleMock(
            TemplateParser::class,
            ['splitTemplateAtDynamicTags', 'buildObjectTree']
        );
        $templateParser->_set('configuration', $parserConfiguration);
        $templateParser->_set('escapingEnabled', false);

        $templateParser->_call('textHandler', $mockState, 'string');
    }

    /**
     * @test
     */
    public function textNodesAreRunThroughInterceptors(): void
    {
        $textInterceptor = $this->getMock(InterceptorInterface::class);
        $textInterceptor->expects(self::once())->method('process')->with(self::anything())->willReturnArgument(0);

        $parserConfiguration = $this->getMock(Configuration::class);
        $parserConfiguration->expects(self::once())->method('getInterceptors')
            ->with(InterceptorInterface::INTERCEPT_TEXT)->willReturn([$textInterceptor]);
        $parserConfiguration->expects(self::any())->method('getEscapingInterceptors')->willReturn([]);

        $mockNodeOnStack = $this->getMockForAbstractClass(AbstractNode::class, [], '', false, false, false, ['addChildNode']);
        $mockNodeOnStack->expects(self::once())->method('addChildNode')->with(self::anything());
        $mockState = $this->getMock(ParsingState::class);
        $mockState->expects(self::once())->method('getNodeFromStack')->willReturn($mockNodeOnStack);

        $templateParser = $this->getAccessibleMock(
            TemplateParser::class,
            ['splitTemplateAtDynamicTags', 'buildObjectTree']
        );
        $templateParser->_set('configuration', $parserConfiguration);
        $templateParser->_set('escapingEnabled', false);

        $templateParser->_call('textHandler', $mockState, 'string');
    }

    public static function dataProviderRecursiveArrayHandler(): \Generator
    {
        yield 'Single number' => [
            'string' => 'number: 123',
            'expected' => [
                'number' => 123,
            ]
        ];

        yield 'Single quoted string' => [
            'string' => 'string: \'some.string\'',
            'expected' => [
                'string' => new TextNode('some.string'),
            ]
        ];

        yield 'Single identifier' => [
            'string' => 'identifier: some.identifier',
            'expected' => [
                'identifier' => new ObjectAccessorNode('some.identifier', [])
            ]
        ];

        yield 'Single subarray' => [
            'string' => 'array: {number: 123, string: \'some.string\', identifier: some.identifier}',
            'expected' => [
                'array' => new ArrayNode([
                    'number' => 123,
                    'string' => new TextNode('some.string'),
                    'identifier' => new ObjectAccessorNode('some.identifier', [])
                ])
            ]
        ];

        yield 'Single subarray with numerical ids' => [
            'string' => 'array: {0: 123, 1: \'some.string\', 2: some.identifier}',
            'expected' => [
                'array' => new ArrayNode([
                    123,
                    new TextNode('some.string'),
                    new ObjectAccessorNode('some.identifier', [])
                ])
            ]
        ];

        yield 'Single quoted subarray' => [
            'string' => 'number: 123, array: \'{number: 234, string: \'some.string\', identifier: some.identifier}\'',
            'expected' => [
                'number' => 234,
                'string' => new TextNode('some.string'),
                'identifier' => new ObjectAccessorNode('some.identifier', [])
            ]
        ];

        yield 'Single quoted subarray with numerical keys' => [
            'string' => 'number: 123, array: \'{0: 234, 1: \'some.string\', 2: some.identifier}\'',
            'expected' => [
                'number' => 123,
                234,
                new TextNode('some.string'),
                new ObjectAccessorNode('some.identifier', [])
            ]
        ];

        yield 'Nested subarray' => [
            'string' => 'array: {number: 123, string: \'some.string\', identifier: some.identifier, array: {number: 123, string: \'some.string\', identifier: some.identifier}}',
            'expected' => [
                'array' => new ArrayNode([
                    'number' => 123,
                    'string' => new TextNode('some.string'),
                    'identifier' => new ObjectAccessorNode('some.identifier', []),
                    'array' => new ArrayNode([
                        'number' => 123,
                        'string' => new TextNode('some.string'),
                        'identifier' => new ObjectAccessorNode('some.identifier', [])
                    ])
                ])
            ]
        ];

        yield 'Mixed types' => [
            'string' => 'number: 123, string: \'some.string\', identifier: some.identifier, array: {number: 123, string: \'some.string\', identifier: some.identifier}',
            'expected' => [
                'number' => 123,
                'string' => new TextNode('some.string'),
                'identifier' => new ObjectAccessorNode('some.identifier', []),
                'array' => new ArrayNode([
                    'number' => 123,
                    'string' => new TextNode('some.string'),
                    'identifier' => new ObjectAccessorNode('some.identifier', [])
                ])
            ]
        ];

        $rootNode = new RootNode();
        $rootNode->addChildNode(new ObjectAccessorNode('some.{index}'));
        yield 'variable identifier' => [
            'string' => 'variableIdentifier: \'{some.{index}}\'',
            'expected' => [
                'variableIdentifier' => $rootNode
            ]
        ];

        $rootNode = new RootNode();
        $rootNode->addChildNode(new ObjectAccessorNode('some.{index}'));
        yield 'variable identifier in array' => [
            'string' => 'array: {variableIdentifier: \'{some.{index}}\'}',
            'expected' => [
                'array' => new ArrayNode([
                    'variableIdentifier' => $rootNode
                ])
            ]
        ];
    }

    /**
     * @dataProvider dataProviderRecursiveArrayHandler
     * @test
     */
    public function testRecursiveArrayHandler(string $string, array $expected): void
    {
        $state = new ParsingState();
        $resolver = $this->getMock(ViewHelperResolver::class, ['isNamespaceIgnored']);
        $resolver->expects(self::any())->method('isNamespaceIgnored')->willReturn(true);
        $context = new RenderingContextFixture();
        $context->setViewHelperResolver($resolver);
        $context->setVariableProvider(new StandardVariableProvider());
        $templateParser = new TemplateParser();
        $templateParser->setRenderingContext($context);
        $method = new \ReflectionMethod($templateParser, 'recursiveArrayHandler');
        $method->setAccessible(true);
        $result = $method->invokeArgs($templateParser, [$state, $string]);

        self::assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function abortIfRequiredArgumentsAreMissingThrowsException(): void
    {
        $this->expectException(Exception::class);

        $expected = [
            'firstArgument' => new ArgumentDefinition('firstArgument', 'string', '', false),
            'secondArgument' => new ArgumentDefinition('secondArgument', 'string', '', true)
        ];
        $templateParser = $this->getAccessibleMock(TemplateParser::class, []);
        $templateParser->_call('abortIfRequiredArgumentsAreMissing', $expected, []);
    }

    /**
     * @test
     */
    public function abortIfRequiredArgumentsAreMissingDoesNotThrowExceptionIfRequiredArgumentExists(): void
    {
        $expectedArguments = [
            new ArgumentDefinition('name1', 'string', 'desc', false),
            new ArgumentDefinition('name2', 'string', 'desc', true)
        ];
        $actualArguments = [
            'name2' => 'bla'
        ];

        $mockTemplateParser = $this->getAccessibleMock(TemplateParser::class);

        $mockTemplateParser->_call('abortIfRequiredArgumentsAreMissing', $expectedArguments, $actualArguments);
        // dummy assertion to avoid "did not perform any assertions" error
        self::assertTrue(true);
    }

    /**
     * @test
     */
    public function booleanArgumentsMustBeConvertedIntoBooleanNodes(): void
    {
        $argumentDefinitions = [
            'var1' => new ArgumentDefinition('var1', 'bool', 'desc', false),
            'var2' => new ArgumentDefinition('var2', 'boolean', 'desc', false)
        ];

        $viewHelper = $this->getMockBuilder(CommentViewHelper::class)->getMock();
        $resolver = $this->getMockBuilder(ViewHelperResolver::class)->onlyMethods(['getArgumentDefinitionsForViewHelper'])->getMock();
        $resolver->expects(self::once())->method('getArgumentDefinitionsForViewHelper')->with($viewHelper)->willReturn($argumentDefinitions);

        $context = new RenderingContextFixture();
        $context->setViewHelperResolver($resolver);

        $mockTemplateParser = $this->getAccessibleMock(TemplateParser::class, []);
        $mockTemplateParser->setRenderingContext($context);

        $parsedArguments = $mockTemplateParser->_call('parseArguments', 'var1="1" var2="0"}', $viewHelper);

        self::assertEquals(
            [
                'var1' => new BooleanNode(new NumericNode(1)),
                'var2' => new BooleanNode(new NumericNode(0))
            ],
            $parsedArguments
        );
    }
}
