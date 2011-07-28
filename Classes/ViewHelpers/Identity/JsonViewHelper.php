<?php
namespace TYPO3\Fluid\ViewHelpers\Identity;

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
 * Renders the identity of a persisted object (if it has an identity).
 * Useful for using the identity outside of the form view helpers
 * (e.g. JavaScript and AJAX).
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
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @scope prototype
 */
class JsonViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var boolean
	 */
	protected $escapingInterceptorEnabled = FALSE;

	/**
	 * @var \F3\FLOW3\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * Injects the FLOW3 Persistence Manager
	 *
	 * @param \TYPO3\FLOW3\Persistence\PersistenceManagerInterface $persistenceManager
	 * @return void
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function injectPersistenceManager(\TYPO3\FLOW3\Persistence\PersistenceManagerInterface $persistenceManager) {
		$this->persistenceManager = $persistenceManager;
	}

	/**
	 * Renders the output of this view helper
	 *
	 * @param object $object The persisted object
	 * @return string Identity
	 * @author Christopher Hlubek <hlubek@networkteam.com>
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