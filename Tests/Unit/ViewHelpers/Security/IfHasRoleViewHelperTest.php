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
 * Testcase for IfHasRoleViewHelper
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class IfHasRoleViewHelperTest extends \TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function viewHelperRendersThenPartIfHasRoleReturnsTrue() {
		$mockSecurityContext = $this->getMock('TYPO3\FLOW3\Security\Context', array(), array(), '', FALSE);
		$mockSecurityContext->expects($this->once())->method('hasRole')->with('someGA')->will($this->returnValue(TRUE));

		$mockViewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Security\IfHasRoleViewHelper', array('renderThenChild', 'hasAccessToResource'));
		$mockViewHelper->injectSecurityContext($mockSecurityContext);
		$mockViewHelper->expects($this->once())->method('renderThenChild')->will($this->returnValue('foo'));

		$actualResult = $mockViewHelper->render('someGA');
		$this->assertEquals('foo', $actualResult);
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function viewHelperRendersElsePartIfConditionIsFalse() {
		$mockSecurityContext = $this->getMock('TYPO3\FLOW3\Security\Context', array(), array(), '', FALSE);
		$mockSecurityContext->expects($this->once())->method('hasRole')->with('someGA')->will($this->returnValue(FALSE));

		$viewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Security\IfHasRoleViewHelper', array('renderElseChild'));
		$viewHelper->expects($this->once())->method('renderElseChild')->will($this->returnValue('ElseViewHelperResults'));
		$viewHelper->injectSecurityContext($mockSecurityContext);

		$actualResult = $viewHelper->render('someGA');
		$this->assertEquals('ElseViewHelperResults', $actualResult);
	}
}

?>
