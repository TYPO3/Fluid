<?php
namespace TYPO3\Fluid\ViewHelpers;

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
 * This ViewHelper prevents rendering of any content inside the tag
 * Note: Contents of the comment will still be *parsed* thus throwing an
 * Exception if it contains syntax errors. You can put child nodes in
 * CDATA tags to avoid this.
 *
 * = Examples =
 *
 * <code title="Commenting out fluid code">
 * Before
 * <f:comment>
 *   This is completely hidden.
 *   <f:debug>This does not get parsed</f:debug>
 * </f:comment>
 * After
 * </code>
 * <output>
 * Before
 * After
 * </output>
 *
 * <code title="Prevent parsing">
 * <f:comment><![CDATA[
 *  <f:some.invalid.syntax />
 * ]]></f:comment>
 * </code>
 * <output>
 * </output>
 *
 * @api
 */
class CommentViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var boolean
	 */
	protected $escapingInterceptorEnabled = FALSE;

	/**
	 * Comments out the tag content
	 *
	 * @return string
	 * @author Claus Due <claus@wildside.dk>
	 * @api
	 */
	public function render() {
	}
}

?>
