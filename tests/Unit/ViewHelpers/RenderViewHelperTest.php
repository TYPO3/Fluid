<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\FluidRenderer;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\ViewHelpers\RenderViewHelper;

/**
 * Testcase for RenderViewHelper
 */
class RenderViewHelperTest extends ViewHelperBaseTestCase
{
    /**
     * @test
     */
    public function throwsErrorWhenMissingAllTargetArguments(): void
    {
        $context = new RenderingContextFixture();
        $viewHelper = new RenderViewHelper();
        $this->setExpectedException(\InvalidArgumentException::class);
        $viewHelper->evaluate($context);
    }

    public function getStandardTestValues(): array
    {
        $context = new RenderingContextFixture();
        $renderer = $this->getMockBuilder(FluidRenderer::class)->setMethods(['renderPartial', 'renderSection'])->setConstructorArgs([$context])->getMock();
        $renderer->expects($this->any())->method('renderSection')->willReturn('sectionRendered');
        $renderer->expects($this->any())->method('renderPartial')->withConsecutive(
            ['partial', null, [], false],
            ['partial', 'section', [], false],
            ['partial', 'section', ['foo' => 'bar'], false]
        )->willReturnOnConsecutiveCalls('partialRendered', 'sectionRendered', 'renderedWithArguments', 'renderedWithContentAs');
        $context->setRenderer($renderer);
        return [
            'renders section' => ['sectionRendered', $context, ['section' => 'section']],
            'renders partial' => ['partialRendered', $context, ['partial' => 'partial']],
            'renders section in partial' => ['sectionRendered', $context, ['section' => 'section', 'partial' => 'partial']],
            'renders section in partial with arguments' => ['renderedWithArguments', $context, ['section' => 'section', 'partial' => 'partial', 'arguments' => ['foo' => 'bar']]],
            'renders with contentAs argument' => ['renderedWithContentAs', $context, ['partial' => 'partial', 'contentAs' => 'foo'], []],
        ];
    }
}
