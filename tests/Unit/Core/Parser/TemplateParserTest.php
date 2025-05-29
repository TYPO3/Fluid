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
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;

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
}
