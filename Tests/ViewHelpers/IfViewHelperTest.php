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
 * @package
 * @subpackage
 * @version $Id$
 */
/**
 * Testcase for [insert classname here]
 *
 * @package
 * @subpackage Tests
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class IfViewHelperTest extends \F3\Testing\BaseTestCase {

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function viewHelperRendersChildrenIfConditionIsTrueAndNoThenViewHelperChildExists() {
		$mockViewHelper = $this->getMock('F3\Fluid\ViewHelpers\IfViewHelper', array('renderChildren'));
		$mockViewHelper->expects($this->at(0))->method('renderChildren')->will($this->returnValue('foo'));

		$actualResult = $mockViewHelper->render(TRUE);
		$this->assertEquals('foo', $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function viewHelperRendersThenViewHelperChildIfConditionIsTrueAndThenViewHelperChildExists() {
		$mockVariableContainer = $this->getMock('F3\Fluid\Core\ViewHelper\VariableContainer');

		$mockThenViewHelperNode = $this->getMock('F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('getViewHelperClassName', 'evaluate', 'setVariableContainer'), array(), '', FALSE);
		$mockThenViewHelperNode->expects($this->at(0))->method('getViewHelperClassName')->will($this->returnValue('F3\Fluid\ViewHelpers\ThenViewHelper'));
		$mockThenViewHelperNode->expects($this->at(1))->method('setVariableContainer')->with($mockVariableContainer);
		$mockThenViewHelperNode->expects($this->at(2))->method('evaluate')->will($this->returnValue('ThenViewHelperResults'));

		$viewHelper = new \F3\Fluid\ViewHelpers\IfViewHelper();
		$viewHelper->setVariableContainer($mockVariableContainer);
		$viewHelper->setChildNodes(array($mockThenViewHelperNode));
		$viewHelper->setViewHelperContext($this->getMock('F3\Fluid\Core\ViewHelper\ViewHelperContext'));
		$actualResult = $viewHelper->render(TRUE);
		$this->assertEquals('ThenViewHelperResults', $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderReturnsEmptyStringIfConditionIsFalseAndNoThenViewHelperChildExists() {
		$viewHelper = new \F3\Fluid\ViewHelpers\IfViewHelper();

		$actualResult = $viewHelper->render(FALSE);
		$this->assertEquals('', $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function viewHelperRendersElseViewHelperChildIfConditionIsFalseAndNoThenViewHelperChildExists() {
		$mockVariableContainer = $this->getMock('F3\Fluid\Core\ViewHelper\VariableContainer');

		$mockElseViewHelperNode = $this->getMock('F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('getViewHelperClassName', 'evaluate', 'setVariableContainer'), array(), '', FALSE);
		$mockElseViewHelperNode->expects($this->at(0))->method('getViewHelperClassName')->will($this->returnValue('F3\Fluid\ViewHelpers\ElseViewHelper'));
		$mockElseViewHelperNode->expects($this->at(1))->method('setVariableContainer')->with($mockVariableContainer);
		$mockElseViewHelperNode->expects($this->at(2))->method('evaluate')->will($this->returnValue('ElseViewHelperResults'));

		$viewHelper = new \F3\Fluid\ViewHelpers\IfViewHelper();
		$viewHelper->setVariableContainer($mockVariableContainer);
		$viewHelper->setChildNodes(array($mockElseViewHelperNode));
		$viewHelper->setViewHelperContext($this->getMock('F3\Fluid\Core\ViewHelper\ViewHelperContext'));

		$actualResult = $viewHelper->render(FALSE);
		$this->assertEquals('ElseViewHelperResults', $actualResult);
	}
}

?>
