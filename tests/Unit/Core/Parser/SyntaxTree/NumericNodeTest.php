<?php
namespace NamelessCoder\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\Core\Parser\SyntaxTree\NumericNode;
use NamelessCoder\Fluid\Tests\UnitTestCase;

/**
 * Testcase for NumericNode
 *
 */
class NumericNodeTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function renderReturnsProperIntegerGivenInConstructor() {
		$string = '1';
		$node = new NumericNode($string);
		$this->assertEquals($node->evaluate($this->getMock('NamelessCoder\Fluid\Core\Rendering\RenderingContext')), 1, 'The rendered value of a numeric node does not match the string given in the constructor.');
	}

	/**
	 * @test
	 */
	public function renderReturnsProperFloatGivenInConstructor() {
		$string = '1.1';
		$node = new NumericNode($string);
		$this->assertEquals($node->evaluate($this->getMock('NamelessCoder\Fluid\Core\Rendering\RenderingContext')), 1.1, 'The rendered value of a numeric node does not match the string given in the constructor.');
	}

	/**
	 * @test
	 * @expectedException \NamelessCoder\Fluid\Core\Parser\Exception
	 */
	public function constructorThrowsExceptionIfNoNumericGiven() {
		new NumericNode('foo');
	}

	/**
	 * @test
	 * @expectedException \NamelessCoder\Fluid\Core\Parser\Exception
	 */
	public function addChildNodeThrowsException() {
		$node = new NumericNode('1');
		$node->addChildNode(clone $node);
	}
}
