<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NumericNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for RootNode
 *
 */
class RootNodeTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function testEvaluateCallsEvaluateChildNodes() {
		$subject = $this->getMock('TYPO3Fluid\\Fluid\\Core\\Parser\\SyntaxTree\\RootNode', array('evaluateChildNodes'));
		$subject->expects($this->once())->method('evaluateChildNodes');
		$subject->evaluate(new RenderingContext());
	}

}
