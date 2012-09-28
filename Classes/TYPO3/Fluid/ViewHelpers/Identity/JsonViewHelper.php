<?php
namespace TYPO3\Fluid\ViewHelpers\Identity;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Renders the identity of a persisted object (if it has an identity).
 * Useful for using the identity outside of the form view helpers
 * (e.g. JavaScript and AJAX).
 *
 * Deprecated since 1.1.0. Use f:format.identifier and f:format.json
 * ViewHelpers instead.
 *
 * = Examples =
 *
 * <code title="Single alias">
 * <f:persistence.identity object="{post.blog}" />
 * </code>
 * <output>
 * 97e7e90a-413c-44ef-b2d0-ddfa4387b5ca
 * </output>
 *
 * @deprecated since 1.1.0
 * @see \TYPO3\Fluid\ViewHelpers\Format\IdentifierViewHelper
 */
class JsonViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var boolean
	 */
	protected $escapingInterceptorEnabled = FALSE;

	/**
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * Injects the Flow Persistence Manager
	 *
	 * @param \TYPO3\Flow\Persistence\PersistenceManagerInterface $persistenceManager
	 * @return void
	 */
	public function injectPersistenceManager(\TYPO3\Flow\Persistence\PersistenceManagerInterface $persistenceManager) {
		$this->persistenceManager = $persistenceManager;
	}

	/**
	 * Renders the output of this view helper
	 *
	 * @param object $object The persisted object
	 * @return string Identity
	 * @throws \TYPO3\Fluid\Exception
	 * @api
	 */
	public function render($object = NULL) {
		if ($object === NULL) {
			$object = $this->renderChildren();
		}
		if (!is_object($object)) {
			throw new \TYPO3\Fluid\Exception('f:identity.json expects an object, ' . \gettype($object) . ' given.', 1277830439);
		}
		$identifier = $this->persistenceManager->getIdentifierByObject($object);
		if ($identifier === NULL) {
			return '{}';
		} else {
			return json_encode(array('__identity' => $identifier));
		}
	}
}

?>