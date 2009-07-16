<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\ViewHelper;

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
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */
class ViewHelperVariableContainer {

	/**
	 * Two-dimensional object array storing the values. The first dimension is the fully qualified ViewHelper name,
	 * and the second dimension is the identifier for the data the ViewHelper wants to store.
	 * @var array
	 */
	protected $objects = array();

	/**
	 *
	 * @var F3\FLOW3\MVC\View\ViewInterface
	 * @internal
	 */
	protected $view;

	/**
	 * Add a variable to the Variable Container. Make sure that $viewHelperName is ALWAYS set
	 * to your fully qualified ViewHelper Class Name
	 *
	 * @param string $viewHelperName The ViewHelper Class name (Fully qualified, like F3\Fluid\ViewHelpers\ForViewHelper)
	 * @param string $key Key of the data
	 * @param object $value The value to store
	 * @return void
	 * @throws F3\Fluid\Core\RuntimeException if there was no key with the specified name
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @api
	 */
	public function add($viewHelperName, $key, $value) {
		if ($this->exists($viewHelperName, $key)) throw new \F3\Fluid\Core\RuntimeException('The key "' . $viewHelperName . '->' . $key . '" was already stored and you cannot override it.', 1243352010);
		if (!isset($this->objects[$viewHelperName])) {
			$this->objects[$viewHelperName] = array();
		}
		$this->objects[$viewHelperName][$key] = $value;
	}

	/**
	 * Gets a variable which is stored
	 *
	 * @param string $viewHelperName The ViewHelper Class name (Fully qualified, like F3\Fluid\ViewHelpers\ForViewHelper)
	 * @param string $key Key of the data
	 * @return object The object stored
	 * @throws F3\Fluid\Core\RuntimeException if there was no key with the specified name
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @api
	 */
	public function get($viewHelperName, $key) {
		if (!$this->exists($viewHelperName, $key)) throw new \F3\Fluid\Core\RuntimeException('No value found for key "' . $viewHelperName . '->' . $key . '"', 1243325768);
		return $this->objects[$viewHelperName][$key];
	}

	/**
	 * Determine whether there is a variable stored for the given key
	 *
	 * @param string $viewHelperName The ViewHelper Class name (Fully qualified, like F3\Fluid\ViewHelpers\ForViewHelper)
	 * @param string $key Key of the data
	 * @return boolean TRUE if a value for the given ViewHelperName / Key is stored, FALSE otherwise.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @api
	 */
	public function exists($viewHelperName, $key) {
		return isset($this->objects[$viewHelperName][$key]);
	}

	/**
	 * Remove a value from the variable container
	 *
	 * @param string $viewHelperName The ViewHelper Class name (Fully qualified, like F3\Fluid\ViewHelpers\ForViewHelper)
	 * @param string $key Key of the data to remove
	 * @return void
	 * @throws F3\Fluid\Core\RuntimeException if there was no key with the specified name
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @api
	 */
	public function remove($viewHelperName, $key) {
		if (!$this->exists($viewHelperName, $key)) throw new \F3\Fluid\Core\RuntimeException('No value found for key "' . $viewHelperName . '->' . $key . '", thus the key cannot be removed.', 1243352249);
		unset($this->objects[$viewHelperName][$key]);
	}

	/**
	 * Set the view to pass it to ViewHelpers.
	 *
	 * @param F3\FLOW3\MVC\View\ViewInterface $view View to set
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setView(\F3\FLOW3\MVC\View\ViewInterface $view) {
		$this->view = $view;
	}

	/**
	 * Get the view.
	 *
	 * !!! This is NOT a public API and might still change!!!
	 *
	 * @return F3\FLOW3\MVC\View\ViewInterface The View
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getView() {
		return $this->view;
	}

	/**
	 * Clean up for serializing.
	 *
	 * @return array
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function __sleep() {
		return array('objects');
	}
}
?>