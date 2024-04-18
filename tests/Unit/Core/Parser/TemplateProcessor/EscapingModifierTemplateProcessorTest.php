<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\TemplateProcessor;

use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessor\EscapingModifierTemplateProcessor;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

final class EscapingModifierTemplateProcessorTest extends UnitTestCase
{
    public static function getEscapingTestValues(): array
    {
        return [
            ['', true],
            ['{escaping on}', true],
            ['{escaping = on}', true],
            ['{escaping=on}', true],
            ['{escaping off}', false],
            ['{escaping = off}', false],
            ['{escaping=off}', false],
            ['{escapingEnabled on}', true],
            ['{escapingEnabled = on}', true],
            ['{escapingEnabled=on}', true],
            ['{escapingEnabled off}', false],
            ['{escapingEnabled = off}', false],
            ['{escapingEnabled=off}', false],
        ];
    }

    /**
     * @dataProvider getEscapingTestValues
     * @test
     */
    public function testSetsEscapingToExpectedValueAndStripsModifier(string $templateSource, bool $expected): void
    {
        $subject = new EscapingModifierTemplateProcessor();
        $context = new RenderingContext();
        $parser = $this->getMockBuilder(TemplateParser::class)->onlyMethods(['setEscapingEnabled'])->getMock();
        if (!$expected) {
            $parser->expects(self::once())->method('setEscapingEnabled')->with(false);
        } else {
            $parser->expects(self::never())->method('setEscapingEnabled');
        }
        $context->setTemplateParser($parser);
        $subject->setRenderingContext($context);
        $processedSource = $subject->preProcessSource($templateSource);
        self::assertStringNotContainsString('{escaping', $processedSource);
    }

    public static function getErrorTestValues(): array
    {
        return [
            [
                '{escapingEnabled off}' . PHP_EOL . '{escapingEnabled off}',
                '{escapingEnabled on}' . PHP_EOL . '{escapingEnabled on}',
                '{escapingEnabled on}' . PHP_EOL . '{escapingEnabled off}',
                '{escaping off}' . PHP_EOL . '{escaping off}',
                '{escaping off}' . PHP_EOL . '{escaping true}',
                '{escaping off}' . PHP_EOL . '{escaping false}',
            ],
        ];
    }

    /**
     * @dataProvider getErrorTestValues
     * @test
     */
    public function testThrowsExceptionOnMultipleDefinitions(string $templateSource): void
    {
        $this->expectException(Exception::class);
        $subject = new EscapingModifierTemplateProcessor();
        $context = new RenderingContext();
        $parser = $this->getMockBuilder(TemplateParser::class)->onlyMethods(['setEscapingEnabled'])->getMock();
        $context->setTemplateParser($parser);
        $subject->setRenderingContext($context);
        $subject->preProcessSource($templateSource);
    }
}
