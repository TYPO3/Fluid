<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid;

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
 * Settings class which is a holder for constants specific to Fluid on v5 and v4.
 *
 * @version $Id$
 */
class Fluid {
	/**
	 * PHP Namespace separator. Backslash in v5, and _ in v4.
	 */
	const NAMESPACE_SEPARATOR = '\\';

	/**
	 * Can be used to enable the verbose mode of Fluid.
	 *
	 * This enables the following things:
	 * - ViewHelper argument descriptions are being parsed from the PHPDoc
	 *
	 * This is NO PUBLIC API and the way this mode is enabled might change without
	 * notice in the future.
	 * @var boolean
	 */
	public static $debugMode = FALSE;

}


?>