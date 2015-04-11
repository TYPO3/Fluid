<?php
namespace TYPO3\Fluid\Tests\Unit\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3\Fluid\Core\Rendering\RenderingContext;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3\Fluid\Tests\Unit\Core\Fixtures\TestViewHelper;
use TYPO3\Fluid\Tests\Unit\Core\Fixtures\TestViewHelper2;
use TYPO3\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithToString;
use TYPO3\Fluid\Tests\UnitTestCase;

/**
 * Testcase for AbstractViewHelper
 *
 */
class AbstractViewHelperTest extends UnitTestCase {

	/**
	 * @var array
	 */
	protected $fixtureMethodParameters = array(
		'param1' => array(
			'position' => 0,
			'optional' => FALSE,
			'type' => 'integer',
			'defaultValue' => NULL
		),
		'param2' => array(
			'position' => 1,
			'optional' => FALSE,
			'type' => 'array',
			'array' => TRUE,
			'defaultValue' => NULL
		),
		'param3' => array(
			'position' => 2,
			'optional' => TRUE,
			'type' => 'string',
			'array' => FALSE,
			'defaultValue' => 'default'
		),
	);

	/**
	 * @var array
	 */
	protected $fixtureMethodTags = array(
		'param' => array(
			'integer $param1 P1 Stuff',
			'array $param2 P2 Stuff',
			'string $param3 P3 Stuff'
		)
	);

	/**
	 * @test
	 */
	public function argumentsCanBeRegistered() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render'), array(), '', FALSE);

		$name = 'This is a name';
		$description = 'Example desc';
		$type = 'string';
		$isRequired = TRUE;
		$expected = new ArgumentDefinition($name, $type, $description, $isRequired);

		$viewHelper->_call('registerArgument', $name, $type, $description, $isRequired);
		$this->assertEquals(array($name => $expected), $viewHelper->prepareArguments(), 'Argument definitions not returned correctly.');
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception
	 */
	public function registeringTheSameArgumentNameAgainThrowsException() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render'), array(), '', FALSE);

		$name = 'shortName';
		$description = 'Example desc';
		$type = 'string';
		$isRequired = TRUE;

		$viewHelper->_call('registerArgument', $name, $type, $description, $isRequired);
		$viewHelper->_call('registerArgument', $name, 'integer', $description, $isRequired);
	}

	/**
	 * @test
	 */
	public function overrideArgumentOverwritesExistingArgumentDefinition() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render'), array(), '', FALSE);

		$name = 'argumentName';
		$description = 'argument description';
		$overriddenDescription = 'overwritten argument description';
		$type = 'string';
		$overriddenType = 'integer';
		$isRequired = TRUE;
		$expected = new ArgumentDefinition($name, $overriddenType, $overriddenDescription, $isRequired);

		$viewHelper->_call('registerArgument', $name, $type, $description, $isRequired);
		$viewHelper->_call('overrideArgument', $name, $overriddenType, $overriddenDescription, $isRequired);
		$this->assertEquals($viewHelper->prepareArguments(), array($name => $expected), 'Argument definitions not returned correctly. The original ArgumentDefinition could not be overridden.');
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception
	 */
	public function overrideArgumentThrowsExceptionWhenTryingToOverwriteAnNonexistingArgument() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render'), array(), '', FALSE);

		$viewHelper->_call('overrideArgument', 'argumentName', 'string', 'description', TRUE);
	}

	/**
	 * @test
	 */
	public function prepareArgumentsCallsInitializeArguments() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'initializeArguments'), array(), '', FALSE);

		$viewHelper->expects($this->once())->method('initializeArguments');

		$viewHelper->prepareArguments();
	}

	/**
	 * @test
	 */
	public function validateArgumentsCallsPrepareArguments() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'prepareArguments'), array(), '', FALSE);

		$viewHelper->expects($this->once())->method('prepareArguments')->will($this->returnValue(array()));

		$viewHelper->validateArguments();
	}

	/**
	 * @test
	 */
	public function validateArgumentsAcceptsAllObjectsImplemtingArrayAccessAsAnArray() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'prepareArguments'), array(), '', FALSE);

		$viewHelper->setArguments(array('test' => new \ArrayObject));
		$viewHelper->expects($this->once())->method('prepareArguments')->will($this->returnValue(array('test' => new ArgumentDefinition('test', 'array', FALSE, 'documentation'))));
		$viewHelper->validateArguments();
	}

	/**
	 * @test
	 */
	public function validateArgumentsCallsTheRightValidators() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'prepareArguments'), array(), '', FALSE);

		$viewHelper->setArguments(array('test' => 'Value of argument'));

		$viewHelper->expects($this->once())->method('prepareArguments')->will($this->returnValue(array(
			'test' => new ArgumentDefinition('test', 'string', FALSE, 'documentation')
		)));

		$viewHelper->validateArguments();
	}

	/**
	 * @test
	 * @expectedException \InvalidArgumentException
	 */
	public function validateArgumentsCallsTheRightValidatorsAndThrowsExceptionIfValidationIsWrong() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'prepareArguments'), array(), '', FALSE);

		$viewHelper->setArguments(array('test' => 'test'));

		$viewHelper->expects($this->once())->method('prepareArguments')->will($this->returnValue(array(
			'test' => new ArgumentDefinition('test', 'stdClass', FALSE, 'documentation')
		)));

		$viewHelper->validateArguments();
	}

	/**
	 * @test
	 */
	public function initializeArgumentsAndRenderCallsTheCorrectSequenceOfMethods() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('validateArguments', 'initialize', 'callRenderMethod'));
		$viewHelper->expects($this->at(0))->method('validateArguments');
		$viewHelper->expects($this->at(1))->method('initialize');
		$viewHelper->expects($this->at(2))->method('callRenderMethod')->will($this->returnValue('Output'));

		$expectedOutput = 'Output';
		$actualOutput = $viewHelper->initializeArgumentsAndRender(array('argument1' => 'value1'));
		$this->assertEquals($expectedOutput, $actualOutput);
	}

	/**
	 * @test
	 */
	public function setRenderingContextShouldSetInnerVariables() {
		$templateVariableContainer = $this->getMock('TYPO3\Fluid\Core\Variables\StandardVariableProvider');
		$viewHelperVariableContainer = $this->getMock('TYPO3\Fluid\Core\ViewHelper\ViewHelperVariableContainer');

		$renderingContext = new RenderingContext();
		$renderingContext->setVariableProvider($templateVariableContainer);
		$renderingContext->injectViewHelperVariableContainer($viewHelperVariableContainer);

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'prepareArguments'), array(), '', FALSE);

		$viewHelper->setRenderingContext($renderingContext);

		$this->assertSame($viewHelper->_get('templateVariableContainer'), $templateVariableContainer);
		$this->assertSame($viewHelper->_get('viewHelperVariableContainer'), $viewHelperVariableContainer);
	}

	/**
	 * @test
	 */
	public function testRenderChildrenCallsRenderChildrenClosureIfSet() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('dummy'), array(), '', FALSE);
		$viewHelper->setRenderChildrenClosure(function() { return 'foobar'; });
		$result = $viewHelper->renderChildren();
		$this->assertEquals('foobar', $result);
	}

	/**
	 * @test
	 * @dataProvider getValidateArgumentsTestValues
	 * @param ArgumentDefinition $argument
	 * @param mixed $value
	 */
	public function testValidateArguments(ArgumentDefinition $argument, $value) {
		$viewHelper = $this->getAccessibleMock(
			'TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper',
			array('hasArgument', 'prepareArguments'),
			array(), '', FALSE
		);
		$viewHelper->expects($this->once())->method('prepareArguments')->willReturn(
			array($argument->getName() => $argument, 'second' => $argument)
		);
		$viewHelper->setArguments(array($argument->getName() => $value, 'second' => $value));
		$viewHelper->expects($this->at(1))->method('hasArgument')->with($argument->getName())->willReturn(TRUE);
		$viewHelper->expects($this->at(2))->method('hasArgument')->with('second')->willReturn(TRUE);
		$viewHelper->validateArguments();
	}

	/**
	 * @return array
	 */
	public function getValidateArgumentsTestValues() {
		return array(
			array(new ArgumentDefinition('test', 'boolean', '', TRUE, FALSE), FALSE),
			array(new ArgumentDefinition('test', 'boolean', '', TRUE), TRUE),
			array(new ArgumentDefinition('test', 'string', '', TRUE), 'foobar'),
			array(new ArgumentDefinition('test', 'string', '', TRUE), new UserWithToString('foobar')),
			array(new ArgumentDefinition('test', 'array', '', TRUE), array('foobar')),
			array(new ArgumentDefinition('test', 'mixed', '', TRUE), new \DateTime('now')),
		);
	}

	/**
	 * @test
	 * @dataProvider getValidateArgumentsErrorsTestValues
	 * @param ArgumentDefinition $argument
	 * @param mixed $value
	 */
	public function testValidateArgumentsErrors(ArgumentDefinition $argument, $value) {
		$viewHelper = $this->getAccessibleMock(
			'TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper',
			array('hasArgument', 'prepareArguments'),
			array(), '', FALSE
		);
		$viewHelper->expects($this->once())->method('prepareArguments')->willReturn(array($argument->getName() => $argument));
		$viewHelper->expects($this->once())->method('hasArgument')->with($argument->getName())->willReturn(TRUE);
		$viewHelper->setArguments(array($argument->getName() => $value));
		$this->setExpectedException('InvalidArgumentException');
		$viewHelper->validateArguments();
	}

	/**
	 * @return array
	 */
	public function getValidateArgumentsErrorsTestValues() {
		return array(
			array(new ArgumentDefinition('test', 'boolean', '', TRUE), array('bad')),
			array(new ArgumentDefinition('test', 'string', '', TRUE), new \ArrayIterator(array('bar'))),
			array(new ArgumentDefinition('test', 'DateTime', '', TRUE), new \ArrayIterator(array('bar'))),
			array(new ArgumentDefinition('test', 'DateTime', '', TRUE), 'test'),
			array(new ArgumentDefinition('test', 'integer', '', TRUE), new \ArrayIterator(array('bar'))),
		);
	}

	/**
	 * @test
	 */
	public function testRenderCallsAndReturnsRenderChildren() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('renderChildren'), array(), '', FALSE);
		$viewHelper->expects($this->once())->method('renderChildren')->willReturn('foobar');
		$result = $viewHelper->render();
		$this->assertEquals('foobar', $result);
	}

	/**
	 * @test
	 */
	public function testCompileReturnsAndAssignsExpectedPhpCode() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('dummy'), array(), '', FALSE);
		$node = new TextNode('test');
		$init = '';
		$compiler = new TemplateCompiler();
		$result = $viewHelper->compile('foobar', 'baz', $init, $node, $compiler);
		$this->assertEquals('', $init);
		$this->assertEquals(get_class($viewHelper) . '::renderStatic(foobar, baz, $renderingContext)', $result);
	}

	/**
	 * @test
	 */
	public function testRenderStaticReturnsNull() {
		$result = AbstractViewHelper::renderStatic(array(), function() { return 'test'; }, new RenderingContext());
		$this->assertNull($result);
	}

}
