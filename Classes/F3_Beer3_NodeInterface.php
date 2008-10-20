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
 * @package 
 * @subpackage 
 * @version $Id:$
 */
/**
 * Node interface - available to all ViewHelpers.
 *
 * @package
 * @subpackage
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
interface NodeInterface {
	/**
	 * Render the whole subtree and return the rendered result string.
	 *
	 * @return string Rendered representation
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function renderSubtree();
	
	/**
	 * Evaluate the whole subtree and return the evaluated results.
	 * 
	 * @return object Normally, an object is returned - in case it is concatenated with a string, a string is returned.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function evaluateSubtree();
	
	/**
	 * Add an object identified by $key to context
	 *
	 * @param string $key Object identifier
	 * @param object $value Object itself
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function addToContext($key, $value);
	
	/**
	 * Removes an object identified by $key from context
	 *
	 * @param string $key Object identifier to remove
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function removeFromContext($key);
}


?>