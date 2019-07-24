<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;

/**
 * Testcase for VariableViewHelper
 */
class VariableViewHelperTest extends ViewHelperBaseTestCase
{
    public function getStandardTestValues(): array
    {
        $context = new RenderingContextFixture();
        $variableProvider = $this->getMockBuilder(VariableProviderInterface::class)->getMockForAbstractClass();
        $variableProvider->expects($this->atLeastOnce())->method('add')->with('foo');
        $context->setVariableProvider($variableProvider);
        return [
            'assigns variable' => [null, $context, ['name' => 'foo']],
            'suppresses child rendering' => [null, $context, ['name' => 'foo'], [new TextNode('foo')]],
        ];
    }
}
