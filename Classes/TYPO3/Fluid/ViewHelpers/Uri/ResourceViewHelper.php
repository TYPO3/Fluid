<?php
namespace TYPO3\Fluid\ViewHelpers\Uri;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Fluid\Core\ViewHelper\Exception\InvalidVariableException;

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
 * <code title="Resource URI">
 * {f:uri.resource(path: 'resource://DifferentPackage/Public/gfx/SomeImage.png')}
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
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Resource\Publishing\ResourcePublisher
	 */
	protected $resourcePublisher;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\I18n\Service
	 */
	protected $i18nService;

	/**
	 * Render the URI to the resource. The filename is used from child content.
	 *
	 * @param string $path The location of the resource, can be either a path relative to the Public resource directory of the package or a resource://... URI
	 * @param string $package Target package key. If not set, the current package key will be used
	 * @param \TYPO3\Flow\Resource\Resource $resource If specified, this resource object is used instead of the path and package information
	 * @param boolean $localize Whether resource localization should be attempted or not
	 * @return string The absolute URI to the resource
	 * @throws \TYPO3\Fluid\Core\ViewHelper\Exception\InvalidVariableException
	 * @api
	 */
	public function render($path = NULL, $package = NULL, \TYPO3\Flow\Resource\Resource $resource = NULL, $localize = TRUE) {
		if ($resource !== NULL) {
			$uri = $this->resourcePublisher->getPersistentResourceWebUri($resource);
			if ($uri === FALSE) {
				$uri = $this->resourcePublisher->getStaticResourcesWebBaseUri() . 'BrokenResource';
			}
		} else {
			if ($path === NULL) {
				throw new InvalidVariableException('The ResourceViewHelper did neither contain a valuable "resource" nor "path" argument.', 1353512742);
			}
			if ($package === NULL) {
				$package = $this->controllerContext->getRequest()->getControllerPackageKey();
			}
			if (strpos($path, 'resource://') === 0) {
				$matches = array();
				if (preg_match('#^resource://([^/]+)/Public/(.*)#', $path, $matches) === 1) {
					$package = $matches[1];
					$path = $matches[2];
				} else {
					throw new InvalidVariableException(sprintf('The path "%s" which was given to the ResourceViewHelper must point to a public resource.', $path), 1353512639);
				}
			}
			if ($localize === TRUE) {
				$resourcePath = 'resource://' . $package . '/Public/' . $path;
				$localizedResourcePathData = $this->i18nService->getLocalizedFilename($resourcePath);
				$matches = array();
				if (preg_match('#resource://([^/]+)/Public/(.*)#', current($localizedResourcePathData), $matches) === 1) {
					$package = $matches[1];
					$path = $matches[2];
				}
			}
			$uri = $this->resourcePublisher->getStaticResourcesWebBaseUri() . 'Packages/' . $package . '/' . $path;
		}
		return $uri;
	}
}

?>
