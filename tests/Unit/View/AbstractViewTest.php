<?php
namespace NamelessCoder\Fluid\Tests\Unit\View;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\View\AbstractTemplateView;
use NamelessCoder\Fluid\Core\Rendering\RenderingContext;
use NamelessCoder\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use NamelessCoder\Fluid\Tests\UnitTestCase;
use NamelessCoder\Fluid\View\TemplatePaths;

/**
 * Testcase for the AbstractView
 */
class AbstractViewViewTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function testParentRenderMethodReturnsEmptyString() {
		$instance = $this->getMockForAbstractClass('NamelessCoder\\Fluid\\View\\AbstractView');
		$result = $instance->render();
		$this->assertEquals('', $result);
	}

	/**
	 * @test
	 */
	public function testAssignsVariableAndReturnsSelf() {
		$mock = $this->getMockForAbstractClass('NamelessCoder\\Fluid\\View\\AbstractView');
		$mock->assign('test', 'foobar');
		$this->assertAttributeEquals(array('test' => 'foobar'), 'variables', $mock);
	}

	/**
	 * @test
	 */
	public function testAssignsMultipleVariablesAndReturnsSelf() {
		$mock = $this->getMockForAbstractClass('NamelessCoder\\Fluid\\View\\AbstractView');
		$mock->assignMultiple(array('test' => 'foobar', 'baz' => 'barfoo'));
		$this->assertAttributeEquals(array('test' => 'foobar', 'baz' => 'barfoo'), 'variables', $mock);
	}

}
