<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\TemplateVariableContainer;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\ConstraintSyntaxTreeNode;
use TYPO3Fluid\Fluid\ViewHelpers\ForViewHelper;

/**
 * Testcase for ForViewHelper
 */
class ForViewHelperTest extends ViewHelperBaseTestcase {

	public function setUp() {
		parent::setUp();


		$this->arguments['reverse'] = NULL;
		$this->arguments['key'] = '';
		$this->arguments['iteration'] = NULL;
	}

	/**
	 * @test
	 */
	public function renderExecutesTheLoopCorrectly() {
		$viewHelper = new ForViewHelper();

		$viewHelperNode = new ConstraintSyntaxTreeNode($this->templateVariableContainer);
		$this->arguments['each'] = array(0, 1, 2, 3);
		$this->arguments['as'] = 'innerVariable';

		$this->injectDependenciesIntoViewHelper($viewHelper);
		$viewHelper->setViewHelperNode($viewHelperNode);
		$viewHelper->initializeArgumentsAndRender();

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
	 */
	public function renderPreservesKeys() {
		$viewHelper = new ForViewHelper();

		$viewHelperNode = new ConstraintSyntaxTreeNode($this->templateVariableContainer);

		$this->arguments['each'] = array('key1' => 'value1', 'key2' => 'value2');
		$this->arguments['as'] = 'innerVariable';
		$this->arguments['key'] = 'someKey';

		$this->injectDependenciesIntoViewHelper($viewHelper);
		$viewHelper->setViewHelperNode($viewHelperNode);
		$viewHelper->initializeArgumentsAndRender();

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
	 */
	public function renderReturnsEmptyStringIfObjectIsNull() {
		$viewHelper = new ForViewHelper();

		$this->arguments['each'] = NULL;
		$this->arguments['as'] = 'foo';

		$this->injectDependenciesIntoViewHelper($viewHelper);

		$this->assertEquals('', $viewHelper->initializeArgumentsAndRender());
	}

	/**
	 * @test
	 */
	public function renderReturnsEmptyStringIfObjectIsEmptyArray() {
		$viewHelper = new ForViewHelper();

		$this->arguments['each'] = array();
		$this->arguments['as'] = 'foo';

		$this->injectDependenciesIntoViewHelper($viewHelper);

		$this->assertEquals('', $viewHelper->initializeArgumentsAndRender());
	}

	/**
	 * @test
	 */
	public function renderIteratesElementsInReverseOrderIfReverseIsTrue() {
		$viewHelper = new ForViewHelper();

		$viewHelperNode = new ConstraintSyntaxTreeNode($this->templateVariableContainer);

		$this->arguments['each'] = array(0, 1, 2, 3);
		$this->arguments['as'] = 'innerVariable';
		$this->arguments['reverse'] = TRUE;

		$this->injectDependenciesIntoViewHelper($viewHelper);
		$viewHelper->setViewHelperNode($viewHelperNode);
		$viewHelper->initializeArgumentsAndRender();

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
	 */
	public function renderIteratesElementsInReverseOrderIfReverseIsTrueAndObjectIsIterator() {
		$viewHelper = new ForViewHelper();

		$viewHelperNode = new ConstraintSyntaxTreeNode($this->templateVariableContainer);

		$this->arguments['each'] = new \ArrayIterator(array(0, 1, 2, 3));
		$this->arguments['as'] = 'innerVariable';
		$this->arguments['reverse'] = TRUE;

		$this->injectDependenciesIntoViewHelper($viewHelper);
		$viewHelper->setViewHelperNode($viewHelperNode);
		$viewHelper->initializeArgumentsAndRender();

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
	 */
	public function renderPreservesKeysIfReverseIsTrue() {
		$viewHelper = new ForViewHelper();

		$viewHelperNode = new ConstraintSyntaxTreeNode($this->templateVariableContainer);

		$this->arguments['each'] = array('key1' => 'value1', 'key2' => 'value2');
		$this->arguments['as'] = 'innerVariable';
		$this->arguments['key'] = 'someKey';
		$this->arguments['reverse'] = TRUE;

		$this->injectDependenciesIntoViewHelper($viewHelper);
		$viewHelper->setViewHelperNode($viewHelperNode);
		$viewHelper->initializeArgumentsAndRender();

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
	 */
	public function keyContainsNumericalIndexIfTheGivenArrayDoesNotHaveAKey() {
		$viewHelper = new ForViewHelper();

		$viewHelperNode = new ConstraintSyntaxTreeNode($this->templateVariableContainer);

		$this->arguments['each'] = array('foo', 'bar', 'baz');
		$this->arguments['as'] = 'innerVariable';
		$this->arguments['key'] = 'someKey';

		$this->injectDependenciesIntoViewHelper($viewHelper);
		$viewHelper->setViewHelperNode($viewHelperNode);
		$viewHelper->initializeArgumentsAndRender();

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
	 */
	public function keyContainsNumericalIndexInAscendingOrderEvenIfReverseIsTrueIfTheGivenArrayDoesNotHaveAKey() {
		$viewHelper = new ForViewHelper();

		$viewHelperNode = new ConstraintSyntaxTreeNode($this->templateVariableContainer);

		$this->arguments['each'] = array('foo', 'bar', 'baz');
		$this->arguments['as'] = 'innerVariable';
		$this->arguments['key'] = 'someKey';
		$this->arguments['reverse'] = TRUE;

		$this->injectDependenciesIntoViewHelper($viewHelper);
		$viewHelper->setViewHelperNode($viewHelperNode);
		$viewHelper->initializeArgumentsAndRender();

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
	 * @expectedException InvalidArgumentException
	 */
	public function renderThrowsExceptionWhenPassingObjectsToEachThatAreNotTraversable() {
		$viewHelper = new ForViewHelper();
		$object = new \stdClass();

		$this->arguments['each'] = $object;
		$this->arguments['as'] = 'innerVariable';
		$this->arguments['key'] = 'someKey';
		$this->arguments['reverse'] = TRUE;

		$this->injectDependenciesIntoViewHelper($viewHelper);
		$viewHelper->initializeArgumentsAndRender();
	}


	/**
	 * @test
	 */
	public function renderIteratesThroughElementsOfTraversableObjects() {
		$viewHelper = new ForViewHelper();

		$viewHelperNode = new ConstraintSyntaxTreeNode($this->templateVariableContainer);

		$this->arguments['each'] = new \ArrayObject(array('key1' => 'value1', 'key2' => 'value2'));
		$this->arguments['as'] = 'innerVariable';

		$this->injectDependenciesIntoViewHelper($viewHelper);
		$viewHelper->setViewHelperNode($viewHelperNode);
		$viewHelper->initializeArgumentsAndRender();

		$expectedCallProtocol = array(
			array('innerVariable' => 'value1'),
			array('innerVariable' => 'value2')
		);
		$this->assertEquals($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs -> The for loop does not work as it should!');
	}

	/**
	 * @test
	 */
	public function renderPreservesKeyWhenIteratingThroughElementsOfObjectsThatImplementIteratorInterface() {
		$viewHelper = new ForViewHelper();

		$viewHelperNode = new ConstraintSyntaxTreeNode($this->templateVariableContainer);

		$this->arguments['each'] = new \ArrayIterator(array('key1' => 'value1', 'key2' => 'value2'));
		$this->arguments['as'] = 'innerVariable';
		$this->arguments['key'] = 'someKey';

		$this->injectDependenciesIntoViewHelper($viewHelper);
		$viewHelper->setViewHelperNode($viewHelperNode);
		$viewHelper->initializeArgumentsAndRender();

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
	 */
	public function keyContainsTheNumericalIndexWhenIteratingThroughElementsOfObjectsOfTyeSplObjectStorage() {
		$viewHelper = new ForViewHelper();

		$viewHelperNode = new ConstraintSyntaxTreeNode($this->templateVariableContainer);

		$splObjectStorageObject = new \SplObjectStorage();
		$object1 = new \stdClass();
		$splObjectStorageObject->attach($object1);
		$object2 = new \stdClass();
		$splObjectStorageObject->attach($object2, 'foo');
		$object3 = new \stdClass();
		$splObjectStorageObject->offsetSet($object3, 'bar');

		$this->arguments['each'] = $splObjectStorageObject;
		$this->arguments['as'] = 'innerVariable';
		$this->arguments['key'] = 'someKey';

		$this->injectDependenciesIntoViewHelper($viewHelper);
		$viewHelper->setViewHelperNode($viewHelperNode);
		$viewHelper->initializeArgumentsAndRender();

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

	/**
	 * @test
	 */
	public function iterationDataIsAddedToTemplateVariableContainerIfIterationArgumentIsSet() {
		$viewHelper = new ForViewHelper();

		$viewHelperNode = new ConstraintSyntaxTreeNode($this->templateVariableContainer);

		$this->arguments['each'] = array('foo' => 'bar', 'Flow' => 'Fluid', 'TYPO3' => 'rocks');
		$this->arguments['as'] = 'innerVariable';
		$this->arguments['iteration'] = 'iteration';

		$this->injectDependenciesIntoViewHelper($viewHelper);
		$viewHelper->setViewHelperNode($viewHelperNode);
		$viewHelper->initializeArgumentsAndRender();

		$expectedCallProtocol = array(
			array(
				'innerVariable' => 'bar',
				'iteration' => array(
					'index' => 0,
					'cycle' => 1,
					'total' => 3,
					'isFirst' => TRUE,
					'isLast' => FALSE,
					'isEven' => FALSE,
					'isOdd' => TRUE
				)
			),
			array(
				'innerVariable' => 'Fluid',
				'iteration' => array(
					'index' => 1,
					'cycle' => 2,
					'total' => 3,
					'isFirst' => FALSE,
					'isLast' => FALSE,
					'isEven' => TRUE,
					'isOdd' => FALSE
				)
			),
			array(
				'innerVariable' => 'rocks',
				'iteration' => array(
					'index' => 2,
					'cycle' => 3,
					'total' => 3,
					'isFirst' => FALSE,
					'isLast' => TRUE,
					'isEven' => FALSE,
					'isOdd' => TRUE
				)
			)
		);
		$this->assertSame($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs -> The for loop does not work as it should!');
	}

	/**
	 * @test
	 */
	public function renderThrowsExceptionOnInvalidObject() {
		$viewHelper = new ForViewHelper();
		$this->arguments['each'] = new \DateTime('now');
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$this->setExpectedException(Exception::class);
		$viewHelper->render();
	}

}
