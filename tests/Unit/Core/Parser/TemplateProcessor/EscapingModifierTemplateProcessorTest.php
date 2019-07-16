<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\TemplateProcessor;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\Configuration;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessor\EscapingModifierTemplateProcessor;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for EscapingModifierTemplateProcessor
 */
class EscapingModifierTemplateProcessorTest extends UnitTestCase
{

    /**
     * @dataProvider getEscapingTestValues
     * @param string $templateSource
     * @param boolean $expected
     */
    public function testSetsEscapingToExpectedValueAndStripsModifier(string $templateSource, bool $expected): void
    {
        $subject = new EscapingModifierTemplateProcessor();

        $configuration = $this->getMockBuilder(Configuration::class)->setMethods(['setFeatureState'])->getMock();
        $configuration->expects($this->once())->method('setFeatureState')->with(Configuration::FEATURE_ESCAPING, $expected);

        $context = $this->getMockBuilder(RenderingContextFixture::class)->setMethods(['getParserConfiguration'])->getMock();
        $context->expects($this->once())->method('getParserConfiguration')->willReturn($configuration);

        $parser = new TemplateParser();
        $context->setTemplateParser($parser);
        $subject->setRenderingContext($context);
        $processedSource = $subject->preProcessSource($templateSource);
        $this->assertStringNotContainsString('{escaping', $processedSource);
    }

    /**
     * @return array
     */
    public function getEscapingTestValues(): array
    {
        return [
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
            ['{escapingEnabled=off}', false]
        ];
    }

    /**
     * @dataProvider getErrorTestValues
     * @param string $templateSource
     */
    public function testThrowsExceptionOnMultipleDefinitions(string $templateSource): void
    {
        $subject = new EscapingModifierTemplateProcessor();
        $this->setExpectedException(Exception::class);
        $subject->preProcessSource($templateSource);
    }

    /**
     * @return array
     */
    public function getErrorTestValues(): array
    {
        return [
            [
                '{escapingEnabled off}' . PHP_EOL . '{escapingEnabled off}',
                '{escapingEnabled on}' . PHP_EOL . '{escapingEnabled on}',
                '{escapingEnabled on}' . PHP_EOL . '{escapingEnabled off}',
                '{escaping off}' . PHP_EOL . '{escaping off}',
                '{escaping off}' . PHP_EOL . '{escaping true}',
                '{escaping off}' . PHP_EOL . '{escaping false}',
            ]
        ];
    }

}
