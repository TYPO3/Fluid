<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\TemplateProcessor;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\PassthroughSourceException;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessor\PassthroughSourceModifierTemplateProcessor;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for BooleanNode
 */
class PassthroughSourceModifierTemplateProcessorTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testPreProcessSourceWithModifierEnabledThrowsPassthroughSourceException()
    {
        $subject = new PassthroughSourceModifierTemplateProcessor();
        $this->setExpectedException(PassthroughSourceException::class);
        $subject->preProcessSource('{parsing off}');
    }

    /**
     * @test
     */
    public function testPreProcessSourceWithModifierDisabledRemovesModifierAndReturnsSource()
    {
        $subject = new PassthroughSourceModifierTemplateProcessor();
        $result = $subject->preProcessSource('{parsing on}' . PHP_EOL . 'foobar');
        $this->assertSame(PHP_EOL . 'foobar', $result);
    }
}
