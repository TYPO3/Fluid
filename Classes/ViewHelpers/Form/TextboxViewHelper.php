<?php
namespace TYPO3\Fluid\ViewHelpers\Form;

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
 * DEPRECATED: Use <f:form.textfield> instead!
 *
 * View Helper which creates a simple Text Box (<input type="text">).
 *
 * = Examples =
 *
 * <code title="Example">
 * <f:form.textbox name="myTextBox" value="default value" />
 * </code>
 * <output>
 * <input type="text" name="myTextBox" value="default value" />
 * </output>
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 * @deprecated since 1.0.0 alpha 7
 */
class TextboxViewHelper extends \TYPO3\Fluid\ViewHelpers\Form\TextfieldViewHelper {
	// BACKPORTER-TOKEN-1
}

?>