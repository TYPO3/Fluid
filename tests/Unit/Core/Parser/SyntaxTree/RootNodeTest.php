<?php
namespace NamelessCoder\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\Core\Parser\SyntaxTree\NumericNode;
use NamelessCoder\Fluid\Core\Rendering\RenderingContext;
use NamelessCoder\Fluid\Tests\UnitTestCase;

/**
 * Testcase for RootNode
 *
 */
class RootNodeTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function testEvaluateCallsEvaluateChildNodes() {
		$subject = $this->getMock('NamelessCoder\\Fluid\\Core\\Parser\\SyntaxTree\\RootNode', array('evaluateChildNodes'));
		$subject->expects($this->once())->method('evaluateChildNodes');
		$subject->evaluate(new RenderingContext());
	}

}
