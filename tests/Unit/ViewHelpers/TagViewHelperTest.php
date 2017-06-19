<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\ViewHelpers\SpacelessViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\TagViewHelper;

/**
 * Testcase for TagViewHelper
 */
class TagViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @param array $arguments
     * @param mixed $tagContent
     * @param string $expected
     * @dataProvider getRenderStaticData
     * @test
     */
    public function testRenderStatic(array $arguments, $tagContent, $expected)
    {
        $context = $this->getMock(RenderingContextInterface::class);
        $this->assertEquals($expected, TagViewHelper::renderStatic($arguments, function () use ($tagContent) {
            return $tagContent;
        }, $context));
    }

    /**
     * @return array
     */
    public function getRenderStaticData()
    {
        return [
            'standard empty tag' => [['tag' => 'div', 'forceClosingTag' => false, 'ignoreEmptyAttributes' => false], null, '<div />'],
            'forces closing tag' => [['tag' => 'div', 'forceClosingTag' => true, 'ignoreEmptyAttributes' => false], null, '<div></div>'],
            'ignores empty attribute' => [['tag' => 'div', 'forceClosingTag' => false, 'ignoreEmptyAttributes' => true, 'somethingempty' => ''], null, '<div />'],
            'includes empty attribute' => [['tag' => 'div', 'forceClosingTag' => false, 'ignoreEmptyAttributes' => false, 'somethingempty' => ''], null, '<div somethingempty="" />'],
            'supports data array' => [['tag' => 'div', 'forceClosingTag' => false, 'ignoreEmptyAttributes' => false, 'data' => ['foo' => 'bar']], null, '<div data-foo="bar" />'],
            'data prefix has priority over data array' => [['tag' => 'div', 'forceClosingTag' => false, 'ignoreEmptyAttributes' => false, 'data' => ['bar' => 'baz', 'foo' => 'bar'], 'data-foo' => 'baz'], null, '<div data-bar="baz" data-foo="baz" />'],
        ];
    }
}
