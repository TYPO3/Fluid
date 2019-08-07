<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\Sequencer;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\ViewHelpers\PassthroughViewHelper;

/**
 * Class PassthroughViewHelperTest
 */
class PassthroughViewHelperTest extends ViewHelperBaseTestCase
{
    /**
     * @test
     * @throws \ReflectionException
     */
    public function sequenceDelegatesToSequencer(): void
    {
        $instance = new PassthroughViewHelper();
        $sequencer = $this->getMockBuilder(Sequencer::class)->setMethods(['sequenceUntilClosingTagAndIgnoreNested'])->disableOriginalConstructor()->getMock();
        $sequencer->expects($this->once())->method('sequenceUntilClosingTagAndIgnoreNested')->with($instance, 'foo', 'bar');
        $instance->sequence($sequencer, 'foo', 'bar');
    }

    public function getStandardTestValues(): array
    {
        $context = new RenderingContextFixture();
        return [
            'outputs whatever is in tag content' => ['&lt;f:foo /&gt;', $context, [], [new TextNode('<f:foo />')]],
            'outputs whatever is in tag content with escape false' => ['<f:foo />', $context, ['escape' => false], [new TextNode('<f:foo />')]],
        ];
    }
}
