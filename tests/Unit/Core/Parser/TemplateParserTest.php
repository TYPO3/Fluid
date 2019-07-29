<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
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
        $templateParser = $this->getAccessibleMock(TemplateParser::class, ['dummy'], [new RenderingContextFixture()]);
        $this->assertEquals($unquoted, $templateParser->_call('unquoteString', $quoted));
    }
}
