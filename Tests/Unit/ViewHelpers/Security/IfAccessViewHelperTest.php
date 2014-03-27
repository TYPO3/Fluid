<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers\Security;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Security\Authorization\PrivilegeManagerInterface;
use TYPO3\Fluid\ViewHelpers\Security\IfAccessViewHelper;
use TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase;

require_once(__DIR__ . '/../ViewHelperBaseTestcase.php');

/**
 * Testcase for IfAccessViewHelper
 *
 */
class IfAccessViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @var IfAccessViewHelper
	 */
	protected $ifAccessViewHelper;

	/**
	 * @var PrivilegeManagerInterface|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mockPrivilegeManager;

	public function setUp() {
		$this->mockPrivilegeManager = $this->getMockBuilder('TYPO3\Flow\Security\Authorization\PrivilegeManagerInterface')->getMock();

		$this->ifAccessViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Security\IfAccessViewHelper', array('renderThenChild', 'renderElseChild'));
		$this->inject($this->ifAccessViewHelper, 'privilegeManager', $this->mockPrivilegeManager);
	}

	/**
	 * @test
	 */
	public function viewHelperRendersThenIfHasAccessToPrivilegeTargetReturnsTrue() {
		$this->mockPrivilegeManager->expects($this->once())->method('isPrivilegeTargetGranted')->with('somePrivilegeTarget')->will($this->returnValue(TRUE));
		$this->ifAccessViewHelper->expects($this->once())->method('renderThenChild')->will($this->returnValue('foo'));

		$actualResult = $this->ifAccessViewHelper->render('somePrivilegeTarget');
		$this->assertEquals('foo', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperRendersElseIfHasAccessToPrivilegeTargetReturnsFalse() {
		$this->mockPrivilegeManager->expects($this->once())->method('isPrivilegeTargetGranted')->with('somePrivilegeTarget')->will($this->returnValue(FALSE));
		$this->ifAccessViewHelper->expects($this->once())->method('renderElseChild')->will($this->returnValue('ElseViewHelperResults'));

		$actualResult = $this->ifAccessViewHelper->render('somePrivilegeTarget');
		$this->assertEquals('ElseViewHelperResults', $actualResult);
	}
}
