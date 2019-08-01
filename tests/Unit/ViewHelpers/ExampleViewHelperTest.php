<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\Sequencer;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\ViewHelpers\ExampleViewHelper;

/**
 * Testcase for ExampleViewHelper
 */
class ExampleViewHelperTest extends ViewHelperBaseTestCase
{
    /**
     * @test
     * @throws \ReflectionException
     */
    public function sequenceDelegatesToSequencer(): void
    {
        $subject = new ExampleViewHelper();
        $sequencer = $this->getMockBuilder(Sequencer::class)->setMethods(['sequenceUntilClosingTagAndIgnoreNested'])->disableOriginalConstructor()->getMock();
        $sequencer->expects($this->once())->method('sequenceUntilClosingTagAndIgnoreNested')->with($subject, 'foo', 'bar');
        $subject->sequence($sequencer, 'foo', 'bar');
    }

    public function getStandardTestValues(): array
    {
        $context = new RenderingContextFixture();
        return [
            'renders child content if evaluated directly' => ['foo', $context, null, [new TextNode('foo')]],
        ];
    }
}
