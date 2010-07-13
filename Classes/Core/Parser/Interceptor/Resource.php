<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\Parser\Interceptor;

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
 * This interceptor looks for URIs pointing to package resources and in place
 * of those adds ViewHelperNode instances using the ResourceViewHelper to
 * make those URIs work in the rendered template.
 *
 * That means you can build your template so that it can be previewed as is and
 * pointers to CSS, JS, images, ... will still work when the resources are
 * mirrored by FLOW3.
 *
 * Currently the supported URIs are of the form
 *  [../]Public/Some/<Path/To/Resource> (will use current package)
 *  [../]<PackageKey>/Resources/Public/<Path/To/Resource> (will use given package)
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Resource implements \F3\Fluid\Core\Parser\InterceptorInterface {

	/**
	 * Split a text at what seems to be a package resource URI.
	 * @var string
	 */
	const PATTERN_SPLIT_AT_RESOURCE_URIS = '!((?:(?:../)|(?:[^"\'(]+/))*Public/[^"\')]+)!';

	/**
	 * Is the text at hand a resource URI and what are path/package?
	 * @var string
	 * @see \F3\FLOW3\Pckage\Package::PATTERN_MATCH_PACKAGEKEY
	 */
	const PATTERN_MATCH_RESOURCE_URI = '!(?:../)*(?:(?P<Package>[A-Z][A-Za-z0-9_]+)/Resources/)?Public/(?P<Path>[^"]+)!';

	/**
	 * The default package key to use when rendering resource links without a
	 * package key in the source URL.
	 * @var string
	 */
	protected $defaultPackageKey;

	/**
	 * Inject object factory
	 *
	 * @param \F3\FLOW3\Object\ObjectManagerInterface $objectManager
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function injectObjectManager(\F3\FLOW3\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 *
	 * @param string $defaultPackageKey
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function setDefaultPackageKey($defaultPackageKey) {
		if (!preg_match(\F3\FLOW3\Package\Package::PATTERN_MATCH_PACKAGEKEY, $defaultPackageKey)) {
			throw new \InvalidArgumentException('The given argument was not a valid package key.', 1277287099);
		}
		$this->defaultPackageKey = $defaultPackageKey;
	}

	/**
	 * Looks for URIs pointing to package resources and in place of those adds
	 * ViewHelperNode instances using the ResourceViewHelper.
	 *
	 * @param \F3\Fluid\Core\Parser\SyntaxTree\NodeInterface $node
	 * @param integer $interceptorPosition One of the INTERCEPT_* constants for the current interception point
	 * @return \F3\Fluid\Core\Parser\SyntaxTree\NodeInterface
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function process(\F3\Fluid\Core\Parser\SyntaxTree\NodeInterface $node, $interceptorPosition) {
		$textParts = preg_split(self::PATTERN_SPLIT_AT_RESOURCE_URIS, $node->getText(), -1, PREG_SPLIT_DELIM_CAPTURE);
		$node = $this->objectManager->create('F3\Fluid\Core\Parser\SyntaxTree\TextNode', '');
		foreach ($textParts as $part) {
			$matches = array();
			if (preg_match(self::PATTERN_MATCH_RESOURCE_URI, $part, $matches)) {
				$arguments = array(
					'path' => $this->objectManager->create('F3\Fluid\Core\Parser\SyntaxTree\TextNode', $matches['Path'])
				);
				if (isset($matches['Package']) && preg_match(\F3\FLOW3\Package\Package::PATTERN_MATCH_PACKAGEKEY, $matches['Package'])) {
					$arguments['package'] = $this->objectManager->create('F3\Fluid\Core\Parser\SyntaxTree\TextNode', $matches['Package']);
				} elseif ($this->defaultPackageKey !== NULL) {
					$arguments['package'] = $this->objectManager->create('F3\Fluid\Core\Parser\SyntaxTree\TextNode', $this->defaultPackageKey);
				}
				$viewHelper = $this->objectManager->create('F3\Fluid\ViewHelpers\Uri\ResourceViewHelper');
				$node->addChildNode($this->objectManager->create('F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', $viewHelper, $arguments));
			} else {
				$node->addChildNode($this->objectManager->create('F3\Fluid\Core\Parser\SyntaxTree\TextNode', $part));
			}
		}

		return $node;
	}

	/**
	 * This interceptor wants to hook into text nodes.
	 *
	 * @return array Array of INTERCEPT_* constants
	 */
	public function getInterceptionPoints() {
		return array(
			\F3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_TEXT
		);
	}
}
?>