<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\View;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\View\AbstractTemplateView;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\TemplatePaths;

/**
 * Testcase for the AbstractView
 */
class AbstractViewViewTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function testParentRenderMethodReturnsEmptyString() {
		$instance = $this->getMockForAbstractClass('TYPO3Fluid\\Fluid\\View\\AbstractView');
		$result = $instance->render();
		$this->assertEquals('', $result);
	}

	/**
	 * @test
	 */
	public function testAssignsVariableAndReturnsSelf() {
		$mock = $this->getMockForAbstractClass('TYPO3Fluid\\Fluid\\View\\AbstractView');
		$mock->assign('test', 'foobar');
		$this->assertAttributeEquals(array('test' => 'foobar'), 'variables', $mock);
	}

	/**
	 * @test
	 */
	public function testAssignsMultipleVariablesAndReturnsSelf() {
		$mock = $this->getMockForAbstractClass('TYPO3Fluid\\Fluid\\View\\AbstractView');
		$mock->assignMultiple(array('test' => 'foobar', 'baz' => 'barfoo'));
		$this->assertAttributeEquals(array('test' => 'foobar', 'baz' => 'barfoo'), 'variables', $mock);
	}

}
