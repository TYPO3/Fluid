<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers\Security;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

require_once(__DIR__ . '/../ViewHelperBaseTestcase.php');

/**
 * Testcase for IfAccessViewHelper
 *
 */
class IfAccessViewHelperTest extends \TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * @test
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
	 */
	public function hasAccessToResourceReturnsTrueIfNoAccessDenyExceptionsHasBeenThrownByTheAccessDecisionManager() {
		$mockAccessDecisionManager = $this->getMock('TYPO3\Flow\Security\Authorization\AccessDecisionManagerInterface', array(), array(), '', FALSE);
		$mockAccessDecisionManager->expects($this->once())->method('decideOnResource')->with('myResource');

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Security\IfAccessViewHelper', array('dummy'));
		$viewHelper->injectAccessDecisionManager($mockAccessDecisionManager);

		$this->assertTrue($viewHelper->_call('hasAccessToResource', 'myResource'));
	}

	/**
	 * @test
	 */
	public function hasAccessToResourceReturnsFalseIfAnAccessDenyExceptionsHasBeenThrownByTheAccessDecisionManager() {
		$mockAccessDecisionManager = $this->getMock('TYPO3\Flow\Security\Authorization\AccessDecisionManagerInterface', array(), array(), '', FALSE);
		$mockAccessDecisionManager->expects($this->once())->method('decideOnResource')->with('myResource')->will($this->throwException(new \TYPO3\Flow\Security\Exception\AccessDeniedException()));

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Security\IfAccessViewHelper', array('dummy'));
		$viewHelper->injectAccessDecisionManager($mockAccessDecisionManager);

		$this->assertFalse($viewHelper->_call('hasAccessToResource', 'myResource'));
	}
}

?>
