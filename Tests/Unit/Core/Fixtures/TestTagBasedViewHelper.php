<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\Fixtures;

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
 * @version $Id$
 */
/**
 * [Enter description here]
 *
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class TestTagBasedViewHelper extends \F3\Fluid\Core\ViewHelper\TagBasedViewHelper {

	/**
	 * Check tag attribute registration
	 */
	public function registerTagAttribute($name, $type, $description, $required = FALSE) {
		parent::registerTagAttribute($name, $type, $description, $required);
	}

	public function initializeArguments() {
		$this->registerUniversalTagAttributes();
	}

	/**
	 * Render the tag attributes registered
	 */
	public function render() {
	}
}


?>
