<?php
declare(ENCODING = 'utf-8');
namespace F3::Beer3;

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
 * @package Beer3
 * @version $Id:$
 */
/**
 * Context
 *
 * @package Beer3
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
class Context {
	/**
	 * Objects stored in context
	 * @var array
	 */
	protected $objects = array();
	
	/**
	 * Add an object to the context
	 *
	 * @param string $identifier
	 * @param object $object
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function add($identifier, $object) {
		if (array_key_exists($identifier, $this->objects)) throw new F3::Beer3::Exception('Duplicate variable declarations!', 1224479063);
		$this->objects[$identifier] = $object;
	}
	
	/**
	 * Get an object from the context. Throws exception if object is not found in context.
	 *
	 * @param string $identifier
	 * @return object The object identified by $identifier
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function get($identifier) {
		if (!array_key_exists($identifier, $this->objects)) throw new F3::Beer3::Exception('Tried to get a variable which is not stored in the context!', 1224479370);
		return $this->objects[$identifier];
	}
	
	/**
	 * Remove an object from context. Throws exception if object is not found in context.
	 *
	 * @param string $identifier The identifier to remove
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function remove($identifier) {
		if (!array_key_exists($identifier, $this->objects)) throw new F3::Beer3::Exception('Tried to remove a variable which is not stored in the context!', 1224479372);
		unset($this->objects[$identifier]);
	}
}

?>