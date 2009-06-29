<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers\Uri;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id: ExternalViewHelperTest.php 2463 2009-05-29 10:22:26Z bwaidelich $
 */

require_once(__DIR__ . '/../ViewHelperBaseTestcase.php');

/**
 * Testcase for the resource uri view helper
 *
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id: ExternalViewHelperTest.php 2463 2009-05-29 10:22:26Z bwaidelich $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
class ResourceViewHelperTest extends \F3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * @test
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function renderUsesCurrentControllerPackageKeyToBuildTheResourceURI() {
		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\Uri\ResourceViewHelper'), array('renderChildren'), array(), '', FALSE);
		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue('foo'));
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$mockRequest = $this->getMock('F3\FLOW3\MVC\RequestInterface');
		$mockRequest->expects($this->atLeastOnce())->method('getControllerPackageKey')->will($this->returnValue('PackageKey'));
		$this->controllerContext->expects($this->atLeastOnce())->method('getRequest')->will($this->returnValue($mockRequest));

		$resourceUri = $viewHelper->render();
		$this->assertEquals('Resources/Packages/PackageKey/foo', $resourceUri);
	}
}

?>