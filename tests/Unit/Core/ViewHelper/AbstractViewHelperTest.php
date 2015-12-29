<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithToString;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

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
		$viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, NULL, array(), '', FALSE);

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
	 * @expectedException Exception
	 */
	public function registeringTheSameArgumentNameAgainThrowsException() {
		$viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, NULL, array(), '', FALSE);

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
		$viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, NULL, array(), '', FALSE);

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
	 * @expectedException Exception
	 */
	public function overrideArgumentThrowsExceptionWhenTryingToOverwriteAnNonexistingArgument() {
		$viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, NULL, array(), '', FALSE);

		$viewHelper->_call('overrideArgument', 'argumentName', 'string', 'description', TRUE);
	}

	/**
	 * @test
	 */
	public function prepareArgumentsCallsInitializeArguments() {
		$viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, array('initializeArguments'), array(), '', FALSE);

		$viewHelper->expects($this->once())->method('initializeArguments');

		$viewHelper->prepareArguments();
	}

	/**
	 * @test
	 */
	public function validateArgumentsCallsPrepareArguments() {
		$viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, array('prepareArguments'), array(), '', FALSE);

		$viewHelper->expects($this->once())->method('prepareArguments')->will($this->returnValue(array()));

		$viewHelper->validateArguments();
	}

	/**
	 * @test
	 */
	public function validateArgumentsAcceptsAllObjectsImplemtingArrayAccessAsAnArray() {
		$viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, array('prepareArguments'), array(), '', FALSE);

		$viewHelper->setArguments(array('test' => new \ArrayObject));
		$viewHelper->expects($this->once())->method('prepareArguments')->will($this->returnValue(array('test' => new ArgumentDefinition('test', 'array', FALSE, 'documentation'))));
		$viewHelper->validateArguments();
	}

	/**
	 * @test
	 */
	public function validateArgumentsCallsTheRightValidators() {
		$viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, array('prepareArguments'), array(), '', FALSE);

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
		$viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, array('prepareArguments'), array(), '', FALSE);

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
		$viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, array('validateArguments', 'initialize', 'callRenderMethod'));
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
		$templateVariableContainer = $this->getMock(StandardVariableProvider::class);
		$viewHelperVariableContainer = $this->getMock(ViewHelperVariableContainer::class);

		$view = new TemplateView();
		$renderingContext = new RenderingContext($view);
		$renderingContext->setVariableProvider($templateVariableContainer);
		$renderingContext->setViewHelperVariableContainer($viewHelperVariableContainer);

		$viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, array('prepareArguments'), array(), '', FALSE);

		$viewHelper->setRenderingContext($renderingContext);

		$this->assertSame($viewHelper->_get('templateVariableContainer'), $templateVariableContainer);
		$this->assertSame($viewHelper->_get('viewHelperVariableContainer'), $viewHelperVariableContainer);
	}

	/**
	 * @test
	 */
	public function testRenderChildrenCallsRenderChildrenClosureIfSet() {
		$viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, NULL, array(), '', FALSE);
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
			AbstractViewHelper::class,
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
			AbstractViewHelper::class,
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
			array(new ArgumentDefinition('test', 'object', '', TRUE), 'test'),
		);
	}

	/**
	 * @test
	 */
	public function testValidateAdditionalArgumentsThrowsExceptionIfNotEmpty() {
		$viewHelper = $this->getAccessibleMock(
			AbstractViewHelper::class,
			array('dummy'),
			array(), '', FALSE
		);
		$this->setExpectedException(Exception::class);
		$viewHelper->validateAdditionalArguments(array('foo' => 'bar'));
	}

	/**
	 * @test
	 */
	public function testCompileReturnsAndAssignsExpectedPhpCode() {
		$view = new TemplateView();
		$context = new RenderingContext($view);
		$viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, array('dummy'), array(), '', FALSE);
		$node = new ViewHelperNode($context, 'f', 'comment', array(), new ParsingState());
		$init = '';
		$compiler = new TemplateCompiler();
		$result = $viewHelper->compile('foobar', 'baz', $init, $node, $compiler);
		$this->assertEmpty($init);
		$this->assertEquals(get_class($viewHelper) . '::renderStatic(foobar, baz, $renderingContext)', $result);
	}

	/**
	 * @test
	 */
	public function testDefaultResetStateMethodDoesNothing() {
		$viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, array('dummy'), array(), '', FALSE);
		$this->assertNull($viewHelper->resetState());
	}

}
