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

use TYPO3\Flow\Http\Request;
use TYPO3\Flow\Http\Uri;
use TYPO3\Flow\Reflection\ObjectAccess;
use TYPO3\Flow\Security\Policy\PolicyService;
use TYPO3\Flow\Security\Policy\Role;
use TYPO3\Fluid\ViewHelpers\Security\IfHasRoleViewHelper;
use TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase;

require_once(__DIR__ . '/../ViewHelperBaseTestcase.php');

/**
 * Test case for IfHasRoleViewHelper
 *
 */
class IfHasRoleViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * Create a mock controllerContext
	 *
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	protected function getMockControllerContext() {
		$httpRequest = Request::create(new Uri('http://robertlemke.com/blog'));
		$mockRequest = $this->getMock('TYPO3\Flow\Mvc\ActionRequest', array(), array($httpRequest));
		$mockRequest->expects($this->any())->method('getControllerPackageKey')->will($this->returnValue('Acme.Demo'));

		$mockControllerContext = $this->getMock('TYPO3\Flow\Mvc\Controller\ControllerContext', array('getRequest'), array(), '', FALSE);
		$mockControllerContext->expects($this->any())->method('getRequest')->will($this->returnValue($mockRequest));

		return $mockControllerContext;
	}

	/**
	 * @test
	 */
	public function viewHelperRendersThenPartIfHasRoleReturnsTrue() {
		$role = new Role('Acme.Demo:SomeRole');

		$mockSecurityContext = $this->getMock('TYPO3\Flow\Security\Context', array(), array(), '', FALSE);
		$mockSecurityContext->expects($this->once())->method('hasRole')->with('Acme.Demo:SomeRole')->will($this->returnValue(TRUE));

		$mockPolicyService = $this->getMock('TYPO3\Flow\Security\Policy\PolicyService', array(), array(), '', FALSE);
		$mockPolicyService->expects($this->once())->method('getRole')->with('Acme.Demo:SomeRole')->will($this->returnValue($role));

		$mockViewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Security\IfHasRoleViewHelper', array('renderThenChild', 'hasAccessToPrivilege'));
		$this->inject($mockViewHelper, 'securityContext', $mockSecurityContext);
		$this->inject($mockViewHelper, 'controllerContext', $this->getMockControllerContext());
		$this->inject($mockViewHelper, 'policyService', $mockPolicyService);
		$mockViewHelper->expects($this->once())->method('renderThenChild')->will($this->returnValue('then-child'));

		/** @var IfHasRoleViewHelper $mockViewHelper */
		$actualResult = $mockViewHelper->render('SomeRole');
		$this->assertEquals('then-child', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperHandlesPackageKeyAttributeCorrectly() {
		$mockSecurityContext = $this->getMock('TYPO3\Flow\Security\Context', array(), array(), '', FALSE);
		$mockSecurityContext->expects($this->any())->method('hasRole')->will($this->returnCallback(function($role) {
			switch($role) {
				case 'TYPO3.Fluid:Administrator':
					return TRUE;
				case 'TYPO3.Fluid:User':
					return FALSE;
			}

		}));

		$mockViewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Security\IfHasRoleViewHelper', array('renderThenChild', 'renderElseChild', 'hasAccessToPrivilege'));
		$this->inject($mockViewHelper, 'securityContext', $mockSecurityContext);
		$this->inject($mockViewHelper, 'controllerContext', $this->getMockControllerContext());
		$mockViewHelper->expects($this->any())->method('renderThenChild')->will($this->returnValue('true'));
		$mockViewHelper->expects($this->any())->method('renderElseChild')->will($this->returnValue('false'));

		$actualResult = $mockViewHelper->render(new Role('TYPO3.Fluid:Administrator'));
		$this->assertEquals('true', $actualResult, 'Full role identifier in role argument is accepted');

		$actualResult = $mockViewHelper->render(new Role('TYPO3.Fluid:User'), 'TYPO3.Fluid');
		$this->assertEquals('false', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperUsesSpecifiedAccountForCheck() {
		$mockAccount = $this->getMock('TYPO3\Flow\Security\Account');
		$mockAccount->expects($this->any())->method('hasRole')->will($this->returnCallback(function(Role $role) {
			switch($role->getIdentifier()) {
				case 'TYPO3.Fluid:Administrator':
					return TRUE;
			}
		}));

		$mockViewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Security\IfHasRoleViewHelper', array('renderThenChild', 'renderElseChild', 'hasAccessToPrivilege'));
		$this->inject($mockViewHelper, 'controllerContext', $this->getMockControllerContext());
		$mockViewHelper->expects($this->any())->method('renderThenChild')->will($this->returnValue('true'));
		$mockViewHelper->expects($this->any())->method('renderElseChild')->will($this->returnValue('false'));

		/** @var IfHasRoleViewHelper $mockViewHelper */
		$actualResult = $mockViewHelper->render(new Role('TYPO3.Fluid:Administrator'), NULL, $mockAccount);
		$this->assertEquals('true', $actualResult, 'Full role identifier in role argument is accepted');
	}
}
