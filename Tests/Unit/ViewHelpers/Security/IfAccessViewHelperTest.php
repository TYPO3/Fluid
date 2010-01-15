<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers\Security;

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

/**
 * Testcase for IfAccessViewHelper
 *
 * @version $Id: IfViewHelperTest.php 2813 2009-07-16 14:02:34Z k-fish $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class IfAccessViewHelperTest extends \F3\Testing\BaseTestCase {

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function viewHelperRendersChildrenIfHasAccessToResourceReturnsTrueAndNoThenViewHelperChildExists() {
		$mockViewHelper = $this->getMock('F3\Fluid\ViewHelpers\Security\IfAccessViewHelper', array('renderChildren', 'hasAccessToResource'));
		$mockViewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue('foo'));
		$mockViewHelper->expects($this->once())->method('hasAccessToResource')->with('someResource')->will($this->returnValue(TRUE));

		$actualResult = $mockViewHelper->render('someResource');
		$this->assertEquals('foo', $actualResult);
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function viewHelperRendersThenViewHelperChildIfHasAccessToResourceReturnsTrueAndThenViewHelperChildExists() {
		$renderingContext = $this->getMock('F3\Fluid\Core\Rendering\RenderingContext');

		$mockThenViewHelperNode = $this->getMock('F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('getViewHelperClassName', 'evaluate', 'setRenderingContext'), array(), '', FALSE);
		$mockThenViewHelperNode->expects($this->at(0))->method('getViewHelperClassName')->will($this->returnValue('F3\Fluid\ViewHelpers\ThenViewHelper'));
		$mockThenViewHelperNode->expects($this->at(1))->method('setRenderingContext')->with($renderingContext);
		$mockThenViewHelperNode->expects($this->at(2))->method('evaluate')->will($this->returnValue('ThenViewHelperResults'));

		$viewHelper = $this->getMock('F3\Fluid\ViewHelpers\Security\IfAccessViewHelper', array('hasAccessToResource'));
		$viewHelper->expects($this->once())->method('hasAccessToResource')->with('someResource')->will($this->returnValue(TRUE));

		$viewHelper->setChildNodes(array($mockThenViewHelperNode));
		$viewHelper->setRenderingContext($renderingContext);
		$actualResult = $viewHelper->render('someResource');

		$this->assertEquals('ThenViewHelperResults', $actualResult);
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function renderReturnsEmptyStringIfHasAccessToResourceReturnsFalseAndNoElseViewHelperChildExists() {
		$viewHelper = $this->getMock('F3\Fluid\ViewHelpers\Security\IfAccessViewHelper', array('hasAccessToResource'));
		$viewHelper->expects($this->once())->method('hasAccessToResource')->with('someResource')->will($this->returnValue(FALSE));

		$actualResult = $viewHelper->render('someResource');
		$this->assertEquals('', $actualResult);
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function viewHelperRendersElseViewHelperChildIfHasAccessToResourceReturnsFalseAndElseViewHelperChildExists() {
		$renderingContext = $this->getMock('F3\Fluid\Core\Rendering\RenderingContext');

		$mockElseViewHelperNode = $this->getMock('F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('getViewHelperClassName', 'evaluate', 'setRenderingContext'), array(), '', FALSE);
		$mockElseViewHelperNode->expects($this->at(0))->method('getViewHelperClassName')->will($this->returnValue('F3\Fluid\ViewHelpers\ElseViewHelper'));
		$mockElseViewHelperNode->expects($this->at(1))->method('setRenderingContext')->with($renderingContext);
		$mockElseViewHelperNode->expects($this->at(2))->method('evaluate')->will($this->returnValue('ElseViewHelperResults'));

		$viewHelper = $this->getMock('F3\Fluid\ViewHelpers\Security\IfAccessViewHelper', array('hasAccessToResource'));
		$viewHelper->expects($this->once())->method('hasAccessToResource')->with('someResource')->will($this->returnValue(FALSE));

		$viewHelper->setChildNodes(array($mockElseViewHelperNode));
		$viewHelper->setRenderingContext($this->getMock('F3\Fluid\Core\Rendering\RenderingContext'));

		$actualResult = $viewHelper->render('someResource');
		$this->assertEquals('ElseViewHelperResults', $actualResult);
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function hasAccessToResourceReturnsTrueIfNoAccessDenyExceptionsHasBeenThrownByTheAccessDecisionManager() {
		$mockAccessDecisionManager = $this->getMock('F3\FLOW3\Security\Authorization\AccessDecisionManagerInterface', array(), array(), '', FALSE);
		$mockAccessDecisionManager->expects($this->once())->method('decideOnResource')->with('myResource');

		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\Security\IfAccessViewHelper'), array('dummy'));
		$viewHelper->injectAccessDecisionManager($mockAccessDecisionManager);

		$this->assertTrue($viewHelper->_call('hasAccessToResource', 'myResource'));
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function hasAccessToResourceReturnsFalseIfAnAccessDenyExceptionsHasBeenThrownByTheAccessDecisionManager() {
		$mockAccessDecisionManager = $this->getMock('F3\FLOW3\Security\Authorization\AccessDecisionManagerInterface', array(), array(), '', FALSE);
		$mockAccessDecisionManager->expects($this->once())->method('decideOnResource')->with('myResource')->will($this->throwException(new \F3\FLOW3\Security\Exception\AccessDeniedException()));

		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\Security\IfAccessViewHelper'), array('dummy'));
		$viewHelper->injectAccessDecisionManager($mockAccessDecisionManager);

		$this->assertFalse($viewHelper->_call('hasAccessToResource', 'myResource'));
	}
}

?>
