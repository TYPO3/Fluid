<?php
namespace TYPO3\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Testcase for TextNode
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class TextNodeTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function renderReturnsSameStringAsGivenInConstructor() {
		$string = 'I can work quite effectively in a train!';
		$node = new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode($string);
		$this->assertEquals($node->evaluate($this->getMock('TYPO3\Fluid\Core\Rendering\RenderingContext')), $string, 'The rendered string of a text node is not the same as the string given in the constructor.');
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\Parser\Exception
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function constructorThrowsExceptionIfNoStringGiven() {
		new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode(123);
	}
}



?>
