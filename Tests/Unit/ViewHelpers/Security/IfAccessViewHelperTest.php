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

use TYPO3\Flow\Security\Authorization\AccessDecisionManagerInterface;
use TYPO3\Fluid\ViewHelpers\Security\IfAccessViewHelper;

require_once(__DIR__ . '/../ViewHelperBaseTestcase.php');

/**
 * Testcase for IfAccessViewHelper
 *
 */
class IfAccessViewHelperTest extends \TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * @var AccessDecisionManagerInterface
	 */
	protected $mockAccessDecisionManager;

	/**
	 * @var IfAccessViewHelper
	 */
	protected $ifAccessViewHelper;

	public function setUp() {
		$this->mockAccessDecisionManager = $this->getMockBuilder('TYPO3\Flow\Security\Authorization\AccessDecisionManagerInterface')->getMock();

		$this->ifAccessViewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Security\IfAccessViewHelper', array('renderThenChild', 'renderElseChild'));
		$this->inject($this->ifAccessViewHelper, 'accessDecisionManager', $this->mockAccessDecisionManager);
	}

	/**
	 * @test
	 */
	public function viewHelperRendersThenIfHasAccessToResourceReturnsTrue() {
		$this->mockAccessDecisionManager->expects($this->once())->method('hasAccessToResource')->with('someResource')->will($this->returnValue(TRUE));
		$this->ifAccessViewHelper->expects($this->once())->method('renderThenChild')->will($this->returnValue('foo'));

		$actualResult = $this->ifAccessViewHelper->render('someResource');
		$this->assertEquals('foo', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperRendersElseIfHasAccessToResourceReturnsFalse() {
		$this->mockAccessDecisionManager->expects($this->once())->method('hasAccessToResource')->with('someResource')->will($this->returnValue(FALSE));
		$this->ifAccessViewHelper->expects($this->once())->method('renderElseChild')->will($this->returnValue('ElseViewHelperResults'));

		$actualResult = $this->ifAccessViewHelper->render('someResource');
		$this->assertEquals('ElseViewHelperResults', $actualResult);
	}
}
