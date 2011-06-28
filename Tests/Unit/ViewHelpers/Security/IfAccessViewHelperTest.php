<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers\Security;

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

require_once(__DIR__ . '/../ViewHelperBaseTestcase.php');

/**
 * Testcase for IfAccessViewHelper
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class IfAccessViewHelperTest extends \TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function viewHelperRendersThenIfHasAccessToResourceReturnsTrue() {
		$mockViewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Security\IfAccessViewHelper', array('renderThenChild', 'hasAccessToResource'));
		$mockViewHelper->expects($this->once())->method('renderThenChild')->will($this->returnValue('foo'));
		$mockViewHelper->expects($this->once())->method('hasAccessToResource')->with('someResource')->will($this->returnValue(TRUE));

		$actualResult = $mockViewHelper->render('someResource');
		$this->assertEquals('foo', $actualResult);
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function viewHelperRendersElseIfHasAccessToResourceReturnsFalse() {
		$viewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Security\IfAccessViewHelper', array('hasAccessToResource', 'renderElseChild'));
		$viewHelper->expects($this->once())->method('hasAccessToResource')->with('someResource')->will($this->returnValue(FALSE));
		$viewHelper->expects($this->once())->method('renderElseChild')->will($this->returnValue('ElseViewHelperResults'));

		$actualResult = $viewHelper->render('someResource');
		$this->assertEquals('ElseViewHelperResults', $actualResult);
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function hasAccessToResourceReturnsTrueIfNoAccessDenyExceptionsHasBeenThrownByTheAccessDecisionManager() {
		$mockAccessDecisionManager = $this->getMock('TYPO3\FLOW3\Security\Authorization\AccessDecisionManagerInterface', array(), array(), '', FALSE);
		$mockAccessDecisionManager->expects($this->once())->method('decideOnResource')->with('myResource');

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Security\IfAccessViewHelper', array('dummy'));
		$viewHelper->injectAccessDecisionManager($mockAccessDecisionManager);

		$this->assertTrue($viewHelper->_call('hasAccessToResource', 'myResource'));
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function hasAccessToResourceReturnsFalseIfAnAccessDenyExceptionsHasBeenThrownByTheAccessDecisionManager() {
		$mockAccessDecisionManager = $this->getMock('TYPO3\FLOW3\Security\Authorization\AccessDecisionManagerInterface', array(), array(), '', FALSE);
		$mockAccessDecisionManager->expects($this->once())->method('decideOnResource')->with('myResource')->will($this->throwException(new \TYPO3\FLOW3\Security\Exception\AccessDeniedException()));

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Security\IfAccessViewHelper', array('dummy'));
		$viewHelper->injectAccessDecisionManager($mockAccessDecisionManager);

		$this->assertFalse($viewHelper->_call('hasAccessToResource', 'myResource'));
	}
}

?>
