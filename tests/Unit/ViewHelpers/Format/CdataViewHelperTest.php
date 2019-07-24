<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Format;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestCase;

/**
 * Test for \TYPO3Fluid\Fluid\ViewHelpers\Format\CdataViewHelper
 */
class CdataViewHelperTest extends ViewHelperBaseTestCase
{
    public function getStandardTestValues(): array
    {
        $context = new RenderingContextFixture();
        return [
            'wraps node in CDATA' => ['<![CDATA[foo]]>', $context, null, [new TextNode('foo')]],
            'wraps multiple nodes in CDATA' => ['<![CDATA[foobar]]>', $context, null, [new TextNode('foo'), new TextNode('bar')]],
        ];
    }
}
