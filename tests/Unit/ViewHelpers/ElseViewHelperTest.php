<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;

/**
 * Testcase for ElseViewHelper
 */
class ElseViewHelperTest extends ViewHelperBaseTestCase
{
    public function getStandardTestValues(): array
    {
        $context = new RenderingContextFixture();
        return [
            'renders child content when no condition is set' => ['foo', $context, null, [new TextNode('foo')]],
            'renders child content when condition is true' => ['foo', $context, ['if' => true], [new TextNode('foo')]],
            'does not render child content when condition is false' => [null, $context, ['if' => false], [new TextNode('foo')]],
        ];
    }
}
