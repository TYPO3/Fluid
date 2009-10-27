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
 * @version $Id: AliasViewHelper.php 2614 2009-06-15 18:13:18Z bwaidelich $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @scope prototype
 */
class ResourceViewHelper extends \F3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var \F3\FLOW3\Resource\Publisher
	 */
	protected $resourcePublisher;
	
	/**
	 * Inject the FLOW3 resource publisher.
	 *
	 * @param \F3\FLOW3\Resource\Publisher $resourcePublisher
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function injectResourcePublisher(\F3\FLOW3\Resource\Publisher $resourcePublisher) {
		$this->resourcePublisher = $resourcePublisher;
	}

	/**
	 * Render the URI to the resource. The filename is used from child content.
	 *
	 * @param string $resource The path and filename of the resource (relative to Public resource directory)
	 * @param string $packageKey Target package key. If not set, the current package key will be used
	 * @return string The URI to the resource
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @api
	 */
	public function render($resource, $packageKey = NULL) {
		if ($packageKey === NULL) {
			$packageKey = $this->controllerContext->getRequest()->getControllerPackageKey();
		}
		$mirrorPath = $this->resourcePublisher->getMirrorDirectory();
		$uri = $mirrorPath . 'Packages/' . $packageKey . '/' . $resource;
		return $uri;
	}
}

?>