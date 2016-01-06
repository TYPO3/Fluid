<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\ViewHelpers\ElseViewHelper;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;

/**
 * Testcase for ElseViewHelper
 */
class ElseViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @test
	 */
	public function testInitializeArgumentsRegistersExpectedArguments() {
		$instance = $this->getMock(ElseViewHelper::class, array('registerArgument'));
		$instance->expects($this->at(0))->method('registerArgument')->with('if', 'boolean', $this->anything(), FALSE, NULL);
		$instance->initializeArguments();
	}

	/**
	 * @test
	 */
	public function renderRendersChildren() {
		$viewHelper = $this->getMock(ElseViewHelper::class, array('renderChildren'));

		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue('foo'));
		$actualResult = $viewHelper->render();
		$this->assertEquals('foo', $actualResult);
	}

	/**
	 * @test
	 */
	public function testCompilesToEmptyString() {
		$viewHelper = new ElseViewHelper();
		$node = $this->getMock(ViewHelperNode::class, array(), array(), '', FALSE);
		$compiler = $this->getMock(TemplateCompiler::class);
		$init = '';
		$result = $viewHelper->compile('', '', $init, $node, $compiler);
		$this->assertEmpty($init);
		$this->assertEquals('\'\'', $result);
	}

}
