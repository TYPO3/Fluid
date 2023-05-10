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
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NumericNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessorInterface;
use TYPO3Fluid\Fluid\Core\Parser\UnknownNamespaceException;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
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
        $resolver = $this->createMock(ViewHelperResolver::class);
        $resolver->expects(self::once())->method('isNamespaceIgnored')->willReturn(true);
        $context = new RenderingContext();
        $context->setViewHelperResolver($resolver);
        $subject = new TemplateParser();
        $subject->setRenderingContext($context);
        $method = new \ReflectionMethod($subject, 'initializeViewHelperAndAddItToStack');
        $result = $method->invoke($subject, new ParsingState(), 'f', 'render', []);
        self::assertNull($result);
    }

    /**
     * @test
     */
    public function testInitializeViewHelperAndAddItToStackThrowsExceptionIfNamespaceInvalid(): void
    {
        $this->expectException(UnknownNamespaceException::class);
        $resolver = $this->createMock(ViewHelperResolver::class);
        $resolver->expects(self::once())->method('isNamespaceIgnored')->willReturn(false);
        $resolver->expects(self::once())->method('isNamespaceValid')->willReturn(false);
        $context = new RenderingContext();
        $context->setViewHelperResolver($resolver);
        $subject = new TemplateParser();
        $subject->setRenderingContext($context);
        $method = new \ReflectionMethod($subject, 'initializeViewHelperAndAddItToStack');
        $result = $method->invoke($subject, new ParsingState(), 'f', 'render', []);
        self::assertNull($result);
    }

    /**
     * @test
     */
    public function testClosingViewHelperTagHandlerReturnsFalseIfNamespaceIgnored(): void
    {
        $resolver = $this->createMock(ViewHelperResolver::class);
        $resolver->expects(self::once())->method('isNamespaceIgnored')->willReturn(true);
        $context = new RenderingContext();
        $context->setViewHelperResolver($resolver);
        $subject = new TemplateParser();
        $subject->setRenderingContext($context);
        $method = new \ReflectionMethod($subject, 'closingViewHelperTagHandler');
        $result = $method->invoke($subject, new ParsingState(), 'f', 'render');
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function testClosingViewHelperTagHandlerThrowsExceptionIfNamespaceInvalid(): void
    {
        $this->expectException(UnknownNamespaceException::class);
        $resolver = $this->createMock(ViewHelperResolver::class);
        $resolver->expects(self::once())->method('isNamespaceIgnored')->willReturn(false);
        $resolver->expects(self::once())->method('isNamespaceValid')->willReturn(false);
        $context = new RenderingContext();
        $context->setViewHelperResolver($resolver);
        $subject = new TemplateParser();
        $subject->setRenderingContext($context);
        $method = new \ReflectionMethod($subject, 'closingViewHelperTagHandler');
        $result = $method->invoke($subject, new ParsingState(), 'f', 'render');
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function isEscapingEnabledReturnsPreviouslySetEscapingEnabled(): void
    {
        $subject = new TemplateParser();
        self::assertTrue($subject->isEscapingEnabled());
        $subject->setEscapingEnabled(false);
        self::assertFalse($subject->isEscapingEnabled());
        $subject->setEscapingEnabled(true);
        self::assertTrue($subject->isEscapingEnabled());
    }

    /**
     * @test
     */
    public function testBuildObjectTreeThrowsExceptionOnUnclosedViewHelperTag(): void
    {
        $this->expectException(Exception::class);
        $renderingContext = new RenderingContext();
        $renderingContext->setVariableProvider(new StandardVariableProvider());
        $subject = new TemplateParser();
        $subject->setRenderingContext($renderingContext);
        $method = new \ReflectionMethod($subject, 'buildObjectTree');
        $method->invoke($subject, ['<f:render>'], TemplateParser::CONTEXT_INSIDE_VIEWHELPER_ARGUMENTS);
    }

    /**
     * @test
     */
    public function testParseCallsPreProcessOnTemplateProcessors(): void
    {
        $subject = new TemplateParser();
        $processor1 = $this->createMock(TemplateProcessorInterface::class);
        $processor2 = $this->createMock(TemplateProcessorInterface::class);
        $processor1->expects(self::once())->method('preProcessSource')->with('source1')->willReturn('source2');
        $processor2->expects(self::once())->method('preProcesssource')->with('source2')->willReturn('final');
        $context = new RenderingContext();
        $context->setTemplateProcessors([$processor1, $processor2]);
        $context->setVariableProvider(new StandardVariableProvider());
        $subject->setRenderingContext($context);
        $result = $subject->parse('source1')->render($context);
        self::assertEquals('final', $result);
    }

    /**
     * @test
     */
    public function getOrParseAndStoreTemplateSetsAndStoresUncompilableStateInCache(): void
    {
        $parsedTemplate = new ParsingState();
        $parsedTemplate->setCompilable(true);
        $subject = $this->getMockBuilder(TemplateParser::class)->onlyMethods(['parse'])->getMock();
        $subject->expects(self::once())->method('parse')->willReturn($parsedTemplate);
        $context = new RenderingContext();
        $compiler = $this->createMock(TemplateCompiler::class);
        $compiler->expects(self::never())->method('get');
        $compiler->expects(self::atLeastOnce())->method('has')->willReturn(false);
        $compiler->expects(self::atLeastOnce())->method('store')->willReturnOnConsecutiveCalls(
            self::throwException(new StopCompilingException()),
            true
        );
        $context->setTemplateCompiler($compiler);
        $context->setVariableProvider(new StandardVariableProvider());
        $subject->setRenderingContext($context);
        $result = $subject->getOrParseAndStoreTemplate('fake-foo-baz', function ($a, $b) {
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
        (new TemplateParser())->parse(123);
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
        $subject = new TemplateParser();
        self::assertEquals($unquoted, $subject->unquoteString($quoted));
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
        $subject = new TemplateParser();
        $method = new \ReflectionMethod($subject, 'splitTemplateAtDynamicTags');
        self::assertSame($expectedResult, $method->invoke($subject, $template));
    }

    /**
     * @test
     */
    public function buildObjectTreeCreatesRootNodeAndSetsUpParsingState(): void
    {
        $context = new RenderingContext();
        $context->setVariableProvider(new StandardVariableProvider());
        $subject = new TemplateParser();
        $subject->setRenderingContext($context);
        $method = new \ReflectionMethod($subject, 'buildObjectTree');
        self::assertInstanceOf(ParsingState::class, $method->invoke($subject, [], TemplateParser::CONTEXT_OUTSIDE_VIEWHELPER_ARGUMENTS));
    }

    /**
     * @test
     */
    public function closingViewHelperTagHandlerThrowsExceptionBecauseOfClosingTagWhichWasNeverOpened(): void
    {
        $this->expectException(\Exception::class);
        $mockNodeOnStack = $this->createMock(NodeInterface::class);
        $mockState = $this->createMock(ParsingState::class);
        $mockState->expects(self::once())->method('popNodeFromStack')->willReturn($mockNodeOnStack);
        $subject = new TemplateParser();
        $subject->setRenderingContext(new RenderingContext());
        $method = new \ReflectionMethod($subject, 'closingViewHelperTagHandler');
        $method->invoke($subject, $mockState, 'f', 'render');
    }

    /**
     * @test
     */
    public function closingViewHelperTagHandlerThrowsExceptionBecauseOfWrongTagNesting(): void
    {
        $this->expectException(\Exception::class);
        $mockState = $this->createMock(ParsingState::class);
        $subject = new TemplateParser();
        $subject->setRenderingContext(new RenderingContext());
        $method = new \ReflectionMethod($subject, 'closingViewHelperTagHandler');
        $method->invoke($subject, $mockState, 'f', 'render');
    }

    /**
     * @test
     */
    public function objectAccessorHandlerCreatesObjectAccessorNodeWithExpectedValueAndAddsItToStack(): void
    {
        $mockNodeOnStack = $this->createMock(NodeInterface::class);
        $mockNodeOnStack->expects(self::once())->method('addChildNode')->with(self::anything());
        $mockState = $this->createMock(ParsingState::class);
        $mockState->expects(self::once())->method('getNodeFromStack')->willReturn($mockNodeOnStack);
        $subject = new TemplateParser();
        $method = new \ReflectionMethod($subject, 'objectAccessorHandler');
        $method->invoke($subject, $mockState, 'objectAccessorString', '', '', '');
    }

    /**
     * @test
     */
    public function valuesFromObjectAccessorsAreRunThroughEscapingInterceptorsByDefault(): void
    {
        $objectAccessorNodeInterceptor = $this->createMock(InterceptorInterface::class);
        $objectAccessorNodeInterceptor->expects(self::once())->method('process')->willReturnArgument(0);
        $parserConfiguration = $this->createMock(Configuration::class);
        $parserConfiguration->expects(self::any())->method('getInterceptors')->willReturn([]);
        $parserConfiguration->expects(self::once())->method('getEscapingInterceptors')
            ->with(InterceptorInterface::INTERCEPT_OBJECTACCESSOR)
            ->willReturn([$objectAccessorNodeInterceptor]);
        $nodeMock = $this->createMock(NodeInterface::class);
        $mockState = $this->createMock(ParsingState::class);
        $mockState->expects(self::once())->method('getNodeFromStack')->willReturn($nodeMock);
        $subject = new TemplateParser();
        $property = new \ReflectionProperty($subject, 'configuration');
        $property->setValue($subject, $parserConfiguration);
        $method = new \ReflectionMethod($subject, 'objectAccessorHandler');
        $method->invoke($subject, $mockState, 'objectAccessorString', '', '', '');
    }

    /**
     * @test
     */
    public function valuesFromObjectAccessorsAreNotRunThroughEscapingInterceptorsIfEscapingIsDisabled(): void
    {
        $parserConfiguration = $this->createMock(Configuration::class);
        $parserConfiguration->expects(self::any())->method('getInterceptors')->willReturn([]);
        $parserConfiguration->expects(self::never())->method('getEscapingInterceptors');
        $nodeMock = $this->createMock(NodeInterface::class);
        $mockState = $this->createMock(ParsingState::class);
        $mockState->expects(self::once())->method('getNodeFromStack')->willReturn($nodeMock);
        $subject = new TemplateParser();
        $subject->setEscapingEnabled(false);
        $property = new \ReflectionProperty($subject, 'configuration');
        $property->setValue($subject, $parserConfiguration);
        $method = new \ReflectionMethod($subject, 'objectAccessorHandler');
        $method->invoke($subject, $mockState, 'objectAccessorString', '', '', '');
    }

    /**
     * @test
     */
    public function valuesFromObjectAccessorsAreRunThroughInterceptors(): void
    {
        $objectAccessorNodeInterceptor = $this->createMock(InterceptorInterface::class);
        $objectAccessorNodeInterceptor->expects(self::once())->method('process')->willReturnArgument(0);
        $parserConfiguration = $this->createMock(Configuration::class);
        $parserConfiguration->expects(self::any())->method('getEscapingInterceptors')->willReturn([]);
        $parserConfiguration->expects(self::once())->method('getInterceptors')
            ->with(InterceptorInterface::INTERCEPT_OBJECTACCESSOR)
            ->willReturn([$objectAccessorNodeInterceptor]);
        $nodeMock = $this->createMock(NodeInterface::class);
        $mockState = $this->createMock(ParsingState::class);
        $mockState->expects(self::once())->method('getNodeFromStack')->willReturn($nodeMock);
        $subject = new TemplateParser();
        $subject->setEscapingEnabled(false);
        $property = new \ReflectionProperty($subject, 'configuration');
        $property->setValue($subject, $parserConfiguration);
        $method = new \ReflectionMethod($subject, 'objectAccessorHandler');
        $method->invoke($subject, $mockState, 'objectAccessorString', '', '', '');
    }

    public static function parseArgumentsWorksAsExpectedDataProvider(): array
    {
        return [
            ['a="2"', ['a' => '2']],
            ['a="2" b="foobar \' with \\" quotes"', ['a' => '2', 'b' => 'foobar \' with " quotes']],
            [' arguments="{number : 362525200}"', ['arguments' => '{number : 362525200}']]
        ];
    }

    /**
     * @test
     * @dataProvider parseArgumentsWorksAsExpectedDataProvider
     */
    public function parseArgumentsWorksAsExpected(string $argumentsString, array $expected): void
    {
        $context = new RenderingContext();
        $viewHelper = $this->getMockBuilder(CommentViewHelper::class)->onlyMethods(['validateAdditionalArguments'])->getMock();
        $viewHelper->expects(self::once())->method('validateAdditionalArguments');
        $subject = $this->getMockBuilder(TemplateParser::class)->onlyMethods(['buildArgumentObjectTree'])->getMock();
        $subject->setRenderingContext($context);
        $subject->expects(self::any())->method('buildArgumentObjectTree')->willReturnArgument(0);
        $method = new \ReflectionMethod($subject, 'parseArguments');
        self::assertSame($expected, $method->invoke($subject, $argumentsString, $viewHelper));
    }

    /**
     * @test
     */
    public function buildArgumentObjectTreeReturnsTextNodeForSimplyString(): void
    {
        $subject = new TemplateParser();
        $method = new \ReflectionMethod($subject, 'buildArgumentObjectTree');
        $this->assertInstanceof(TextNode::class, $method->invoke($subject, 'a very plain string'));
    }

    /**
     * @test
     */
    public function buildArgumentObjectTreeBuildsObjectTreeForComplexString(): void
    {
        $objectTree = $this->createMock(ParsingState::class);
        $objectTree->expects(self::once())->method('getRootNode')->willReturn('theRootNode');
        $subject = $this->getMockBuilder(TemplateParser::class)
            ->onlyMethods(['splitTemplateAtDynamicTags', 'buildObjectTree'])
            ->getMock();
        $subject->expects(self::atLeastOnce())->method('splitTemplateAtDynamicTags')->with('a <very> {complex} string')->willReturn(['split string']);
        $subject->expects(self::atLeastOnce())->method('buildObjectTree')->with(['split string'])->willReturn($objectTree);
        $method = new \ReflectionMethod($subject, 'buildArgumentObjectTree');
        self::assertEquals('theRootNode', $method->invoke($subject, 'a <very> {complex} string'));
    }

    /**
     * @test
     */
    public function arrayHandlerAddsArrayNodeWithProperContentToStack(): void
    {
        $nodeMock = $this->createMock(NodeInterface::class);
        $nodeMock->expects(self::once())->method('addChildNode')->with(self::anything());
        $parsingStateMock = $this->createMock(ParsingState::class);
        $parsingStateMock->expects(self::once())->method('getNodeFromStack')->willReturn($nodeMock);
        $subject = $this->getMockBuilder(TemplateParser::class)->onlyMethods(['recursiveArrayHandler'])->getMock();
        $subject->expects(self::any())->method('recursiveArrayHandler')->willReturn('processedArrayText');
        $method = new \ReflectionMethod($subject, 'arrayHandler');
        $method->invoke($subject, $parsingStateMock, ['arrayText']);
    }

    /**
     * @test
     */
    public function textNodesAreRunThroughEscapingInterceptorsByDefault(): void
    {
        $textInterceptor = $this->createMock(InterceptorInterface::class);
        $textInterceptor->expects(self::once())->method('process')->with(self::anything())->willReturnArgument(0);
        $parserConfiguration = $this->createMock(Configuration::class);
        $parserConfiguration->expects(self::once())->method('getEscapingInterceptors')->with(InterceptorInterface::INTERCEPT_TEXT)->willReturn([$textInterceptor]);
        $parserConfiguration->expects(self::once())->method('getInterceptors')->willReturn([]);
        $nodeMock = $this->createMock(NodeInterface::class);
        $nodeMock->expects(self::once())->method('addChildNode')->with(self::anything());
        $parsingStateMock = $this->createMock(ParsingState::class);
        $parsingStateMock->expects(self::once())->method('getNodeFromStack')->willReturn($nodeMock);
        $subject = new TemplateParser();
        $property = new \ReflectionProperty($subject, 'configuration');
        $property->setValue($subject, $parserConfiguration);
        $method = new \ReflectionMethod($subject, 'textHandler');
        $method->invoke($subject, $parsingStateMock, 'string');
    }

    /**
     * @test
     */
    public function textNodesAreNotRunThroughEscapingInterceptorsIfEscapingIsDisabled(): void
    {
        $parserConfiguration = $this->createMock(Configuration::class);
        $parserConfiguration->expects(self::never())->method('getEscapingInterceptors');
        $parserConfiguration->expects(self::any())->method('getInterceptors')->willReturn([]);
        $nodeMock = $this->createMock(NodeInterface::class);
        $nodeMock->expects(self::once())->method('addChildNode')->with(self::anything());
        $parsingStateMock = $this->createMock(ParsingState::class);
        $parsingStateMock->expects(self::once())->method('getNodeFromStack')->willReturn($nodeMock);
        $subject = new TemplateParser();
        $property = new \ReflectionProperty($subject, 'configuration');
        $property->setValue($subject, $parserConfiguration);
        $subject->setEscapingEnabled(false);
        $method = new \ReflectionMethod($subject, 'textHandler');
        $method->invoke($subject, $parsingStateMock, 'string');
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
                'identifier' => new ObjectAccessorNode('some.identifier')
            ]
        ];

        yield 'Single subarray' => [
            'string' => 'array: {number: 123, string: \'some.string\', identifier: some.identifier}',
            'expected' => [
                'array' => new ArrayNode([
                    'number' => 123,
                    'string' => new TextNode('some.string'),
                    'identifier' => new ObjectAccessorNode('some.identifier')
                ])
            ]
        ];

        yield 'Single subarray with numerical ids' => [
            'string' => 'array: {0: 123, 1: \'some.string\', 2: some.identifier}',
            'expected' => [
                'array' => new ArrayNode([
                    123,
                    new TextNode('some.string'),
                    new ObjectAccessorNode('some.identifier')
                ])
            ]
        ];

        yield 'Single quoted subarray' => [
            'string' => 'number: 123, array: \'{number: 234, string: \'some.string\', identifier: some.identifier}\'',
            'expected' => [
                'number' => 234,
                'string' => new TextNode('some.string'),
                'identifier' => new ObjectAccessorNode('some.identifier')
            ]
        ];

        yield 'Single quoted subarray with numerical keys' => [
            'string' => 'number: 123, array: \'{0: 234, 1: \'some.string\', 2: some.identifier}\'',
            'expected' => [
                'number' => 123,
                234,
                new TextNode('some.string'),
                new ObjectAccessorNode('some.identifier')
            ]
        ];

        yield 'Nested subarray' => [
            'string' => 'array: {number: 123, string: \'some.string\', identifier: some.identifier, array: {number: 123, string: \'some.string\', identifier: some.identifier}}',
            'expected' => [
                'array' => new ArrayNode([
                    'number' => 123,
                    'string' => new TextNode('some.string'),
                    'identifier' => new ObjectAccessorNode('some.identifier'),
                    'array' => new ArrayNode([
                        'number' => 123,
                        'string' => new TextNode('some.string'),
                        'identifier' => new ObjectAccessorNode('some.identifier')
                    ])
                ])
            ]
        ];

        yield 'Mixed types' => [
            'string' => 'number: 123, string: \'some.string\', identifier: some.identifier, array: {number: 123, string: \'some.string\', identifier: some.identifier}',
            'expected' => [
                'number' => 123,
                'string' => new TextNode('some.string'),
                'identifier' => new ObjectAccessorNode('some.identifier'),
                'array' => new ArrayNode([
                    'number' => 123,
                    'string' => new TextNode('some.string'),
                    'identifier' => new ObjectAccessorNode('some.identifier')
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
        $resolver = $this->createMock(ViewHelperResolver::class);
        $resolver->expects(self::any())->method('isNamespaceIgnored')->willReturn(true);
        $context = new RenderingContext();
        $context->setViewHelperResolver($resolver);
        $context->setVariableProvider(new StandardVariableProvider());
        $subject = new TemplateParser();
        $subject->setRenderingContext($context);
        $method = new \ReflectionMethod($subject, 'recursiveArrayHandler');
        $result = $method->invoke($subject, $state, $string);

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
        $subject = new TemplateParser();
        $method = new \ReflectionMethod($subject, 'abortIfRequiredArgumentsAreMissing');
        $method->invoke($subject, $expected, []);
    }

    /**
     * @test
     */
    public function abortIfRequiredArgumentsAreMissingDoesNotThrowExceptionIfRequiredArgumentExists(): void
    {
        $expectedArguments = [
            'name1' => new ArgumentDefinition('name1', 'string', 'desc', false),
            'name2' => new ArgumentDefinition('name2', 'string', 'desc', true)
        ];
        $actualArguments = [
            'name2' => 'bla'
        ];
        $subject = new TemplateParser();
        $method = new \ReflectionMethod($subject, 'abortIfRequiredArgumentsAreMissing');
        $method->invoke($subject, $expectedArguments, $actualArguments);
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
        $viewHelper = $this->createMock(CommentViewHelper::class);
        $resolver = $this->createMock(ViewHelperResolver::class);
        $resolver->expects(self::once())->method('getArgumentDefinitionsForViewHelper')->with($viewHelper)->willReturn($argumentDefinitions);
        $context = new RenderingContext();
        $context->setViewHelperResolver($resolver);
        $subject = new TemplateParser();
        $subject->setRenderingContext($context);
        $method = new \ReflectionMethod($subject, 'parseArguments');
        $parsedArguments= $method->invoke($subject, 'var1="1" var2="0"}', $viewHelper);
        self::assertEquals(
            [
                'var1' => new BooleanNode(new NumericNode(1)),
                'var2' => new BooleanNode(new NumericNode(0))
            ],
            $parsedArguments
        );
    }
}
