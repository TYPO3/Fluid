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
 * Testcase for ViewHelperNode's evaluateBooleanExpression()
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class BooleanNodeTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @var TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode
	 */
	protected $viewHelperNode;

	/**
	 * @var TYPO3\Fluid\Core\Rendering\RenderingContextInterface
	 */
	protected $renderingContext;

	/**
	 * Setup fixture
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function setUp() {
		$this->renderingContext = $this->getMock('TYPO3\Fluid\Core\Rendering\RenderingContextInterface');
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\Parser\Exception
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function havingMoreThanThreeElementsInTheSyntaxTreeThrowsException() {
		$rootNode = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode');
		$rootNode->expects($this->once())->method('getChildNodes')->will($this->returnValue(array(1,2,3,4)));

		$booleanNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode($rootNode);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function comparingEqualNumbersReturnsTrue() {
		$rootNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('5'));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('=='));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('5'));

		$booleanNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode($rootNode);
		$this->assertTrue($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function comparingUnequalNumbersReturnsFalse() {
		$rootNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('5'));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('=='));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('3'));

		$booleanNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode($rootNode);
		$this->assertFalse($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function notEqualReturnsFalseIfNumbersAreEqual() {
		$rootNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('5'));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('!='));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('5'));

		$booleanNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode($rootNode);
		$this->assertFalse($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function notEqualReturnsTrueIfNumbersAreNotEqual() {
		$rootNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('5'));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('!='));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('3'));

		$booleanNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode($rootNode);
		$this->assertTrue($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function oddNumberModulo2ReturnsTrue() {
		$rootNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('43'));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('%'));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('2'));

		$booleanNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode($rootNode);
		$this->assertTrue($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function evenNumberModulo2ReturnsFalse() {
		$rootNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('42'));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('%'));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('2'));

		$booleanNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode($rootNode);
		$this->assertFalse($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function greaterThanReturnsTrueIfNumberIsReallyGreater() {
		$rootNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('>'));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('9'));

		$booleanNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode($rootNode);
		$this->assertTrue($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function greaterThanReturnsFalseIfNumberIsEqual() {
		$rootNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('>'));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));

		$booleanNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode($rootNode);
		$this->assertFalse($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function greaterOrEqualsReturnsTrueIfNumberIsReallyGreater() {
		$rootNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('>='));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('9'));

		$booleanNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode($rootNode);
		$this->assertTrue($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function greaterOrEqualsReturnsTrueIfNumberIsEqual() {
		$rootNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('>='));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));

		$booleanNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode($rootNode);
		$this->assertTrue($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function greaterOrEqualsReturnFalseIfNumberIsSmaller() {
		$rootNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('>='));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('11'));

		$booleanNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode($rootNode);
		$this->assertFalse($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function lessThanReturnsTrueIfNumberIsReallyless() {
		$rootNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('9'));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('<'));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));

		$booleanNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode($rootNode);
		$this->assertTrue($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function lessThanReturnsFalseIfNumberIsEqual() {
		$rootNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('<'));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));

		$booleanNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode($rootNode);
		$this->assertFalse($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function lessOrEqualsReturnsTrueIfNumberIsReallyLess() {
		$rootNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('9'));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('<='));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));

		$booleanNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode($rootNode);
		$this->assertTrue($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function lessOrEqualsReturnsTrueIfNumberIsEqual() {
		$rootNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('<='));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));

		$booleanNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode($rootNode);
		$this->assertTrue($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function lessOrEqualsReturnFalseIfNumberIsBigger() {
		$rootNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('11'));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('<='));
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('10'));

		$booleanNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode($rootNode);
		$this->assertFalse($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function lessOrEqualsReturnFalseIfComparingWithANegativeNumber() {
		$rootNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('11 <= -2.1'));

		$booleanNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode($rootNode);
		$this->assertFalse($booleanNode->evaluate($this->renderingContext));
	}


	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function objectsAreComparedStrictly() {
		$object1 = new \stdClass();
		$object2 = new \stdClass();

		$rootNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode();

		$object1Node = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode', array('evaluate'), array('foo'));
		$object1Node->expects($this->any())->method('evaluate')->will($this->returnValue($object1));

		$object2Node = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode', array('evaluate'), array('foo'));
		$object2Node->expects($this->any())->method('evaluate')->will($this->returnValue($object2));

		$rootNode->addChildNode($object1Node);
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('=='));
		$rootNode->addChildNode($object2Node);

		$booleanNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode($rootNode);
		$this->assertFalse($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function objectsAreComparedStrictlyInUnequalComparison() {
		$object1 = new \stdClass();
		$object2 = new \stdClass();

		$rootNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode();

		$object1Node = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode', array('evaluate'), array('foo'));
		$object1Node->expects($this->any())->method('evaluate')->will($this->returnValue($object1));

		$object2Node = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode', array('evaluate'), array('foo'));
		$object2Node->expects($this->any())->method('evaluate')->will($this->returnValue($object2));

		$rootNode->addChildNode($object1Node);
		$rootNode->addChildNode(new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('!='));
		$rootNode->addChildNode($object2Node);

		$booleanNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode($rootNode);
		$this->assertTrue($booleanNode->evaluate($this->renderingContext));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function convertToBooleanProperlyConvertsValuesOfTypeBoolean() {
		$this->assertFalse(\TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode::convertToBoolean(FALSE));
		$this->assertTrue(\TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode::convertToBoolean(TRUE));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function convertToBooleanProperlyConvertsValuesOfTypeString() {
		$this->assertFalse(\TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode::convertToBoolean(''));
		$this->assertFalse(\TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode::convertToBoolean('false'));
		$this->assertFalse(\TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode::convertToBoolean('FALSE'));

		$this->assertTrue(\TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode::convertToBoolean('true'));
		$this->assertTrue(\TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode::convertToBoolean('TRUE'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function convertToBooleanProperlyConvertsNumericValues() {
		$this->assertFalse(\TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode::convertToBoolean(0));
		$this->assertFalse(\TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode::convertToBoolean(-1));
		$this->assertFalse(\TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode::convertToBoolean('-1'));
		$this->assertFalse(\TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode::convertToBoolean(-.5));

		$this->assertTrue(\TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode::convertToBoolean(1));
		$this->assertTrue(\TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode::convertToBoolean(.5));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function convertToBooleanProperlyConvertsValuesOfTypeArray() {
		$this->assertFalse(\TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode::convertToBoolean(array()));

		$this->assertTrue(\TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode::convertToBoolean(array('foo')));
		$this->assertTrue(\TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode::convertToBoolean(array('foo' => 'bar')));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function convertToBooleanProperlyConvertsObjects() {
		$this->assertFalse(\TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode::convertToBoolean(NULL));

		$this->assertTrue(\TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode::convertToBoolean(new \stdClass()));
	}
}
?>