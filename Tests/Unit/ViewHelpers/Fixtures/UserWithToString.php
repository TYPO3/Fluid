<?php
namespace TYPO3\Fluid\ViewHelpers\Fixtures;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Dummy object to test Viewhelper behavior on objects with and without a __toString method
 */
class UserWithToString extends UserWithoutToString {

	/**
	 * @return string
	 */
	function __toString() {
		return $this->name;
	}
}