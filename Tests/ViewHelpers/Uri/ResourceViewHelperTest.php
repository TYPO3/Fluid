<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers\Uri;

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
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id: AliasViewHelper.php 2614 2009-06-15 18:13:18Z bwaidelich $
 */

require_once(__DIR__ . '/../ViewHelperBaseTestcase.php');

/**
 * Testcase for the resource uri view helper
 *
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id: AliasViewHelper.php 2614 2009-06-15 18:13:18Z bwaidelich $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
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