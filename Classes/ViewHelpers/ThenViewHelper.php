<?php
namespace TYPO3\Fluid\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * "THEN" -> only has an effect inside of "IF". See If-ViewHelper for documentation.
 * @see TYPO3\Fluid\ViewHelpers\IfViewHelper
 *
 * @api
 * @FLOW3\Scope("prototype")
 */
class ThenViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Just render everything.
	 *
	 * @return string the rendered string
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @api
	 */
	public function render() {
		return $this->renderChildren();
	}
}

?>
