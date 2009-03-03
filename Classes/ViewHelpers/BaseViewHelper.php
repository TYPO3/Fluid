<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers;

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
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id$
 */

/**
 * Vew helper which creates a <base href="..."/> tag.
 *
 * Example:
 * <f3:base />
 * Generates a <base href="..." /> tag.
 *
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
class BaseViewHelper extends \F3\Fluid\Core\AbstractViewHelper {

	/**
	 * The Base view helper does not take any arguments.
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function initializeArguments() {
	}

	/**
	 * Render the "Base" tag by outputting $request->getBaseURI()
	 *
	 * Note: renders as <base></base>, because IE6 will else refuse to display
	 * the page...
	 *
	 * @return string "base"-Tag.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function render() {
		$currentRequest = $this->variableContainer->get('view')->getRequest();
		return '<base href="' . $currentRequest->getBaseURI() . '"></base>';
	}
}

?>
