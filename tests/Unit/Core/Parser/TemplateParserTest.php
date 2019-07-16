<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\StopCompilingException;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\Configuration;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\InterceptorInterface;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\AbstractNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NumericNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessorInterface;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\Fixtures\PostParseFacetViewHelper;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

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
    public function testParseCallsPreProcessOnTemplateProcessors(): void
    {
        $templateParser = new TemplateParser();
        $processor1 = $this->getMockForAbstractClass(
            TemplateProcessorInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['preProcessSource']
        );
        $processor2 = clone $processor1;
        $processor1->expects($this->once())->method('preProcessSource')->with('source1')->willReturn('source2');
        $processor2->expects($this->once())->method('preProcessSource')->with('source2')->willReturn('final');
        $context = new RenderingContextFixture();
        $context->setTemplateProcessors([$processor1, $processor2]);
        $context->setVariableProvider(new StandardVariableProvider());
        $templateParser->setRenderingContext($context);
        $result = $templateParser->parse('source1')->render($context);
        $this->assertEquals('final', $result);
    }

    public function quotedStrings(): array
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
    public function unquoteStringReturnsUnquotedStrings($quoted, $unquoted): void
    {
        $templateParser = $this->getAccessibleMock(TemplateParser::class, ['dummy']);
        $this->assertEquals($unquoted, $templateParser->_call('unquoteString', $quoted));
    }
}
