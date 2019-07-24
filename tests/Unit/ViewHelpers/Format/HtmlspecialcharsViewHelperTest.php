<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Format;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestCase;

/**
 * Test for \TYPO3Fluid\Fluid\ViewHelpers\Format\HtmlspecialcharsViewHelper
 */
class HtmlspecialcharsViewHelperTest extends ViewHelperBaseTestCase
{
    public function getStandardTestValues(): array
    {
        $html = '<b>html</b>';
        $context = new RenderingContextFixture();
        $context->setVariableProvider(new StandardVariableProvider(['foo' => $html]));
        return [
            'encodes html via argument value' => ['&lt;b&gt;html&lt;/b&gt;', $context, ['value' => $html]],
            'encodes html via child node' => ['&lt;b&gt;html&lt;/b&gt;', $context, null, [new ObjectAccessorNode('foo')]],
        ];
    }
}
