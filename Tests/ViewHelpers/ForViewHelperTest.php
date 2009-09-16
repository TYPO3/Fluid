<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 * @version $Id$
 */
/**
 * Testcase for ForViewHelper
 *
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

include_once(__DIR__ . '/Fixtures/ConstraintSyntaxTreeNode.php');
require_once(__DIR__ . '/ViewHelperBaseTestcase.php');
class ForViewHelperTest extends \F3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderExecutesTheLoopCorrectly() {
		$viewHelper = new \F3\Fluid\ViewHelpers\ForViewHelper();

		$variableContainer = new \F3\Fluid\Core\ViewHelper\TemplateVariableContainer(array());

		$viewHelperNode = new \F3\Fluid\ViewHelpers\Fixtures\ConstraintSyntaxTreeNode($variableContainer);
		$viewHelper->setTemplateVariableContainer($variableContainer);
		$viewHelper->setViewHelperNode($viewHelperNode);
		$viewHelper->render(array(0,1,2,3), 'innerVariable');

		$expectedCallProtocol = array(
			array('innerVariable' => 0),
			array('innerVariable' => 1),
			array('innerVariable' => 2),
			array('innerVariable' => 3)
		);
		$this->assertEquals($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs -> The for loop does not work as it should!');
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderPreservesKeys() {
		$viewHelper = new \F3\Fluid\ViewHelpers\ForViewHelper();

		$variableContainer = new \F3\Fluid\Core\ViewHelper\TemplateVariableContainer(array());

		$viewHelperNode = new \F3\Fluid\ViewHelpers\Fixtures\ConstraintSyntaxTreeNode($variableContainer);
		$viewHelper->setTemplateVariableContainer($variableContainer);
		$viewHelper->setViewHelperNode($viewHelperNode);
		$viewHelper->render(array('key1' => 'value1', 'key2' => 'value2'), 'innerVariable', 'someKey');

		$expectedCallProtocol = array(
			array(
				'innerVariable' => 'value1',
				'someKey' => 'key1'
			),
			array(
				'innerVariable' => 'value2',
				'someKey' => 'key2'
			)
		);
		$this->assertEquals($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs -> The for loop does not work as it should!');
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderReturnsEmptyStringIfObjectIsNull() {
		$viewHelper = new \F3\Fluid\ViewHelpers\ForViewHelper();

		$this->assertEquals('', $viewHelper->render(NULL, 'foo'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderReturnsEmptyStringIfObjectIsEmptyArray() {
		$viewHelper = new \F3\Fluid\ViewHelpers\ForViewHelper();

		$this->assertEquals('', $viewHelper->render(array(), 'foo'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderAddsCurrentValueToTemplateVariableContainerAndRemovesItAfterRendering() {
		$viewHelper = new \F3\Fluid\ViewHelpers\ForViewHelper();

		$mockViewHelperNode = $this->getMock('F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('evaluateChildNodes'), array(), '', FALSE);
		$mockViewHelperNode->expects($this->any())->method('evaluateChildNodes')->will($this->returnValue('foo'));

		$this->templateVariableContainer->expects($this->at(0))->method('add')->with('innerVariable', 'bar');
		$this->templateVariableContainer->expects($this->at(1))->method('remove')->with('innerVariable');
		$this->templateVariableContainer->expects($this->at(2))->method('add')->with('innerVariable', 'Fluid');
		$this->templateVariableContainer->expects($this->at(3))->method('remove')->with('innerVariable');

		$viewHelper->setTemplateVariableContainer($this->templateVariableContainer);
		$viewHelper->setViewHelperNode($mockViewHelperNode);
		$viewHelper->render(array('foo' => 'bar', 'FLOW3' => 'Fluid'), 'innerVariable');
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderAddsCurrentKeyToTemplateVariableContainerAndRemovesItAfterRendering() {
		$viewHelper = new \F3\Fluid\ViewHelpers\ForViewHelper();

		$mockViewHelperNode = $this->getMock('F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('evaluateChildNodes'), array(), '', FALSE);
		$mockViewHelperNode->expects($this->any())->method('evaluateChildNodes')->will($this->returnValue('foo'));

		$this->templateVariableContainer->expects($this->at(0))->method('add')->with('innerVariable', 'bar');
		$this->templateVariableContainer->expects($this->at(1))->method('add')->with('someKey', 'foo');
		$this->templateVariableContainer->expects($this->at(2))->method('remove')->with('innerVariable');
		$this->templateVariableContainer->expects($this->at(3))->method('remove')->with('someKey');
		$this->templateVariableContainer->expects($this->at(4))->method('add')->with('innerVariable', 'Fluid');
		$this->templateVariableContainer->expects($this->at(5))->method('add')->with('someKey', 'FLOW3');
		$this->templateVariableContainer->expects($this->at(6))->method('remove')->with('innerVariable');
		$this->templateVariableContainer->expects($this->at(7))->method('remove')->with('someKey');

		$viewHelper->setTemplateVariableContainer($this->templateVariableContainer);
		$viewHelper->setViewHelperNode($mockViewHelperNode);
		$viewHelper->render(array('foo' => 'bar', 'FLOW3' => 'Fluid'), 'innerVariable', 'someKey');
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderIteratesElementsInReverseOrderIfReverseIsTrue() {
		$viewHelper = new \F3\Fluid\ViewHelpers\ForViewHelper();

		$variableContainer = new \F3\Fluid\Core\ViewHelper\TemplateVariableContainer(array());

		$viewHelperNode = new \F3\Fluid\ViewHelpers\Fixtures\ConstraintSyntaxTreeNode($variableContainer);
		$viewHelper->setTemplateVariableContainer($variableContainer);
		$viewHelper->setViewHelperNode($viewHelperNode);
		$viewHelper->render(array(0,1,2,3), 'innerVariable', '', TRUE);

		$expectedCallProtocol = array(
			array('innerVariable' => 3),
			array('innerVariable' => 2),
			array('innerVariable' => 1),
			array('innerVariable' => 0)
		);
		$this->assertEquals($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs -> The for loop does not work as it should!');
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderPreservesKeysIfReverseIsTrue() {
		$viewHelper = new \F3\Fluid\ViewHelpers\ForViewHelper();

		$variableContainer = new \F3\Fluid\Core\ViewHelper\TemplateVariableContainer(array());

		$viewHelperNode = new \F3\Fluid\ViewHelpers\Fixtures\ConstraintSyntaxTreeNode($variableContainer);
		$viewHelper->setTemplateVariableContainer($variableContainer);
		$viewHelper->setViewHelperNode($viewHelperNode);
		$viewHelper->render(array('key1' => 'value1', 'key2' => 'value2'), 'innerVariable', 'someKey', TRUE);

		$expectedCallProtocol = array(
			array(
				'innerVariable' => 'value2',
				'someKey' => 'key2'
			),
			array(
				'innerVariable' => 'value1',
				'someKey' => 'key1'
			)
		);
		$this->assertEquals($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs -> The for loop does not work as it should!');
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function keyContainsNumericalIndexIfTheGivenArrayDoesNotHaveAKey() {
		$viewHelper = new \F3\Fluid\ViewHelpers\ForViewHelper();

		$variableContainer = new \F3\Fluid\Core\ViewHelper\TemplateVariableContainer(array());

		$viewHelperNode = new \F3\Fluid\ViewHelpers\Fixtures\ConstraintSyntaxTreeNode($variableContainer);
		$viewHelper->setTemplateVariableContainer($variableContainer);
		$viewHelper->setViewHelperNode($viewHelperNode);
		$viewHelper->render(array('foo', 'bar', 'baz'), 'innerVariable', 'someKey');

		$expectedCallProtocol = array(
			array(
				'innerVariable' => 'foo',
				'someKey' => 0
			),
			array(
				'innerVariable' => 'bar',
				'someKey' => 1
			),
			array(
				'innerVariable' => 'baz',
				'someKey' => 2
			)
		);
		$this->assertSame($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs -> The for loop does not work as it should!');
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function keyContainsNumericalIndexInAscendingOrderEvenIfReverseIsTrueIfTheGivenArrayDoesNotHaveAKey() {
		$viewHelper = new \F3\Fluid\ViewHelpers\ForViewHelper();

		$variableContainer = new \F3\Fluid\Core\ViewHelper\TemplateVariableContainer(array());

		$viewHelperNode = new \F3\Fluid\ViewHelpers\Fixtures\ConstraintSyntaxTreeNode($variableContainer);
		$viewHelper->setTemplateVariableContainer($variableContainer);
		$viewHelper->setViewHelperNode($viewHelperNode);
		$viewHelper->render(array('foo', 'bar', 'baz'), 'innerVariable', 'someKey', TRUE);

		$expectedCallProtocol = array(
			array(
				'innerVariable' => 'baz',
				'someKey' => 0
			),
			array(
				'innerVariable' => 'bar',
				'someKey' => 1
			),
			array(
				'innerVariable' => 'foo',
				'someKey' => 2
			)
		);
		$this->assertSame($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs -> The for loop does not work as it should!');
	}

	/**
	 * @test
	 * @expectedException \F3\Fluid\Core\ViewHelper\Exception
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderThrowsExceptionWhenPassingObjectsToEachThatAreNotTraversable() {
		$viewHelper = new \F3\Fluid\ViewHelpers\ForViewHelper();
		$object = new \stdClass();

		$viewHelper->render($object, 'innerVariable', 'someKey');
	}


	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderIteratesThroughElementsOfTraversableObjects() {
		$viewHelper = new \F3\Fluid\ViewHelpers\ForViewHelper();

		$variableContainer = new \F3\Fluid\Core\ViewHelper\TemplateVariableContainer(array());

		$viewHelperNode = new \F3\Fluid\ViewHelpers\Fixtures\ConstraintSyntaxTreeNode($variableContainer);
		$viewHelper->setTemplateVariableContainer($variableContainer);
		$viewHelper->setViewHelperNode($viewHelperNode);
		$traversableObject = new \ArrayObject(array('key1' => 'value1', 'key2' => 'value2'));
		$viewHelper->render($traversableObject, 'innerVariable');

		$expectedCallProtocol = array(
			array('innerVariable' => 'value1'),
			array('innerVariable' => 'value2'),
		);
		$this->assertEquals($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs -> The for loop does not work as it should!');
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderPreservesKeyWhenIteratingThroughElementsOfObjectsThatImplementIteratorInterface() {
		$viewHelper = new \F3\Fluid\ViewHelpers\ForViewHelper();

		$variableContainer = new \F3\Fluid\Core\ViewHelper\TemplateVariableContainer(array());

		$viewHelperNode = new \F3\Fluid\ViewHelpers\Fixtures\ConstraintSyntaxTreeNode($variableContainer);
		$viewHelper->setTemplateVariableContainer($variableContainer);
		$viewHelper->setViewHelperNode($viewHelperNode);
		$iteratorObject = new \ArrayIterator(array('key1' => 'value1', 'key2' => 'value2'));
		$viewHelper->render($iteratorObject, 'innerVariable', 'someKey');

		$expectedCallProtocol = array(
			array(
				'innerVariable' => 'value1',
				'someKey' => 'key1'
			),
			array(
				'innerVariable' => 'value2',
				'someKey' => 'key2'
			)
		);
		$this->assertEquals($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs -> The for loop does not work as it should!');
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function keyContainsTheNumericalIndexWhenIteratingThroughElementsOfObjectsOfTyeSplObjectStorage() {
		$viewHelper = new \F3\Fluid\ViewHelpers\ForViewHelper();

		$variableContainer = new \F3\Fluid\Core\ViewHelper\TemplateVariableContainer(array());

		$viewHelperNode = new \F3\Fluid\ViewHelpers\Fixtures\ConstraintSyntaxTreeNode($variableContainer);
		$viewHelper->setTemplateVariableContainer($variableContainer);
		$viewHelper->setViewHelperNode($viewHelperNode);
		$splObjectStorageObject = new \SplObjectStorage();
		$object1 = new \stdClass();
		$splObjectStorageObject->attach($object1);
		$object2 = new \stdClass();
		$splObjectStorageObject->attach($object2, 'foo');
		$object3 = new \stdClass();
		$splObjectStorageObject->offsetSet($object3, 'bar');
		$viewHelper->render($splObjectStorageObject, 'innerVariable', 'someKey');

		$expectedCallProtocol = array(
			array(
				'innerVariable' => $object1,
				'someKey' => 0
			),
			array(
				'innerVariable' => $object2,
				'someKey' => 1
			),
			array(
				'innerVariable' => $object3,
				'someKey' => 2
			)
		);
		$this->assertSame($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs -> The for loop does not work as it should!');
	}

}

?>
