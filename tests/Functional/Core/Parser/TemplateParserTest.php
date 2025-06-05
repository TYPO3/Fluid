<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\Parser;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\Compiler\StopCompilingException;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\Exception as ParserException;
use TYPO3Fluid\Fluid\Core\Parser\InterceptorInterface;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\EscapingNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NumericNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessorInterface;
use TYPO3Fluid\Fluid\Core\Parser\UnknownNamespaceException;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\ParserConfigurationAccessRenderingContext;

final class TemplateParserTest extends AbstractFunctionalTestCase
{
    public static function viewHelperInIgnoredNamespaceGetsIgnoredDataProvider(): array
    {
        return [
            'opening tag' => ['<ignored:foo>'],
            'closing tag' => ['</ignored:foo>'],
            'self-closing tag' => ['<ignored:foo />'],
        ];
    }

    #[Test]
    #[DataProvider('viewHelperInIgnoredNamespaceGetsIgnoredDataProvider')]
    public function viewHelperInIgnoredNamespaceGetsIgnored(string $source): void
    {
        $renderingContext = new RenderingContext();
        $renderingContext->getViewHelperResolver()->addNamespace('ignored', null);
        $subject = $renderingContext->getTemplateParser();
        $parsedTemplate = $subject->parse($source);

        /** @var TextNode */
        $viewHelperNode = $parsedTemplate->getRootNode()->getChildNodes()[0];
        self::assertInstanceOf(TextNode::class, $viewHelperNode);
        self::assertSame($source, $viewHelperNode->getText());
    }

    public static function viewHelperInInvalidNamespaceThrowsExceptionDataProvider(): array
    {
        return [
            'opening tag' => ['<invalid:foo>'],
            'closing tag' => ['</invalid:foo>'],
            'self-closing tag' => ['<invalid:foo />'],
            'inline syntax' => ['{invalid:foo()}'],
        ];
    }

    #[Test]
    #[DataProvider('viewHelperInInvalidNamespaceThrowsExceptionDataProvider')]
    public function viewHelperInInvalidNamespaceThrowsException(string $source): void
    {
        self::expectException(UnknownNamespaceException::class);

        $renderingContext = new RenderingContext();
        $subject = $renderingContext->getTemplateParser();
        $subject->parse($source);
    }

    public static function invalidViewHelperCallThrowsExceptionDataProvider(): array
    {
        return [
            'only opening tag' => ['<f:render>', 1238169398],
            'only closing tag' => ['</f:render>', 1224485838],
            'non-matching tags' => ['<f:render></f:format.trim>', 1224485398],
            'missing required argument' => ['<test:requiredArgument optional="test" />', 1237823699],
            'missing required argument, inline syntax' => ['{test:requiredArgument(optional: "test")} />', 1237823699],
        ];
    }

    #[Test]
    #[DataProvider('invalidViewHelperCallThrowsExceptionDataProvider')]
    public function invalidViewHelperCallThrowsException(string $source, int $exceptionCode): void
    {
        self::expectException(ParserException::class);
        self::expectExceptionCode($exceptionCode);

        $renderingContext = new RenderingContext();
        $renderingContext->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers');
        $subject = $renderingContext->getTemplateParser();
        $subject->parse($source);
    }

    #[Test]
    public function providedRequiredViewHelperArgumentThrowsNoException(): void
    {
        $renderingContext = new RenderingContext();
        $renderingContext->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers');
        $subject = $renderingContext->getTemplateParser();
        self::assertInstanceOf(ParsingState::class, $subject->parse('<test:requiredArgument required="test" />'));
    }

    public static function validateAdditionalArgumentsGetsCalledWithUndefinedArgumentsDataProvider(): array
    {
        return [
            'tag syntax with undefined argument' => ['<test:additionalArguments defined="1" undefined1="1" />', 'additional arguments validation: undefined1'],
            'inline syntax with undefined argument' => ['{test:additionalArguments(defined: \'1\', undefined1: \'1\')}', 'additional arguments validation: undefined1'],
            'tag syntax with multiple undefined arguments' => ['<test:additionalArguments defined="1" undefined1="1" undefined2="2" />', 'additional arguments validation: undefined1,undefined2'],
            'inline syntax with multiple undefined arguments' => ['{test:additionalArguments(defined: \'1\', undefined1: \'1\', undefined2: \'2\')}', 'additional arguments validation: undefined1,undefined2'],
            'tag syntax without undefined argument' => ['<test:additionalArguments defined="1" />', 'additional arguments validation: '],
            'inline syntax without undefined argument' => ['{test:additionalArguments(defined: \'1\')}', 'additional arguments validation: '],
            'tag syntax without any argument' => ['<test:additionalArguments />', 'additional arguments validation: '],
            // @todo this is inconsistent to tag syntax
            // 'inline syntax without any argument' => ['{test:additionalArguments()}', 'additional arguments validation: '],
        ];
    }

    #[Test]
    #[DataProvider('validateAdditionalArgumentsGetsCalledWithUndefinedArgumentsDataProvider')]
    public function validateAdditionalArgumentsGetsCalledWithUndefinedArguments(string $source, string $expectedExceptionMessage): void
    {
        // @todo this can be improved once mocking of ViewHelpers is possible
        self::expectExceptionCode(1748543954);
        self::expectExceptionMessage($expectedExceptionMessage);

        $renderingContext = new RenderingContext();
        $renderingContext->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers');
        $subject = $renderingContext->getTemplateParser();
        $subject->parse($source);
    }

    public static function viewHelperArgumentsGetParsedCorrectlyDataProvider(): iterable
    {
        yield ['<test:arbitraryArguments />', []];
        yield ['{test:arbitraryArguments()}', []];
        yield ['<test:arbitraryArguments a="2" />', ['a' => new NumericNode(2)]];
        yield ['{test:arbitraryArguments(a: \'2\')}', ['a' => new NumericNode(2)]];
        yield ['{test:arbitraryArguments(a: 2)}', ['a' => 2]];
        $rootNode = new RootNode();
        $rootNode->addChildNode(new ArrayNode(['number' => new NumericNode(123456)]));
        yield ['<test:arbitraryArguments a="{number: \'123456\'}" />', ['a' => $rootNode]];
        $rootNode = new RootNode();
        $rootNode->addChildNode(new ArrayNode(['number' => 123456]));
        yield ['<test:arbitraryArguments a="{number: 123456}" />', ['a' => $rootNode]];
        yield ['{test:arbitraryArguments(a: {number: \'123456\'})}', ['a' => new ArrayNode(['number' => new NumericNode(123456)])]];
        yield ['{test:arbitraryArguments(a: {number: 123456})}', ['a' => new ArrayNode(['number' => 123456])]];
        yield ['<test:arbitraryArguments a="2" b="foobar \' with \\" quotes" />', ['a' => new NumericNode(2), 'b' => new TextNode('foobar \' with " quotes')]];
        yield ['<test:arbitraryArguments a="2" b=\'foobar \\\' with " quotes\' />', ['a' => new NumericNode(2), 'b' => new TextNode('foobar \' with " quotes')]];
        yield ['{test:arbitraryArguments(a: \'2\', b: "foobar \' with \\" quotes")}', ['a' => new NumericNode(2), 'b' => new TextNode('foobar \' with " quotes')]];
        yield ['{test:arbitraryArguments(a: \'2\', b: \'foobar \\\' with " quotes\')}', ['a' => new NumericNode(2), 'b' => new TextNode('foobar \' with " quotes')]];
        // Optimization for simple strings
        yield ['{test:arbitraryArguments(a: \'foobar\')}', ['a' => new TextNode('foobar')]];
        // If possible sub-ViewHelpers are found, different object tree is generated
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('<test>foobar'));
        yield ['{test:arbitraryArguments(a: \'<test>foobar\')}', ['a' => $rootNode]];
        $rootNode = new RootNode();
        $rootNode->addChildNode(new TextNode('{ test }'));
        $rootNode->addChildNode(new TextNode('foobar'));
        yield ['{test:arbitraryArguments(a: \'{ test }foobar\')}', ['a' => $rootNode]];
        yield ['<test:scalarArguments boolArg="1" booleanArg="1" />', ['boolArg' => new BooleanNode([new NumericNode(1)]), 'booleanArg' => new BooleanNode([new NumericNode(1)])]];
        yield ['{test:scalarArguments(boolArg: 1, booleanArg: 1)}', ['boolArg' => new BooleanNode('1'), 'booleanArg' => new BooleanNode('1')]];
        yield ['<test:scalarArguments boolArg="0" booleanArg="0" />', ['boolArg' => new BooleanNode([new NumericNode(0)]), 'booleanArg' => new BooleanNode([new NumericNode(0)])]];
        yield ['{test:scalarArguments(boolArg: 0, booleanArg: 0)}', ['boolArg' => new BooleanNode('0'), 'booleanArg' => new BooleanNode('0')]];
        yield ['<test:scalarArguments boolArg="1 == 1" booleanArg="1 == 1" />', ['boolArg' => new BooleanNode('1 == 1'), 'booleanArg' => new BooleanNode('1 == 1')]];
        yield ['{test:scalarArguments(boolArg: \'1 == 1\', booleanArg: \'1 == 1\')}', ['boolArg' => new BooleanNode('1 == 1'), 'booleanArg' => new BooleanNode('1 == 1')]];
    }

    #[Test]
    #[DataProvider('viewHelperArgumentsGetParsedCorrectlyDataProvider')]
    public function viewHelperArgumentsGetParsedCorrectly(string $source, array $expected): void
    {
        $renderingContext = new RenderingContext();
        $renderingContext->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers');
        $subject = $renderingContext->getTemplateParser();
        $parsedTemplate = $subject->parse($source);

        /** @var ViewHelperNode */
        $viewHelperNode = $parsedTemplate->getRootNode()->getChildNodes()[0];
        self::assertInstanceOf(ViewHelperNode::class, $viewHelperNode);
        self::assertEquals($expected, $viewHelperNode->getArguments());
    }

    public static function arraysAreParsedCorrectlyDataProvider(): iterable
    {
        yield 'Single number' => [
            'source' => '{number: 123}',
            'expected' => new ArrayNode([
                'number' => 123,
            ]),
        ];

        yield 'Single quoted string' => [
            'source' => '{string: \'some.string\'}',
            'expected' => new ArrayNode([
                'string' => new TextNode('some.string'),
            ]),
        ];

        yield 'Single identifier' => [
            'source' => '{identifier: some.identifier}',
            'expected' => new ArrayNode([
                'identifier' => new ObjectAccessorNode('some.identifier'),
            ]),
        ];

        yield 'Single subarray' => [
            'source' => '{array: {number: 123, string: \'some.string\', identifier: some.identifier}}',
            'expected' => new ArrayNode([
                'array' => new ArrayNode([
                    'number' => 123,
                    'string' => new TextNode('some.string'),
                    'identifier' => new ObjectAccessorNode('some.identifier'),
                ]),
            ]),
        ];

        yield 'Single subarray with numerical ids' => [
            'source' => '{array: {0: 123, 1: \'some.string\', 2: some.identifier}}',
            'expected' => new ArrayNode([
                'array' => new ArrayNode([
                    123,
                    new TextNode('some.string'),
                    new ObjectAccessorNode('some.identifier'),
                ]),
            ]),
        ];

        // @todo these worked with the old unit tests, but these tested internal functionality (recursiveArrayHandler()).
        //       this array merging doesn't make sense with the proper API of TemplateParser
        /*
        yield 'Single quoted subarray' => [
            'source' => '{number: 123, array: \'{number: 234, string: \'some.string\', identifier: some.identifier}\'}',
            'expected' => new ArrayNode([
                'number' => 234,
                'string' => new TextNode('some.string'),
                'identifier' => new ObjectAccessorNode('some.identifier'),
            ]),
        ];
        yield 'Single quoted subarray with numerical keys' => [
            'source' => '{number: 123, array: \'{0: 234, 1: \'some.string\', 2: some.identifier}\'}',
            'expected' => new ArrayNode([
                'number' => 123,
                234,
                new TextNode('some.string'),
                new ObjectAccessorNode('some.identifier'),
            ]),
        ];
        */

        yield 'Nested subarray' => [
            'source' => '{array: {number: 123, string: \'some.string\', identifier: some.identifier, array: {number: 123, string: \'some.string\', identifier: some.identifier}}}',
            'expected' => new ArrayNode([
                'array' => new ArrayNode([
                    'number' => 123,
                    'string' => new TextNode('some.string'),
                    'identifier' => new ObjectAccessorNode('some.identifier'),
                    'array' => new ArrayNode([
                        'number' => 123,
                        'string' => new TextNode('some.string'),
                        'identifier' => new ObjectAccessorNode('some.identifier'),
                    ]),
                ]),
            ]),
        ];

        yield 'Mixed types' => [
            'source' => '{number: 123, string: \'some.string\', identifier: some.identifier, array: {number: 123, string: \'some.string\', identifier: some.identifier}}',
            'expected' => new ArrayNode([
                'number' => 123,
                'string' => new TextNode('some.string'),
                'identifier' => new ObjectAccessorNode('some.identifier'),
                'array' => new ArrayNode([
                    'number' => 123,
                    'string' => new TextNode('some.string'),
                    'identifier' => new ObjectAccessorNode('some.identifier'),
                ]),
            ]),
        ];

        $rootNode = new RootNode();
        $rootNode->addChildNode(new ObjectAccessorNode('some.{index}'));
        yield 'variable identifier' => [
            'source' => '{variableIdentifier: \'{some.{index}}\'}',
            'expected' => new ArrayNode([
                'variableIdentifier' => $rootNode,
            ]),
        ];

        $rootNode = new RootNode();
        $rootNode->addChildNode(new ObjectAccessorNode('some.{index}'));
        yield 'variable identifier in array' => [
            'source' => '{array: {variableIdentifier: \'{some.{index}}\'}}',
            'expected' => new ArrayNode([
                'array' => new ArrayNode([
                    'variableIdentifier' => $rootNode,
                ]),
            ]),
        ];
    }

    #[Test]
    #[DataProvider('arraysAreParsedCorrectlyDataProvider')]
    public function arraysAreParsedCorrectly(string $source, ArrayNode $expected): void
    {
        $renderingContext = new RenderingContext();
        $renderingContext->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers');
        $subject = $renderingContext->getTemplateParser();
        $parsedTemplate = $subject->parse('<test:arbitraryArguments a="' . $source . '" />');

        /** @var ViewHelperNode */
        $viewHelperNode = $parsedTemplate->getRootNode()->getChildNodes()[0];
        self::assertInstanceOf(ViewHelperNode::class, $viewHelperNode);
        /** @var ArrayNode */
        $arrayNode = $viewHelperNode->getArguments()['a']->getChildNodes()[0];
        self::assertInstanceOf(ArrayNode::class, $arrayNode);
        self::assertEquals($expected, $arrayNode);
    }

    #[Test]
    public function objectAccessorNodesAreEscaped(): void
    {
        $renderingContext = new RenderingContext();
        $subject = $renderingContext->getTemplateParser();
        $parsedTemplate = $subject->parse('{foo.bar}');

        /** @var EscapingNode */
        $escapingNode = $objectAccessorNode = $parsedTemplate->getRootNode()->getChildNodes()[0];
        self::assertInstanceOf(EscapingNode::class, $escapingNode);
        /** @var ObjectAccessorNode */
        $objectAccessorNode = $escapingNode->getNode();
        self::assertInstanceOf(ObjectAccessorNode::class, $objectAccessorNode);
        self::assertSame('foo.bar', $objectAccessorNode->getObjectPath());
    }

    #[Test]
    public function objectAccessorNodesAreNotEscapedIfEscapingIsDisabled(): void
    {
        $renderingContext = new RenderingContext();
        $subject = $renderingContext->getTemplateParser();
        // We can't use TemplateParser->setEscapingEnabled() here because reset() is called in parse()
        $parsedTemplate = $subject->parse('{escaping=false}{foo.bar}');

        /** @var ObjectAccessorNode */
        $objectAccessorNode = $parsedTemplate->getRootNode()->getChildNodes()[0];
        self::assertInstanceOf(ObjectAccessorNode::class, $objectAccessorNode);
        self::assertSame('foo.bar', $objectAccessorNode->getObjectPath());
    }

    #[Test]
    public function objectAccessorNodesAreRunThroughInterceptors(): void
    {
        $mockEscapingInterceptor = self::createMock(InterceptorInterface::class);
        $mockEscapingInterceptor->expects(self::once())->method('process')->willReturnArgument(0);
        $mockEscapingInterceptor->expects(self::once())->method('getInterceptionPoints')->willReturn([InterceptorInterface::INTERCEPT_OBJECTACCESSOR]);

        $mockInterceptor = self::createMock(InterceptorInterface::class);
        $mockInterceptor->expects(self::once())->method('process')->willReturnArgument(0);
        $mockInterceptor->expects(self::once())->method('getInterceptionPoints')->willReturn([InterceptorInterface::INTERCEPT_OBJECTACCESSOR]);

        $renderingContext = new ParserConfigurationAccessRenderingContext();
        $renderingContext->parserConfiguration->addInterceptor($mockInterceptor);
        $renderingContext->parserConfiguration->addEscapingInterceptor($mockEscapingInterceptor);
        $subject = $renderingContext->getTemplateParser();
        $subject->parse('{foo.bar}');
    }

    #[Test]
    public function textNodesAreNotEscapedIfEscapingIsDisabled(): void
    {
        $mockEscapingInterceptor = self::createMock(InterceptorInterface::class);
        $mockEscapingInterceptor->expects(self::never())->method('process')->willReturnArgument(0);
        $mockEscapingInterceptor->expects(self::once())->method('getInterceptionPoints')->willReturn([InterceptorInterface::INTERCEPT_TEXT]);

        $renderingContext = new ParserConfigurationAccessRenderingContext();
        $renderingContext->parserConfiguration->addEscapingInterceptor($mockEscapingInterceptor);
        $subject = $renderingContext->getTemplateParser();
        $subject->parse('{escaping=false}foo');
    }

    #[Test]
    public function textNodesAreRunThroughInterceptors(): void
    {
        $mockEscapingInterceptor = self::createMock(InterceptorInterface::class);
        $mockEscapingInterceptor->expects(self::once())->method('process')->willReturnArgument(0);
        $mockEscapingInterceptor->expects(self::once())->method('getInterceptionPoints')->willReturn([InterceptorInterface::INTERCEPT_TEXT]);

        $mockInterceptor = self::createMock(InterceptorInterface::class);
        $mockInterceptor->expects(self::once())->method('process')->willReturnArgument(0);
        $mockInterceptor->expects(self::once())->method('getInterceptionPoints')->willReturn([InterceptorInterface::INTERCEPT_TEXT]);

        $renderingContext = new ParserConfigurationAccessRenderingContext();
        $renderingContext->parserConfiguration->addInterceptor($mockInterceptor);
        $renderingContext->parserConfiguration->addEscapingInterceptor($mockEscapingInterceptor);
        $subject = $renderingContext->getTemplateParser();
        $subject->parse('foo');
    }

    #[Test]
    public function parseCallsTemplateProcessors(): void
    {
        $mockProcessor = self::createMock(TemplateProcessorInterface::class);
        $mockProcessor->expects(self::once())->method('preProcessSource')->willReturn('called');

        $renderingContext = new RenderingContext();
        $renderingContext->setTemplateProcessors([$mockProcessor]);
        $subject = $renderingContext->getTemplateParser();
        $parsedTemplate = $subject->parse('original');

        /** @var TextNode */
        $textNode = $parsedTemplate->getRootNode()->getChildNodes()[0];
        self::assertInstanceOf(TextNode::class, $textNode);
        self::assertSame('called', $textNode->getText());
    }

    #[Test]
    public function getOrParseAndStoreTemplateSetsAndStoresUncompilableStateInCache(): void
    {
        $mockCompiler = $this->createMock(TemplateCompiler::class);
        $mockCompiler->expects(self::never())->method('get');
        $mockCompiler->expects(self::once())->method('has')->willReturn(false);
        $mockCompiler->expects(self::exactly(2))->method('store')->willReturnOnConsecutiveCalls(
            // First try to store to cache
            self::throwException(new StopCompilingException()),
            // Second try with uncompilable flag
            '',
        );

        $renderingContext = new RenderingContext();
        $renderingContext->setTemplateCompiler($mockCompiler);
        $subject = $renderingContext->getTemplateParser();
        $parsedTemplate = $subject->getOrParseAndStoreTemplate(
            'identifier',
            fn(): string => 'test',
        );
        self::assertFalse($parsedTemplate->isCompilable());
    }
}
