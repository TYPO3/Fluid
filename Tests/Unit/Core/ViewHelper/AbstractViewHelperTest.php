<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\ViewHelper;

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

include_once(__DIR__ . '/../Fixtures/TestViewHelper.php');

/**
 * Testcase for AbstractViewHelper
 *
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class AbstractViewHelperTest extends \F3\Testing\BaseTestCase {
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function argumentsCanBeRegistered() {
		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\ViewHelper\AbstractViewHelper'), array('render'), array(), '', FALSE);
		$viewHelper->injectReflectionService($this->objectManager->getObject('F3\FLOW3\Reflection\Service'));

		$name = "This is a name";
		$description = "Example desc";
		$type = "string";
		$isRequired = TRUE;
		$expected = new \F3\Fluid\Core\ViewHelper\ArgumentDefinition($name, $type, $description, $isRequired);

		$viewHelper->_call('registerArgument', $name, $type, $isRequired, $description);
		$this->assertEquals($viewHelper->prepareArguments(), array($name => $expected), 'Argument definitions not returned correctly.');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @expectedException F3\Fluid\Core\ViewHelper\Exception
	 */
	public function registeringTheSameArgumentNameAgainThrowsException() {
		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\ViewHelper\AbstractViewHelper'), array('render'), array(), '', FALSE);

		$name = "shortName";
		$description = "Example desc";
		$type = "string";
		$isRequired = TRUE;

		$viewHelper->_call('registerArgument', $name, $type, $isRequired, $description);
		$viewHelper->_call('registerArgument', $name, "integer", $isRequired, $description);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function prepareArgumentsCallsInitializeArguments() {
		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\ViewHelper\AbstractViewHelper'), array('render', 'initializeArguments'), array(), '', FALSE);
		$viewHelper->injectReflectionService($this->objectManager->getObject('F3\FLOW3\Reflection\Service'));

		$viewHelper->setArguments(new \F3\Fluid\Core\ViewHelper\Arguments(array()));
		$viewHelper->expects($this->once())->method('initializeArguments');

		$viewHelper->prepareArguments();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function prepareArgumentsRegistersAnnotationBasedArgumentsWithDescriptionIfDebugModeIsEnabled() {

		\F3\Fluid\Fluid::$debugMode = TRUE;

		$availableClassNames = array(
			'F3\Fluid\Core\Fixtures\TestViewHelper',
		);
		$reflectionService = new \F3\FLOW3\Reflection\Service();
		$reflectionService->setCache($this->getMock('F3\FLOW3\Cache\Frontend\VariableFrontend', array(), array(), '', FALSE));
		$reflectionService->initialize($availableClassNames);

		$viewHelper = new \F3\Fluid\Core\Fixtures\TestViewHelper();
		$viewHelper->injectReflectionService($reflectionService);

		$expected = array(
			'param1' => new \F3\Fluid\Core\ViewHelper\ArgumentDefinition('param1', 'integer', 'P1 Stuff', TRUE, null, TRUE),
			'param2' => new \F3\Fluid\Core\ViewHelper\ArgumentDefinition('param2', 'array', 'P2 Stuff', TRUE, null, TRUE),
			'param3' => new \F3\Fluid\Core\ViewHelper\ArgumentDefinition('param3', 'string', 'P3 Stuff', FALSE, 'default', TRUE),
		);

		$this->assertEquals($expected, $viewHelper->prepareArguments(), 'Annotation based arguments were not registered.');

		\F3\Fluid\Fluid::$debugMode = FALSE;
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function prepareArgumentsRegistersAnnotationBasedArgumentsWithoutDescriptionIfDebugModeIsDisabled() {

		\F3\Fluid\Fluid::$debugMode = FALSE;

		$availableClassNames = array(
			'F3\Fluid\Core\Fixtures\TestViewHelper',
		);
		$reflectionService = new \F3\FLOW3\Reflection\Service();
		$reflectionService->setCache($this->getMock('F3\FLOW3\Cache\Frontend\VariableFrontend', array(), array(), '', FALSE));
		$reflectionService->initialize($availableClassNames);

		$viewHelper = new \F3\Fluid\Core\Fixtures\TestViewHelper();
		$viewHelper->injectReflectionService($reflectionService);

		$expected = array(
			'param1' => new \F3\Fluid\Core\ViewHelper\ArgumentDefinition('param1', 'integer', '', TRUE, null, TRUE),
			'param2' => new \F3\Fluid\Core\ViewHelper\ArgumentDefinition('param2', 'array', '', TRUE, null, TRUE),
			'param3' => new \F3\Fluid\Core\ViewHelper\ArgumentDefinition('param3', 'string', '', FALSE, 'default', TRUE),
		);

		$this->assertEquals($expected, $viewHelper->prepareArguments(), 'Annotation based arguments were not registered.');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function validateArgumentsCallsPrepareArguments() {
		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\ViewHelper\AbstractViewHelper'), array('render', 'prepareArguments'), array(), '', FALSE);
		$viewHelper->injectReflectionService($this->objectManager->getObject('F3\FLOW3\Reflection\Service'));

		$viewHelper->setArguments(new \F3\Fluid\Core\ViewHelper\Arguments(array()));
		$viewHelper->expects($this->once())->method('prepareArguments')->will($this->returnValue(array()));

		$viewHelper->validateArguments();
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function validateArgumentsAcceptsAllObjectsImplemtingArrayAccessAsAnArray() {
		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\ViewHelper\AbstractViewHelper'), array('render', 'prepareArguments'), array(), '', FALSE);

		$viewHelper->setArguments(new \F3\Fluid\Core\ViewHelper\Arguments(array('test' => new \ArrayObject)));
		$viewHelper->expects($this->once())->method('prepareArguments')->will($this->returnValue(array('test' => new \F3\Fluid\Core\ViewHelper\ArgumentDefinition('test', 'array', FALSE, 'documentation'))));
		$viewHelper->validateArguments();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function validateArgumentsCallsTheRightValidators() {
		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\ViewHelper\AbstractViewHelper'), array('render', 'prepareArguments'), array(), '', FALSE);
		$viewHelper->injectReflectionService($this->objectManager->getObject('F3\FLOW3\Reflection\Service'));

		$viewHelper->setArguments(new \F3\Fluid\Core\ViewHelper\Arguments(array('test' => 'Value of argument')));

		$validatorResolver = $this->getMock('F3\FLOW3\Validation\ValidatorResolver', array('createValidator'), array(), '', FALSE);
		$validatorResolver->expects($this->once())->method('createValidator')->with('string')->will($this->returnValue(new \F3\FLOW3\Validation\Validator\TextValidator()));

		$viewHelper->injectValidatorResolver($validatorResolver);

		$viewHelper->expects($this->once())->method('prepareArguments')->will($this->returnValue(array(
			'test' => new \F3\Fluid\Core\ViewHelper\ArgumentDefinition("test", "string", FALSE, "documentation")
		)));

		$viewHelper->validateArguments();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setControllerContextSetsTheControllerContext() {
		$controllerContext = $this->getMock('F3\FLOW3\MVC\Controller\ControllerContext');
		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\ViewHelper\AbstractViewHelper'), array('render', 'prepareArguments'), array(), '', FALSE);

		$viewHelper->setControllerContext($controllerContext);
		$this->assertSame($viewHelper->_get('controllerContext'), $controllerContext);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setViewHelperVariableContainerSetsTheViewHelperVariableContainer() {
		$viewHelperVariableContainer = $this->getMock('F3\Fluid\Core\ViewHelper\ViewHelperVariableContainer');
		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\ViewHelper\AbstractViewHelper'), array('render', 'prepareArguments'), array(), '', FALSE);

		$viewHelper->setViewHelperVariableContainer($viewHelperVariableContainer);
		$this->assertSame($viewHelper->_get('viewHelperVariableContainer'), $viewHelperVariableContainer);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function objectAccessorPostProcessorDisabledSettingIsReturnedToCorrectly() {
		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\ViewHelper\AbstractViewHelper'), array('render', 'prepareArguments'), array(), '', FALSE);

		$this->assertTrue($viewHelper->isObjectAccessorPostProcessorEnabled());

		$viewHelper->_set('objectAccessorPostProcessorEnabled', FALSE);
		$this->assertFalse($viewHelper->isObjectAccessorPostProcessorEnabled());

		$viewHelper->_set('objectAccessorPostProcessorEnabled', TRUE);
		$this->assertTrue($viewHelper->isObjectAccessorPostProcessorEnabled());
	}
}
?>