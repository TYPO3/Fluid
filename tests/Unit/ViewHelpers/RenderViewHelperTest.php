<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * Testcase for RenderViewHelper
 */
class RenderViewHelperTest extends ViewHelperBaseTestCase
{
    public function getStandardTestValues(): array
    {
        $context = new RenderingContextFixture();
        $view = $this->getMockBuilder(TemplateView::class)->setMethods(['renderPartial', 'renderSection'])->getMock();
        $view->expects($this->any())->method('renderSection')->willReturn('sectionRendered');
        $view->expects($this->any())->method('renderPartial')->withConsecutive(
            ['partial', null, [], false],
            ['partial', 'section', [], false],
            ['partial', 'section', ['foo' => 'bar'], false]
        )->willReturnOnConsecutiveCalls('partialRendered', 'sectionRendered', 'renderedWithArguments');
        $viewHelperVariableContainer = new ViewHelperVariableContainer();
        $viewHelperVariableContainer->setView($view);
        $context->setViewHelperVariableContainer($viewHelperVariableContainer);
        return [
            'renders section' => ['sectionRendered', $context, ['section' => 'section']],
            'renders partial' => ['partialRendered', $context, ['partial' => 'partial']],
            'renders section in partial' => ['sectionRendered', $context, ['section' => 'section', 'partial' => 'partial']],
            'renders section in partial with arguments' => ['renderedWithArguments', $context, ['section' => 'section', 'partial' => 'partial', 'arguments' => ['foo' => 'bar']]],
        ];
    }
}
