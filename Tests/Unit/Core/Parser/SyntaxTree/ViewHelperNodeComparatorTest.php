<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\Parser\SyntaxTree;

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
 * Testcase for ViewHelperNode's evaluateBooleanExpression()
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ViewHelperNodeComparatorTest extends \F3\Testing\BaseTestCase {

	/**
	 * @var F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode
	 */
	protected $viewHelperNode;

	/**
	 * @var F3\Fluid\Core\Rendering\RenderingContextInterface
	 */
	protected $renderingContext;

	/**
	 * Setup fixture
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function setUp() {
		$this->viewHelperNode = $this->getAccessibleMock('F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('dummy'), array(), '', FALSE);
		$this->renderingContext = $this->getMock('F3\Fluid\Core\Rendering\RenderingContextInterface');
	}

	/**
	 * @test
	 * @expectedException \F3\Fluid\Core\Parser\Exception
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function havingMoreThanThreeElementsInTheSyntaxTreeThrowsException() {
		$rootNode = $this->getMock('F3\Fluid\Core\Parser\SyntaxTree\RootNode');
		$rootNode->expects($this->once())->method('getChildNodes')->will($this->returnValue(array(1,2,3,4)));

		$this->viewHelperNode->_call('evaluateBooleanExpression', $rootNode, $this->renderingContext);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function comparingEqualNumbersReturnsTrue() {
		$rootNode = new \F3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('5'));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('=='));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('5'));

		$this->assertTrue($this->viewHelperNode->_call('evaluateBooleanExpression', $rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function comparingUnequalNumbersReturnsFalse() {
		$rootNode = new \F3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('5'));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('=='));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('3'));

		$this->assertFalse($this->viewHelperNode->_call('evaluateBooleanExpression', $rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function notEqualReturnsFalseIfNumbersAreEqual() {
		$rootNode = new \F3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('5'));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('!='));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('5'));

		$this->assertFalse($this->viewHelperNode->_call('evaluateBooleanExpression', $rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function notEqualReturnsTrueIfNumbersAreNotEqual() {
		$rootNode = new \F3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('5'));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('!='));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('3'));

		$this->assertTrue($this->viewHelperNode->_call('evaluateBooleanExpression', $rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function oddNumberModulo2ReturnsTrue() {
		$rootNode = new \F3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('43'));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('%'));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('2'));

		$this->assertTrue($this->viewHelperNode->_call('evaluateBooleanExpression', $rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function evenNumberModulo2ReturnsFalse() {
		$rootNode = new \F3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('42'));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('%'));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('2'));

		$this->assertFalse($this->viewHelperNode->_call('evaluateBooleanExpression', $rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function greaterThanReturnsTrueIfNumberIsReallyGreater() {
		$rootNode = new \F3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('>'));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('9'));

		$this->assertTrue($this->viewHelperNode->_call('evaluateBooleanExpression', $rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function greaterThanReturnsFalseIfNumberIsEqual() {
		$rootNode = new \F3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('>'));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));

		$this->assertFalse($this->viewHelperNode->_call('evaluateBooleanExpression', $rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function greaterOrEqualsReturnsTrueIfNumberIsReallyGreater() {
		$rootNode = new \F3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('>='));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('9'));

		$this->assertTrue($this->viewHelperNode->_call('evaluateBooleanExpression', $rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function greaterOrEqualsReturnsTrueIfNumberIsEqual() {
		$rootNode = new \F3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('>='));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));

		$this->assertTrue($this->viewHelperNode->_call('evaluateBooleanExpression', $rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function greaterOrEqualsReturnFalseIfNumberIsSmaller() {
		$rootNode = new \F3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('>='));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('11'));

		$this->assertFalse($this->viewHelperNode->_call('evaluateBooleanExpression', $rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function lessThanReturnsTrueIfNumberIsReallyless() {
		$rootNode = new \F3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('9'));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('<'));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));

		$this->assertTrue($this->viewHelperNode->_call('evaluateBooleanExpression', $rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function lessThanReturnsFalseIfNumberIsEqual() {
		$rootNode = new \F3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('<'));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));

		$this->assertFalse($this->viewHelperNode->_call('evaluateBooleanExpression', $rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function lessOrEqualsReturnsTrueIfNumberIsReallyLess() {
		$rootNode = new \F3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('9'));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('<='));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));

		$this->assertTrue($this->viewHelperNode->_call('evaluateBooleanExpression', $rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function lessOrEqualsReturnsTrueIfNumberIsEqual() {
		$rootNode = new \F3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('<='));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));

		$this->assertTrue($this->viewHelperNode->_call('evaluateBooleanExpression', $rootNode, $this->renderingContext));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function lessOrEqualsReturnFalseIfNumberIsBigger() {
		$rootNode = new \F3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('11'));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('<='));
		$rootNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));

		$this->assertFalse($this->viewHelperNode->_call('evaluateBooleanExpression', $rootNode, $this->renderingContext));
	}

}

?>