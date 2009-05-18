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
/**
 * Testcase for VariableContainer
 *
 * @package Fluid
 * @subpackage Tests
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class ViewHelperContextTest extends \F3\Testing\BaseTestCase {

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function viewHelperContextIsOfScopePrototype() {
		$mockView = $this->getMock('F3\FLOW3\MVC\View\ViewInterface');
		$instance1 = $this->objectManager->getObject('F3\Fluid\Core\ViewHelperContext', $mockView);
		$instance2 = $this->objectManager->getObject('F3\Fluid\Core\ViewHelperContext', $mockView);
		$this->assertNotSame($instance1, $instance2, 'The ViewHelperContext is not a prototype.');
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function viewIsSetInConstructor() {
		$mockView = $this->getMock('F3\FLOW3\MVC\View\ViewInterface');
		$viewHelperContext = new \F3\Fluid\Core\ViewHelperContext($mockView);
		$this->assertSame($mockView, $viewHelperContext->getView());
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function viewHelperDefaultsCanBeSetInConstructor() {
		$mockView = $this->getMock('F3\FLOW3\MVC\View\ViewInterface');
		$viewHelperDefaults = array(
			'\F3\Fluid\ViewHelpers\FooViewHelper' => array(
				'someParameter' => 'someDefaultValue',
				'someOtherParameter' => 'someOtherDefaultValue',
			),
			'\F3\Fluid\ViewHelpers\BarViewHelper' => array(
				'thirdParameter' => 'thirdDefaultValue',
			),
		);
		$expectedResult = array(
			'someParameter' => 'someDefaultValue',
			'someOtherParameter' => 'someOtherDefaultValue',
		);
		$viewHelperContext = new \F3\Fluid\Core\ViewHelperContext($mockView, $viewHelperDefaults);
		$this->assertEquals($expectedResult, $viewHelperContext->getViewHelperDefaults('\F3\Fluid\ViewHelpers\FooViewHelper'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function getViewHelperDefaultsReturnsAnEmptyArrayIfNoDefaultExistsForTheSpecifiedViewHelper() {
		$mockView = $this->getMock('F3\FLOW3\MVC\View\ViewInterface');
		$viewHelperDefaults = array(
			'\F3\Fluid\ViewHelpers\FooViewHelper' => array(
				'someParameter' => 'someDefaultValue',
				'someOtherParameter' => 'someOtherDefaultValue',
			),
			'\F3\Fluid\ViewHelpers\BarViewHelper' => array(
				'thirdParameter' => 'thirdDefaultValue',
			),
		);
		$viewHelperContext = new \F3\Fluid\Core\ViewHelperContext($mockView, $viewHelperDefaults);
		$this->assertEquals(array(), $viewHelperContext->getViewHelperDefaults('\F3\Fluid\ViewHelpers\BazViewHelper'));
	}

	/**
	 * test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function requestCanBeRetrieved() {
		$mockView = $this->getMock('F3\FLOW3\MVC\View\ViewInterface');
		$viewHelperContext = new \F3\Fluid\Core\ViewHelperContext($mockView);
		$this->assertEquals($viewHelperDefaults, $viewHelperContext->getViewHelperDefaults());
	}
}

?>