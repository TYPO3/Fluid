<?php
namespace NamelessCoder\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\Core\Parser\SyntaxTree\TextNode;
use NamelessCoder\Fluid\Tests\UnitTestCase;

/**
 * Testcase for TextNode
 */
class TextNodeTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function renderReturnsSameStringAsGivenInConstructor() {
		$string = 'I can work quite effectively in a train!';
		$node = new TextNode($string);
		$this->assertEquals($node->evaluate($this->getMock('NamelessCoder\Fluid\Core\Rendering\RenderingContext')), $string, 'The rendered string of a text node is not the same as the string given in the constructor.');
	}

	/**
	 * @test
	 * @expectedException \NamelessCoder\Fluid\Core\Parser\Exception
	 */
	public function constructorThrowsExceptionIfNoStringGiven() {
		new TextNode(123);
	}
}
