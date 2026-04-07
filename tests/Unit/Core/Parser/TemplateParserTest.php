<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3Fluid\Fluid\Core\ErrorHandler\TolerantErrorHandler;
use TYPO3Fluid\Fluid\Core\Parser\Exception as ParserException;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception as ViewHelperException;

final class TemplateParserTest extends TestCase
{
    public static function quotedStrings(): array
    {
        return [
            ['"no quotes here"', 'no quotes here'],
            ["'no quotes here'", 'no quotes here'],
            ["'this \"string\" had \\'quotes\\' in it'", 'this "string" had \'quotes\' in it'],
            ['"this \\"string\\" had \'quotes\' in it"', 'this "string" had \'quotes\' in it'],
            ['"a weird \"string\" \'with\' *freaky* \\\\stuff', 'a weird "string" \'with\' *freaky* \\stuff'],
            ['\'\\\'escaped quoted string in string\\\'\'', '\'escaped quoted string in string\''],
        ];
    }

    #[DataProvider('quotedStrings')]
    #[Test]
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
            ['TemplateParserTestFixture14'],
        ];
    }

    #[DataProvider('templatesToSplit')]
    #[Test]
    public function splitTemplateAtDynamicTagsReturnsCorrectlySplitTemplate(string $templateName): void
    {
        $template = file_get_contents(__DIR__ . '/Fixtures/' . $templateName . '.html');
        $expectedResult = require __DIR__ . '/Fixtures/' . $templateName . '-split.php';
        $subject = new TemplateParser();
        $method = new \ReflectionMethod($subject, 'splitTemplateAtDynamicTags');
        self::assertSame($expectedResult, $method->invoke($subject, $template));
    }

    #[Test]
    public function initializeViewHelperAndAddItToStackHandlesParserExceptionsAsText(): void
    {
        $renderingContext = new RenderingContext();
        $renderingContext->setErrorHandler(new TolerantErrorHandler());
        $renderingContext->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $subject = $renderingContext->getTemplateParser();

        $state = new ParsingState();
        $rootNode = new RootNode();
        $state->setRootNode($rootNode);
        $state->pushNodeToStack($rootNode);

        $method = new \ReflectionMethod($subject, 'initializeViewHelperAndAddItToStack');
        $result = $method->invoke(
            $subject,
            $state,
            'test',
            'requiredArgument',
            fn(ViewHelperNode $viewHelperNode): array => throw new ParserException('interceptor failure'),
        );

        self::assertNull($result);
        $childNodes = $rootNode->getChildNodes();
        self::assertCount(1, $childNodes);
        self::assertInstanceOf(TextNode::class, $childNodes[0]);
        self::assertStringContainsString('Parser error: interceptor failure Offending code: ', $childNodes[0]->getText());
    }

    #[Test]
    public function initializeViewHelperAndAddItToStackHandlesViewHelperExceptionsAsText(): void
    {
        $renderingContext = new RenderingContext();
        $renderingContext->setErrorHandler(new TolerantErrorHandler());
        $renderingContext->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $subject = $renderingContext->getTemplateParser();

        $state = new ParsingState();
        $rootNode = new RootNode();
        $state->setRootNode($rootNode);
        $state->pushNodeToStack($rootNode);

        $method = new \ReflectionMethod($subject, 'initializeViewHelperAndAddItToStack');
        $result = $method->invoke(
            $subject,
            $state,
            'test',
            'requiredArgument',
            fn(ViewHelperNode $viewHelperNode): array => throw new ViewHelperException('interceptor failure'),
        );

        self::assertNull($result);
        $childNodes = $rootNode->getChildNodes();
        self::assertCount(1, $childNodes);
        self::assertInstanceOf(TextNode::class, $childNodes[0]);
        self::assertStringContainsString('ViewHelper error: interceptor failure - Offending code: ', $childNodes[0]->getText());
    }
}
