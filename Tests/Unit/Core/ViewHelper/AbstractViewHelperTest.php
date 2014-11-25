<?php
namespace TYPO3\Fluid\Tests\Unit\Core\ViewHelper;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

require_once(__DIR__ . '/../Fixtures/TestViewHelper.php');
require_once(__DIR__ . '/../Fixtures/TestViewHelper2.php');

/**
 * Testcase for AbstractViewHelper
 *
 */
class AbstractViewHelperTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @var \TYPO3\Flow\Reflection\ReflectionService
	 */
	protected $mockReflectionService;

	/**
	 * @var \TYPO3\Flow\Object\ObjectManagerInterface
	 */
	protected $mockObjectManager;

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

	public function setUp() {
		$this->mockReflectionService = $this->getMock('TYPO3\Flow\Reflection\ReflectionService', array(), array(), '', FALSE);
		$this->mockObjectManager = $this->getMock('TYPO3\Flow\Object\ObjectManagerInterface');
		$this->mockObjectManager->expects($this->any())->method('get')->with('TYPO3\Flow\Reflection\ReflectionService')->will($this->returnValue($this->mockReflectionService));
	}

	/**
	 * @test
	 */
	public function argumentsCanBeRegistered() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render'), array(), '', FALSE);
		$viewHelper->injectObjectManager($this->mockObjectManager);

		$name = 'This is a name';
		$description = 'Example desc';
		$type = 'string';
		$isRequired = TRUE;
		$expected = new \TYPO3\Fluid\Core\ViewHelper\ArgumentDefinition($name, $type, $description, $isRequired);

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
		$viewHelper->injectObjectManager($this->mockObjectManager);

		$name = 'argumentName';
		$description = 'argument description';
		$overriddenDescription = 'overwritten argument description';
		$type = 'string';
		$overriddenType = 'integer';
		$isRequired = TRUE;
		$expected = new \TYPO3\Fluid\Core\ViewHelper\ArgumentDefinition($name, $overriddenType, $overriddenDescription, $isRequired);

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
		$viewHelper->injectObjectManager($this->mockObjectManager);

		$viewHelper->_call('overrideArgument', 'argumentName', 'string', 'description', TRUE);
	}

	/**
	 * @test
	 */
	public function prepareArgumentsCallsInitializeArguments() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'initializeArguments'), array(), '', FALSE);
		$viewHelper->injectObjectManager($this->mockObjectManager);

		$viewHelper->expects($this->once())->method('initializeArguments');

		$viewHelper->prepareArguments();
	}

	/**
	 * @test
	 */
	public function prepareArgumentsRegistersAnnotationBasedArgumentsWithDescriptionIfDebugModeIsEnabled() {

		\TYPO3\Fluid\Fluid::$debugMode = TRUE;

		$dataCacheMock = $this->getMock('TYPO3\Flow\Cache\Frontend\VariableFrontend', array(), array(), '', FALSE);
		$dataCacheMock->expects($this->any())->method('has')->will($this->returnValue(TRUE));
		$dataCacheMock->expects($this->any())->method('get')->will($this->returnValue(array()));

		$viewHelper = new \TYPO3\Fluid\Core\Fixtures\TestViewHelper();

		$this->mockReflectionService->expects($this->once())->method('getMethodParameters')->with('TYPO3\Fluid\Core\Fixtures\TestViewHelper', 'render')->will($this->returnValue($this->fixtureMethodParameters));
		$this->mockReflectionService->expects($this->once())->method('getMethodTagsValues')->with('TYPO3\Fluid\Core\Fixtures\TestViewHelper', 'render')->will($this->returnValue($this->fixtureMethodTags));
		$viewHelper->injectObjectManager($this->mockObjectManager);

		$expected = array(
			'param1' => new \TYPO3\Fluid\Core\ViewHelper\ArgumentDefinition('param1', 'integer', 'P1 Stuff', TRUE, NULL, TRUE),
			'param2' => new \TYPO3\Fluid\Core\ViewHelper\ArgumentDefinition('param2', 'array', 'P2 Stuff', TRUE, NULL, TRUE),
			'param3' => new \TYPO3\Fluid\Core\ViewHelper\ArgumentDefinition('param3', 'string', 'P3 Stuff', FALSE, 'default', TRUE)
		);

		$this->assertEquals($expected, $viewHelper->prepareArguments(), 'Annotation based arguments were not registered.');

		\TYPO3\Fluid\Fluid::$debugMode = FALSE;
	}

	/**
	 * @test
	 */
	public function prepareArgumentsRegistersAnnotationBasedArgumentsWithoutDescriptionIfDebugModeIsDisabled() {

		\TYPO3\Fluid\Fluid::$debugMode = FALSE;

		$dataCacheMock = $this->getMock('TYPO3\Flow\Cache\Frontend\VariableFrontend', array(), array(), '', FALSE);
		$dataCacheMock->expects($this->any())->method('has')->will($this->returnValue(TRUE));
		$dataCacheMock->expects($this->any())->method('get')->will($this->returnValue(array()));

		$viewHelper = new \TYPO3\Fluid\Core\Fixtures\TestViewHelper2();

		$this->mockReflectionService->expects($this->once())->method('getMethodParameters')->with('TYPO3\Fluid\Core\Fixtures\TestViewHelper2', 'render')->will($this->returnValue($this->fixtureMethodParameters));
		$this->mockReflectionService->expects($this->never())->method('getMethodTagsValues');
		$viewHelper->injectObjectManager($this->mockObjectManager);

		$expected = array(
			'param1' => new \TYPO3\Fluid\Core\ViewHelper\ArgumentDefinition('param1', 'integer', '', TRUE, NULL, TRUE),
			'param2' => new \TYPO3\Fluid\Core\ViewHelper\ArgumentDefinition('param2', 'array', '', TRUE, NULL, TRUE),
			'param3' => new \TYPO3\Fluid\Core\ViewHelper\ArgumentDefinition('param3', 'string', '', FALSE, 'default', TRUE),
		);

		$this->assertEquals($expected, $viewHelper->prepareArguments(), 'Annotation based arguments were not registered.');
	}

	/**
	 * @test
	 */
	public function validateArgumentsCallsPrepareArguments() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'prepareArguments'), array(), '', FALSE);
		$viewHelper->injectObjectManager($this->mockObjectManager);

		$viewHelper->expects($this->once())->method('prepareArguments')->will($this->returnValue(array()));

		$viewHelper->validateArguments();
	}

	/**
	 * @test
	 */
	public function validateArgumentsAcceptsAllObjectsImplemtingArrayAccessAsAnArray() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'prepareArguments'), array(), '', FALSE);

		$viewHelper->setArguments(array('test' => new \ArrayObject));
		$viewHelper->expects($this->once())->method('prepareArguments')->will($this->returnValue(array('test' => new \TYPO3\Fluid\Core\ViewHelper\ArgumentDefinition('test', 'array', FALSE, 'documentation'))));
		$viewHelper->validateArguments();
	}

	/**
	 * @test
	 */
	public function validateArgumentsCallsTheRightValidators() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'prepareArguments'), array(), '', FALSE);
		$viewHelper->injectObjectManager($this->mockObjectManager);

		$viewHelper->setArguments(array('test' => 'Value of argument'));

		$viewHelper->expects($this->once())->method('prepareArguments')->will($this->returnValue(array(
			'test' => new \TYPO3\Fluid\Core\ViewHelper\ArgumentDefinition('test', 'string', FALSE, 'documentation')
		)));

		$viewHelper->validateArguments();
	}

	/**
	 * @test
	 * @expectedException \InvalidArgumentException
	 */
	public function validateArgumentsCallsTheRightValidatorsAndThrowsExceptionIfValidationIsWrong() {
		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'prepareArguments'), array(), '', FALSE);
		$viewHelper->injectObjectManager($this->mockObjectManager);

		$viewHelper->setArguments(array('test' => 'test'));

		$viewHelper->expects($this->once())->method('prepareArguments')->will($this->returnValue(array(
			'test' => new \TYPO3\Fluid\Core\ViewHelper\ArgumentDefinition('test', 'stdClass', FALSE, 'documentation')
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
		$templateVariableContainer = $this->getMock('TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer');
		$viewHelperVariableContainer = $this->getMock('TYPO3\Fluid\Core\ViewHelper\ViewHelperVariableContainer');
		$controllerContext = $this->getMock('TYPO3\Flow\Mvc\Controller\ControllerContext', array(), array(), '', FALSE);

		$renderingContext = new \TYPO3\Fluid\Core\Rendering\RenderingContext();
		$renderingContext->injectTemplateVariableContainer($templateVariableContainer);
		$renderingContext->injectViewHelperVariableContainer($viewHelperVariableContainer);
		$renderingContext->setControllerContext($controllerContext);

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'prepareArguments'), array(), '', FALSE);

		$viewHelper->setRenderingContext($renderingContext);

		$this->assertSame($viewHelper->_get('templateVariableContainer'), $templateVariableContainer);
		$this->assertSame($viewHelper->_get('viewHelperVariableContainer'), $viewHelperVariableContainer);
		$this->assertSame($viewHelper->_get('controllerContext'), $controllerContext);
	}
}
