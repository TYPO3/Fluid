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

use TYPO3\Flow\Tests\UnitTestCase;

/**
 * Test case for the CsrfTokenViewHelper
 */
class CsrfTokenViewHelperTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function viewHelperRendersTheCsrfTokenReturnedFromTheSecurityContext() {
		$mockSecurityContext = $this->getMock('TYPO3\Flow\Security\Context');
		$mockSecurityContext->expects($this->once())->method('getCsrfProtectionToken')->will($this->returnValue('TheCsrfToken'));

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Security\CsrfTokenViewHelper', array('dummy'));
		$viewHelper->_set('securityContext', $mockSecurityContext);

		$actualResult = $viewHelper->render();
		$this->assertEquals('TheCsrfToken', $actualResult);
	}
}
