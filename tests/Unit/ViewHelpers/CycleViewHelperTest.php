<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;

/**
 * Testcase for CycleViewHelper
 */
class CycleViewHelperTest extends ViewHelperBaseTestCase
{
    public function getStandardTestValues(): array
    {
        $context = new RenderingContextFixture();
        $context->setViewHelperVariableContainer(new ViewHelperVariableContainer());
        $context->setVariableProvider(new StandardVariableProvider());
        return [
            'renders cycled variables' => ['foobarbaz', $context, ['values' => ['foo', 'bar', 'baz'], 'as' => 'value'], [new ObjectAccessorNode('value')], 3],
        ];
    }
}
