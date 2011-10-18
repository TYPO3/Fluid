<?php
namespace TYPO3\Fluid\ViewHelpers\Uri;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
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
 * <output>
 * <link href="http://yourdomain.tld/_Resources/Static/YourPackage/CSS/Stylesheet.css" rel="stylesheet" />
 * (depending on current package)
 * </output>
 *
 * <code title="Other package resource">
 * {f:uri.resource(path: 'gfx/SomeImage.png', package: 'DifferentPackage')}
 * </code>
 * <output>
 * http://yourdomain.tld/_Resources/Static/DifferentPackage/gfx/SomeImage.png
 * (depending on domain)
 * </output>
 *
 * <code title="Resource object">
 * <img src="{f:uri.resource(resource: myImage.resource)}" />
 * </code>
 * <output>
 * <img src="http://yourdomain.tld/_Resources/Persistent/69e73da3ce0ad08c717b7b9f1c759182d6650944.jpg" />
 * (depending on your resource object)
 * </output>
 *
 * @api
 */
class ResourceViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var \TYPO3\FLOW3\Resource\Publishing\ResourcePublisher
	 */
	protected $resourcePublisher;

	/**
	 * Inject the FLOW3 resource publisher.
	 *
	 * @param \TYPO3\FLOW3\Resource\Publishing\ResourcePublisher $resourcePublisher
	 * @return void
	 */
	public function injectResourcePublisher(\TYPO3\FLOW3\Resource\Publishing\ResourcePublisher $resourcePublisher) {
		$this->resourcePublisher = $resourcePublisher;
	}

	/**
	 * Render the URI to the resource. The filename is used from child content.
	 *
	 * @param string $path The path and filename of the resource (relative to Public resource directory of the package)
	 * @param string $package Target package key. If not set, the current package key will be used
	 * @param \TYPO3\FLOW3\Resource\Resource $resource If specified, this resource object is used instead of the path and package information
	 * @param string $uri A resource URI, a relative / absolute path or URL
	 * @return string The absolute URI to the resource
	 * @api
	 */
	public function render($path = NULL, $package = NULL, $resource = NULL, $uri = NULL) {
		if ($uri !== NULL) {
			if (preg_match('#resource://([^/]*)/Public/(.*)#', $uri, $matches) > 0) {
				$package = $matches[1];
				$path = $matches[2];
			} else {
				return $uri;
			}
		}
		if ($resource === NULL) {
			if ($path === NULL) {
				return '!!! No path specified in uri.resource view helper !!!';
			}
			$uri = $this->resourcePublisher->getStaticResourcesWebBaseUri() . 'Packages/' . ($package === NULL ? $this->controllerContext->getRequest()->getControllerPackageKey() : $package ). '/' . $path;
		} else {
			$uri = $this->resourcePublisher->getPersistentResourceWebUri($resource);
			if ($uri === FALSE) {
				$uri = $this->resourcePublisher->getStaticResourcesWebBaseUri() . 'BrokenResource';
			}
		}
		return $uri;
	}
}

?>
