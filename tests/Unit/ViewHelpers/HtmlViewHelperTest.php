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
 * Testcase for HtmlViewHelper
 */
class HtmlViewHelperTest extends ViewHelperBaseTestCase
{
    public function getStandardTestValues(): array
    {
        $context = new RenderingContextFixture();
        return [
            'renders tag without data-namespace-typo3-fluid' => ['<html foo="bar">foo</html>', $context, ['foo' => 'bar'], [new TextNode('foo')]],
            'renders tag with data-namespace-typo3-fluid="false" but does not render data-namespace-typo3-fluid attribute' => ['<html foo="bar">foo</html>', $context, ['data-namespace-typo3-fluid' => 'false', 'foo' => 'bar'], [new TextNode('foo')]],
            'renders tag with data-namespace-typo3-fluid="arbitrary" but does not render data-namespace-typo3-fluid attribute' => ['<html foo="bar">foo</html>', $context, ['data-namespace-typo3-fluid' => 'arbitrary', 'foo' => 'bar'], [new TextNode('foo')]],
            'does not render tag with data-namespace-typo3-fluid="true"' => ['foo', $context, ['data-namespace-typo3-fluid' => 'true', 'foo' => 'bar'], [new TextNode('foo')]],
        ];
    }
}
