<?php
namespace TYPO3\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Testcase for TextNode
 *
 */
class TextNodeTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function renderReturnsSameStringAsGivenInConstructor() {
		$string = 'I can work quite effectively in a train!';
		$node = new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode($string);
		$this->assertEquals($node->evaluate($this->getMock('TYPO3\Fluid\Core\Rendering\RenderingContext')), $string, 'The rendered string of a text node is not the same as the string given in the constructor.');
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\Parser\Exception
	 */
	public function constructorThrowsExceptionIfNoStringGiven() {
		new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode(123);
	}
}



?>
