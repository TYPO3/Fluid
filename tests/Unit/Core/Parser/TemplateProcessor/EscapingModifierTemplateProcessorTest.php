<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\TemplateProcessor;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

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
    public function testSetsEscapingToExpectedValueAndStripsModifier($templateSource, $expected)
    {
        $subject = new EscapingModifierTemplateProcessor();
        $context = new RenderingContextFixture();
        $parser = $this->getMockBuilder(TemplateParser::class)->setMethods(['setEscapingEnabled'])->getMock();
        if (!$expected) {
            $parser->expects($this->once())->method('setEscapingEnabled')->with(false);
        } else {
            $parser->expects($this->never())->method('setEscapingEnabled');
        }
        $context->setTemplateParser($parser);
        $subject->setRenderingContext($context);
        $processedSource = $subject->preProcessSource($templateSource);
        $this->assertNotContains('{escaping', $processedSource);
    }

    /**
     * @return array
     */
    public function getEscapingTestValues()
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
            ['{escapingEnabled=off}', false]
        ];
    }

    /**
     * @dataProvider getErrorTestValues
     * @param string $templateSource
     */
    public function testThrowsExceptionOnMultipleDefinitions($templateSource)
    {
        $subject = new EscapingModifierTemplateProcessor();
        $context = new RenderingContextFixture();
        $parser = $this->getMockBuilder(TemplateParser::class)->setMethods(['setEscapingEnabled'])->getMock();
        $context->setTemplateParser($parser);
        $subject->setRenderingContext($context);
        $this->setExpectedException(Exception::class);
        $subject->preProcessSource($templateSource);
    }

    /**
     * @return array
     */
    public function getErrorTestValues()
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
