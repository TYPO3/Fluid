<?php
namespace TYPO3\Fluid\Tests\Unit\Core\ViewHelper;

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

require_once(__DIR__ . '/../../ViewHelpers/ViewHelperBaseTestcase.php');

/**
 * Testcase for Condition ViewHelper
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class AbstractConditionViewHelperTest extends \TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * var \TYPO3\Fluid\Core\ViewHelper\AbstractConditionViewHelper
	 */
	protected $viewHelper;

	/**
	 * var \TYPO3\Fluid\Core\ViewHelper\Arguments
	 */
	protected $mockArguments;

	public function setUp() {
		parent::setUp();
		$this->viewHelper = $this->getAccessibleMock('TYPO3\Fluid\Core\ViewHelper\AbstractConditionViewHelper', array('getRenderingContext', 'renderChildren', 'hasArgument'));
		$this->viewHelper->expects($this->any())->method('getRenderingContext')->will($this->returnValue($this->renderingContext));
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function renderThenChildReturnsAllChildrenIfNoThenViewHelperChildExists() {
		$this->viewHelper->expects($this->any())->method('renderChildren')->will($this->returnValue('foo'));

		$actualResult = $this->viewHelper->_call('renderThenChild');
		$this->assertEquals('foo', $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function renderThenChildReturnsThenViewHelperChildIfConditionIsTrueAndThenViewHelperChildExists() {
		$mockThenViewHelperNode = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('getViewHelperClassName', 'evaluate'), array(), '', FALSE);
		$mockThenViewHelperNode->expects($this->at(0))->method('getViewHelperClassName')->will($this->returnValue('TYPO3\Fluid\ViewHelpers\ThenViewHelper'));
		$mockThenViewHelperNode->expects($this->at(1))->method('evaluate')->with($this->renderingContext)->will($this->returnValue('ThenViewHelperResults'));

		$this->viewHelper->setChildNodes(array($mockThenViewHelperNode));
		$actualResult = $this->viewHelper->_call('renderThenChild');
		$this->assertEquals('ThenViewHelperResults', $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderElseChildReturnsEmptyStringIfConditionIsFalseAndNoElseViewHelperChildExists() {
		$actualResult = $this->viewHelper->_call('renderElseChild');
		$this->assertEquals('', $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderElseChildRendersElseViewHelperChildIfConditionIsFalseAndNoThenViewHelperChildExists() {
		$mockElseViewHelperNode = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('getViewHelperClassName', 'evaluate', 'setRenderingContext'), array(), '', FALSE);
		$mockElseViewHelperNode->expects($this->at(0))->method('getViewHelperClassName')->will($this->returnValue('TYPO3\Fluid\ViewHelpers\ElseViewHelper'));
		$mockElseViewHelperNode->expects($this->at(1))->method('evaluate')->with($this->renderingContext)->will($this->returnValue('ElseViewHelperResults'));

		$this->viewHelper->setChildNodes(array($mockElseViewHelperNode));
		$actualResult = $this->viewHelper->_call('renderElseChild');
		$this->assertEquals('ElseViewHelperResults', $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderThenChildReturnsValueOfThenArgumentIfConditionIsTrue() {
		$this->viewHelper->expects($this->atLeastOnce())->method('hasArgument')->with('then')->will($this->returnValue(TRUE));
		$this->arguments['then'] = 'ThenArgument';
		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$actualResult = $this->viewHelper->_call('renderThenChild');
		$this->assertEquals('ThenArgument', $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderThenChildReturnsEmptyStringIfChildNodesOnlyContainElseViewHelper() {
		$mockElseViewHelperNode = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('getViewHelperClassName', 'evaluate'), array(), '', FALSE);
		$mockElseViewHelperNode->expects($this->any())->method('getViewHelperClassName')->will($this->returnValue('TYPO3\Fluid\ViewHelpers\ElseViewHelper'));
		$this->viewHelper->setChildNodes(array($mockElseViewHelperNode));
		$this->viewHelper->expects($this->never())->method('renderChildren')->will($this->returnValue('Child nodes'));

		$actualResult = $this->viewHelper->_call('renderThenChild');
		$this->assertEquals('', $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function thenArgumentHasPriorityOverChildNodesIfConditionIsTrue() {
		$mockRenderingContext = $this->getMock('TYPO3\Fluid\Core\Rendering\RenderingContextInterface');

		$mockThenViewHelperNode = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('getViewHelperClassName', 'evaluate', 'setRenderingContext'), array(), '', FALSE);
		$mockThenViewHelperNode->expects($this->never())->method('evaluate');

		$this->viewHelper->setChildNodes(array($mockThenViewHelperNode));

		$this->viewHelper->expects($this->atLeastOnce())->method('hasArgument')->with('then')->will($this->returnValue(TRUE));
		$this->arguments['then'] = 'ThenArgument';

		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$actualResult = $this->viewHelper->_call('renderThenChild');
		$this->assertEquals('ThenArgument', $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderReturnsValueOfElseArgumentIfConditionIsFalse() {
		$this->viewHelper->expects($this->atLeastOnce())->method('hasArgument')->with('else')->will($this->returnValue(TRUE));
		$this->arguments['else'] = 'ElseArgument';
		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$actualResult = $this->viewHelper->_call('renderElseChild');
		$this->assertEquals('ElseArgument', $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function elseArgumentHasPriorityOverChildNodesIfConditionIsFalse() {
		$mockRenderingContext = $this->getMock('TYPO3\Fluid\Core\Rendering\RenderingContextInterface');

		$mockElseViewHelperNode = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('getViewHelperClassName', 'evaluate', 'setRenderingContext'), array(), '', FALSE);
		$mockElseViewHelperNode->expects($this->any())->method('getViewHelperClassName')->will($this->returnValue('TYPO3\Fluid\ViewHelpers\ElseViewHelper'));
		$mockElseViewHelperNode->expects($this->never())->method('evaluate');

		$this->viewHelper->setChildNodes(array($mockElseViewHelperNode));

		$this->viewHelper->expects($this->atLeastOnce())->method('hasArgument')->with('else')->will($this->returnValue(TRUE));
		$this->arguments['else'] = 'ElseArgument';
		$this->injectDependenciesIntoViewHelper($this->viewHelper);

		$actualResult = $this->viewHelper->_call('renderElseChild');
		$this->assertEquals('ElseArgument', $actualResult);
	}
}
?>