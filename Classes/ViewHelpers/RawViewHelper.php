<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers;

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
 * Outputs an argument without any escaping. Is normally used to output
 * an ObjectAccessor which should not be escaped, but output as-is.
 *
 * PAY SPECIAL ATTENTION TO SECURITY HERE (especially Cross Site Scripting), as the output
 * is NOT SANITIZED!
 *
 * Backgroud: If you use an ObjectAccessor inside the template, it is automatically escaped.
 * If you use an object accessor as an argument for a ViewHelper, it is NOT escaped.
 *
 * = Examples =
 *
 * <code title="Simple escaping">
 * <f:raw value="{string}" />
 * {f:raw(value: string)}
 * </code>
 * <output>
 * (String without any conversion/escaping)
 * </output>
 *
 *
 * @version $Id: LayoutViewHelper.php 3751 2010-01-22 15:56:47Z k-fish $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @scope prototype
 * @todo refine documentation
 */
class RawViewHelper extends \F3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 *
	 * @param mixed $value The value to output
	 * @return string HTML
	 */
	public function render($value) {
		return $value;
	}
}


?>
