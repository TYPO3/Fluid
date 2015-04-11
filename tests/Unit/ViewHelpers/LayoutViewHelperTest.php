<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\Parser\ParsingState;
use TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer;
use TYPO3\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase;
use TYPO3\Fluid\ViewHelpers\LayoutViewHelper;

/**
 * Testcase for LayoutViewHelper
 */
class LayoutViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @test
	 */
	public function testInitializeArgumentsRegistersExpectedArguments() {
		$instance = $this->getMock('TYPO3\\Fluid\\ViewHelpers\\LayoutViewHelper', array('registerArgument'));
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
