<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers\Format;

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
 * @version $Id$
 */

/**
 * A view helper for escaping HTML. Any HTML character in the body of this tag will
 * be escaped to an HTML entity.
 *
 * = Examples =
 *
 * <code title="Example">
 * <f:format.htmlEscape><p>This will be <em>escaped</em></p></f:format.htmlEscape>
 * </code>
 *
 * Output:
 * &lt;p&gt;This will be &lt;em&gt;escaped&lt;/em&gt;&lt;/p&gt;
 *
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */
class HtmlEscapeViewHelper extends \F3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * HTML escape the content of this tag.
	 *
	 * @return string The HTML escaped body.
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function render() {
		return htmlspecialchars($this->renderChildren());
	}
}
?>