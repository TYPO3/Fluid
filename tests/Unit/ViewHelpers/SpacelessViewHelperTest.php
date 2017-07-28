<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\ViewHelpers\SpacelessViewHelper;

/**
 * Testcase for SpacelessViewHelper
 */
class SpacelessViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @param string $input
     * @param string $expected
     * @dataProvider getRenderStaticData
     * @test
     */
    public function testRenderStatic($input, $expected)
    {
        $context = $this->getMock(RenderingContextInterface::class);
        $this->assertEquals($expected, SpacelessViewHelper::renderStatic([], function () use ($input) {
            return $input;
        }, $context));
    }

    /**
     * @return array
     */
    public function getRenderStaticData()
    {
        return [
            'extra whitespace between tags' => ['<div>foo</div>  <div>bar</div>', '<div>foo</div><div>bar</div>'],
            'whitespace preserved in text node' => [PHP_EOL . '<div>' . PHP_EOL . 'foo</div>', '<div>' . PHP_EOL . 'foo</div>'],
            'whitespace removed from non-text node' => [PHP_EOL . '<div>' . PHP_EOL . '<div>foo</div></div>', '<div><div>foo</div></div>']
        ];
    }
}
