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
 * @version $Id: ActionViewHelper.php 2345 2009-05-23 16:51:24Z bwaidelich $
 */

/**
 * A view helper for creating URIs to resources.
 *
 * = Examples =
 *
 * <code title="Defaults">
 * <link href="{f:uri.resource('css/stylesheet.css')}" rel="stylesheet" />
 * </code>
 * 
 * Output:
 * <link href="Resources/Packages/MyPackage/stylesheet.css" rel="stylesheet" />
 * (depending on current package)
 *
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id: ActionViewHelper.php 2345 2009-05-23 16:51:24Z bwaidelich $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
class ResourceViewHelper extends \F3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Render the URI to the resource. The filename is used from child content.
	 *
	 * @param string $package Target package key. If not set, the current package key will be used
	 * @return string The URI to the resource
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function render($package = NULL) {
		$packageKey = $package !== NULL ? $package : $this->controllerContext->getRequest()->getControllerPackageKey();
		$resource = $this->renderChildren();
		$uri = 'Resources/Packages/' . $packageKey . '/' . $resource;
		return $uri;
	}
}

?>