<?php
namespace NamelessCoder\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\Core\Parser\ParsingState;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\TextNode;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use NamelessCoder\Fluid\Core\Variables\StandardVariableProvider;
use NamelessCoder\Fluid\Core\ViewHelper\TemplateVariableContainer;
use NamelessCoder\Fluid\Core\ViewHelper\ViewHelperResolver;
use NamelessCoder\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase;
use NamelessCoder\Fluid\ViewHelpers\LayoutViewHelper;

/**
 * Testcase for LayoutViewHelper
 */
class LayoutViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @test
	 */
	public function testInitializeArgumentsRegistersExpectedArguments() {
		$instance = $this->getMock('NamelessCoder\\Fluid\\ViewHelpers\\LayoutViewHelper', array('registerArgument'));
		$instance->expects($this->at(0))->method('registerArgument')->with('name', 'string', $this->anything());
		$instance->initializeArguments();
	}

	/**
	 * @test
	 */
	public function testRenderReturnsNull() {
		$instance = new LayoutViewHelper();
		$result = $instance->render();
		$this->assertNull($result);
	}

	/**
	 * @test
	 * @dataProvider getPostParseEventTestValues
	 * @param arary $arguments
	 * @param string $expectedLayoutName
	 */
	public function testPostParseEvent(array $arguments, $expectedLayoutName) {
		$instance = new LayoutViewHelper();
		$variableContainer = new StandardVariableProvider();
		$node = new ViewHelperNode(new ViewHelperResolver(), 'f', 'layout', $arguments, new ParsingState());
		$result = LayoutViewHelper::postParseEvent($node, $arguments, $variableContainer);
		$this->assertNull($result);
		$this->assertEquals($expectedLayoutName, $variableContainer->get('layoutName'));
	}

	/**
	 * @return array
	 */
	public function getPostParseEventTestValues() {
		return array(
			array(array('name' => 'test'), 'test'),
			array(array(), new TextNode('Default')),
		);
	}

}
