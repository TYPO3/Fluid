<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers\Exp;

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
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id: ForViewHelper.php 2378 2009-05-25 20:47:00Z sebastian $
 */

/**
 * an "Equals" expression. THIS IS CURRENTLY AN EXPERIMENTAL ViewHelper AND SUBJECT TO CHANGE!
 *
 * Use at your own risk!
 *
 * @internal
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id: ForViewHelper.php 2378 2009-05-25 20:47:00Z sebastian $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */
class EqualsViewHelper extends \F3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Compares two elements
	 *
	 * @param mixed $left First parameter to compare
	 * @param mixed $right Second parameter to compare
	 * @return boolean TRUE if $left and $right are equal
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @internal
	 */
	public function render($left, $right) {
		return ($left === $right);
	}
}

?>
