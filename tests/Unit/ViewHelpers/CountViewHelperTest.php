<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;

/**
 * Testcase for CountViewHelper
 */
class CountViewHelperTest extends ViewHelperBaseTestCase
{
    public function getStandardTestValues(): array
    {
        $context = new RenderingContextFixture();
        return [
            'returns count of simple array passed as argument' => [3, $context, ['subject' => ['foo', 'bar', 'baz']]],
            'returns count of simple array passed as child node' => [3, $context, null, [new ArrayNode(['foo', 'bar', 'baz'])]],
            'returns count of simple iterator passed as argument' => [3, $context, ['subject' => new \ArrayIterator(['foo', 'bar', 'baz'])]],
        ];
    }
}
