<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\ViewHelpers\OrViewHelper;

/**
 * Class OrViewHelperTest
 */
class OrViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @test
     */
    public function testInitializeArguments()
    {
        $instance = $this->getMock(OrViewHelper::class, ['registerArgument']);
        $instance->expects($this->at(0))->method('registerArgument')->with('content', 'mixed', $this->anything(), false, '');
        $instance->expects($this->at(1))->method('registerArgument')->with('alternative', 'mixed', $this->anything(), false, '');
        $instance->expects($this->at(2))->method('registerArgument')->with('arguments', 'array', $this->anything());
        $instance->setRenderingContext(new RenderingContextFixture());
        $instance->initializeArguments();
    }

    /**
     * @test
     * @dataProvider getRenderTestValues
     * @param array $arguments
     * @param mixed $expected
     */
    public function testRender($arguments, $expected)
    {
        $instance = $this->getMock(OrViewHelper::class, ['renderChildren']);
        $instance->expects($this->exactly((integer) empty($arguments['content'])))->method('renderChildren')->willReturn($arguments['content']);
        $instance->setArguments($arguments);
        $instance->setRenderingContext(new RenderingContextFixture());
        $result = $instance->render();
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getRenderTestValues()
    {
        return [
            [['arguments' => null, 'content' => 'alt', 'alternative' => 'alternative'], 'alt'],
            [['arguments' => null, 'content' => '1', 'alternative' => 'alternative'], '1'],
        ];
    }

    /**
     * @test
     * @dataProvider getRenderAlternativeTestValues
     * @param array $arguments
     * @param mixed $expected
     */
    public function testRenderAlternative($arguments, $expected)
    {
        $instance = $this->getMock(OrViewHelper::class, ['renderChildren']);
        $instance->expects($this->once())->method('renderChildren')->willReturn(null);
        $arguments['content'] = null;
        $instance->setArguments($arguments);
        $instance->setRenderingContext(new RenderingContextFixture());
        $result = $instance->render();
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getRenderAlternativeTestValues()
    {
        return [
            [['arguments' => null, 'alternative' => 'alternative'], 'alternative'],
            [['arguments' => ['abc'], 'alternative' => 'alternative %s alt'], 'alternative abc alt'],
        ];
    }
}
