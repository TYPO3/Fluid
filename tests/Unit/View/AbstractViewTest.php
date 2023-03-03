<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\View;

use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\AbstractView;

class AbstractViewTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testParentRenderMethodReturnsEmptyString()
    {
        $instance = $this->getMockForAbstractClass(AbstractView::class);
        $result = $instance->render();
        self::assertEquals('', $result);
    }

    /**
     * @test
     */
    public function testAssignsVariableAndReturnsSelf()
    {
        $mock = $this->getMockForAbstractClass(AbstractView::class);
        $mock->assign('test', 'foobar');
        self::assertAttributeEquals(['test' => 'foobar'], 'variables', $mock);
    }

    /**
     * @test
     */
    public function testAssignsMultipleVariablesAndReturnsSelf()
    {
        $mock = $this->getMockForAbstractClass(AbstractView::class);
        $mock->assignMultiple(['test' => 'foobar', 'baz' => 'barfoo']);
        self::assertAttributeEquals(['test' => 'foobar', 'baz' => 'barfoo'], 'variables', $mock);
    }
}
