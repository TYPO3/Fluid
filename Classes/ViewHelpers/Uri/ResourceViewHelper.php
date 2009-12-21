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
 * <link href="{f:uri.resource(path: 'CSS/Stylesheet.css')}" rel="stylesheet" />
 * </code>
 *
 * Output:
 * <link href="http://yourdomain.tld/_Resources/Static/YourPackage/CSS/Stylesheet.css" rel="stylesheet" />
 * (depending on current package)
 *
 * <code title="Other package resource">
 * {f:uri.resource(path: 'gfx/SomeImage.png', package: 'DifferentPackage')}
 * </code>
 *
 * Output:
 * http://yourdomain.tld/_Resources/Static/DifferentPackage/gfx/SomeImage.png
 * (depending on domain)
 *
 * <code title="Resource object">
 * <img src="{f:uri.resource(object: {myImage.resource})}" />
 * </code>
 *
 * Output:
 * <img src="http://yourdomain.tld/_Resources/Persistent/69e73da3ce0ad08c717b7b9f1c759182d6650944.jpg" />
 *
 * (depending on your resource object)
 *
 * <code title="Resource object with title">
 * <img src="{f:uri.resource(object: {myImage.resource}, title: 'My title')}" />
 * </code>
 *
 * Output:
 * <img src="http://yourdomain.tld/_Resources/Persistent/69e73da3ce0ad08c717b7b9f1c759182d6650944/my-title.jpg" />
 *
 * @version $Id: AliasViewHelper.php 2614 2009-06-15 18:13:18Z bwaidelich $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @scope prototype
 */
class ResourceViewHelper extends \F3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var \F3\FLOW3\Resource\Publishing\ResourcePublisher
	 */
	protected $resourcePublisher;
	
	/**
	 * Inject the FLOW3 resource publisher.
	 *
	 * @param \F3\FLOW3\Resource\Publishing\ResourcePublisher $resourcePublisher
	 */
	public function injectResourcePublisher(\F3\FLOW3\Resource\Publishing\ResourcePublisher $resourcePublisher) {
		$this->resourcePublisher = $resourcePublisher;
	}

	/**
	 * Render the URI to the resource. The filename is used from child content.
	 *
	 * @param string $path The path and filename of the resource (relative to Public resource directory of the package)
	 * @param string $package Target package key. If not set, the current package key will be used
	 * @param \F3\FLOW3\Resource\Resource $resource If specified, this resource object is used instead of the path and package information
	 * @param string $title If specified, this title is added to the resource uri to make it more descriptive
	 * @return string The absolute URI to the resource
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function render($path = NULL, $package = NULL, $resource = NULL, $title = '') {
		if ($resource === NULL) {
			if ($path === NULL) {
				return '!!! No path specified in uri.resource view helper !!!';
			}
			$uri = $this->resourcePublisher->getStaticResourcesWebBaseUri() . 'Packages/' . ($package === NULL ? $this->controllerContext->getRequest()->getControllerPackageKey() : $package ). '/' . $path;
		} else {
			$uri = $this->resourcePublisher->getPersistentResourceWebUri($resource, $title);
			if ($uri === FALSE) {
				$uri = $this->resourcePublisher->getStaticResourcesWebBaseUri() . 'BrokenResource';
			}
		}
		return $uri;
	}
}

?>