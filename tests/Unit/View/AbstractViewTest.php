<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\View;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\AbstractView;

/**
 * Testcase for the AbstractView
 */
class AbstractViewViewTest extends UnitTestCase
{

    /**
     * @test
     */
    public function testParentRenderMethodReturnsEmptyString()
    {
        $instance = $this->getMockForAbstractClass(AbstractView::class);
        $result = $instance->render();
        $this->assertEquals('', $result);
    }

    /**
     * @test
     */
    public function testAssignsVariableAndReturnsSelf()
    {
        $mock = $this->getMockForAbstractClass(AbstractView::class);
        $mock->assign('test', 'foobar');
        $this->assertAttributeEquals(['test' => 'foobar'], 'variables', $mock);
    }

    /**
     * @test
     */
    public function testAssignsMultipleVariablesAndReturnsSelf()
    {
        $mock = $this->getMockForAbstractClass(AbstractView::class);
        $mock->assignMultiple(['test' => 'foobar', 'baz' => 'barfoo']);
        $this->assertAttributeEquals(['test' => 'foobar', 'baz' => 'barfoo'], 'variables', $mock);
    }
}
