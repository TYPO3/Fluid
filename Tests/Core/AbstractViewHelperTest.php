<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core;

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
 * @package Fluid
 * @subpackage Tests
 * @version $Id$
 */

include_once(__DIR__ . '/Fixtures/TestViewHelper.php');

/**
 * Testcase for AbstractViewHelper
 *
 * @package
 * @subpackage Tests
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class AbstractViewHelperTest extends \F3\Testing\BaseTestCase {
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function argumentsCanBeRegistered() {
		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\AbstractViewHelper'), array('render'), array(), '', FALSE);
		$viewHelper->injectReflectionService($this->objectManager->getObject('F3\FLOW3\Reflection\Service'));

		$name = "This is a name";
		$description = "Example desc";
		$type = "string";
		$isRequired = TRUE;
		$expected = new \F3\Fluid\Core\ArgumentDefinition($name, $type, $description, $isRequired);

		$viewHelper->_call('registerArgument', $name, $type, $isRequired, $description);
		$this->assertEquals($viewHelper->prepareArguments(), array($name => $expected), 'Argument definitions not returned correctly.');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function prepareArgumentsCallsInitializeArguments() {
		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\AbstractViewHelper'), array('render', 'initializeArguments'), array(), '', FALSE);
		$viewHelper->injectReflectionService($this->objectManager->getObject('F3\FLOW3\Reflection\Service'));

		$viewHelper->arguments = new \F3\Fluid\Core\ViewHelperArguments(array());
		$viewHelper->expects($this->once())->method('initializeArguments');

		$viewHelper->prepareArguments();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function prepareArgumentsRegistersAnnotationBasedArguments() {

		$availableClassNames = array(
			'F3\Fluid\Core\Fixtures\TestViewHelper',
		);
		$reflectionService = new \F3\FLOW3\Reflection\Service();
		$reflectionService->setCache($this->getMock('F3\FLOW3\Cache\Frontend\VariableFrontend', array(), array(), '', FALSE));
		$reflectionService->initialize($availableClassNames);

		$viewHelper = new \F3\Fluid\Core\Fixtures\TestViewHelper();
		$viewHelper->injectReflectionService($reflectionService);

		$expected = array(
			'param1' => new \F3\Fluid\Core\ArgumentDefinition('param1', 'integer', 'P1 Stuff', TRUE),
			'param2' => new \F3\Fluid\Core\ArgumentDefinition('param2', 'array', 'P2 Stuff', TRUE),
			'param3' => new \F3\Fluid\Core\ArgumentDefinition('param3', 'string', 'P3 Stuff', FALSE, 'default'),
		);

		$this->assertEquals($expected, $viewHelper->prepareArguments(), 'Annotation based arguments were not registered.');

	}
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function validateArgumentsCallsPrepareArguments() {
		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\AbstractViewHelper'), array('render', 'prepareArguments'), array(), '', FALSE);
		$viewHelper->injectReflectionService($this->objectManager->getObject('F3\FLOW3\Reflection\Service'));

		$viewHelper->arguments = new \F3\Fluid\Core\ViewHelperArguments(array());
		$viewHelper->expects($this->once())->method('prepareArguments')->will($this->returnValue(array()));

		$viewHelper->validateArguments();
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function validateArgumentsAcceptsAllObjectsImplemtingArrayAccessAsAnArray() {
		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\AbstractViewHelper'), array('render', 'prepareArguments'), array(), '', FALSE);

		$viewHelper->arguments = new \F3\Fluid\Core\ViewHelperArguments(array('test' => new \ArrayObject));
		$viewHelper->expects($this->once())->method('prepareArguments')->will($this->returnValue(array('test' => new \F3\Fluid\Core\ArgumentDefinition('test', 'array', FALSE, 'documentation'))));
		$viewHelper->validateArguments();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function validateArgumentsCallsTheRightValidators() {
		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\AbstractViewHelper'), array('render', 'prepareArguments'), array(), '', FALSE);
		$viewHelper->injectReflectionService($this->objectManager->getObject('F3\FLOW3\Reflection\Service'));

		$viewHelper->arguments = new \F3\Fluid\Core\ViewHelperArguments(array('test' => 'Value of argument'));

		$validatorResolver = $this->getMock('F3\FLOW3\Validation\ValidatorResolver', array('getValidator'), array(), '', FALSE);
		$validatorResolver->expects($this->once())->method('getValidator')->with('string')->will($this->returnValue(new \F3\FLOW3\Validation\Validator\TextValidator()));

		$viewHelper->injectValidatorResolver($validatorResolver);

		$viewHelper->expects($this->once())->method('prepareArguments')->will($this->returnValue(array(
			'test' => new \F3\Fluid\Core\ArgumentDefinition("test", "string", FALSE, "documentation")
		)));

		$viewHelper->validateArguments();
	}
}
?>