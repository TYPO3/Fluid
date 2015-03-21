<?php
namespace TYPO3\Fluid\Tests\Unit\View;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\View\AbstractTemplateView;
use TYPO3\Fluid\Core\Rendering\RenderingContext;
use TYPO3\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3\Fluid\Tests\UnitTestCase;
use TYPO3\Fluid\View\TemplatePaths;

/**
 * Testcase for the AbstractView
 */
class AbstractViewViewTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function testAssignsVariableAndReturnsSelf() {
		$mock = $this->getMockForAbstractClass('TYPO3\\Fluid\\View\\AbstractView');
		$mock->assign('test', 'foobar');
		$this->assertAttributeEquals(array('test' => 'foobar'), 'variables', $mock);
	}

	/**
	 * @test
	 */
	public function testAssignsMultipleVariablesAndReturnsSelf() {
		$mock = $this->getMockForAbstractClass('TYPO3\\Fluid\\View\\AbstractView');
		$mock->assignMultiple(array('test' => 'foobar', 'baz' => 'barfoo'));
		$this->assertAttributeEquals(array('test' => 'foobar', 'baz' => 'barfoo'), 'variables', $mock);
	}

}
