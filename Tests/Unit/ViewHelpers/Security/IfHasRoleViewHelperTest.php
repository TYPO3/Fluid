<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers\Security;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

require_once(__DIR__ . '/../ViewHelperBaseTestcase.php');

/**
 * Testcase for IfHasRoleViewHelper
 *
 */
class IfHasRoleViewHelperTest extends \TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * @test
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
