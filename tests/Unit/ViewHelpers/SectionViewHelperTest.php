<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\ViewHelpers\SectionViewHelper;

/**
 * Testcase for SectionViewHelper
 */
class SectionViewHelperTest extends ViewHelperBaseTestCase
{
    public function getStandardTestValues(): array
    {
        $viewHelperVariableContainer = new ViewHelperVariableContainer();
        $viewHelperVariableContainer->addOrUpdate(SectionViewHelper::class, 'isCurrentlyRenderingSection', true);
        $context1 = new RenderingContextFixture();
        $context1->setViewHelperVariableContainer($viewHelperVariableContainer);

        $context2 = new RenderingContextFixture();
        return [
            'renders child nodes when rendered with trigger variable in context' => ['rendered', $context1, ['name' => 'test'], [new TextNode('rendered')]],
            'does not render child nodes without trigger variable in context' => [null, $context2, ['name' => 'test'], [new TextNode('foo'), new TextNode('bar')]],
        ];
    }
}
