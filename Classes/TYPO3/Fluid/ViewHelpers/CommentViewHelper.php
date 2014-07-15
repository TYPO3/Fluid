<?php
namespace TYPO3\Fluid\ViewHelpers;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * This ViewHelper prevents rendering of any content inside the tag
 * Note: Contents of the comment will still be **parsed** thus throwing an
 * Exception if it contains syntax errors. You can put child nodes in
 * CDATA tags to avoid this.
 *
 * = Examples =
 *
 * <code title="Commenting out fluid code">
 * Before
 * <f:comment>
 *   This is completely hidden.
 *   <f:debug>This does not get rendered</f:debug>
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
 * Note: Using this view helper won't have a notable effect on performance, especially once the template is parsed.
 * However it can lead to reduced readability. You can use layouts and partials to split a large template into smaller
 * parts. Using self-descriptive names for the partials can make comments redundant.
 *
 * @api
 */
class CommentViewHelper extends AbstractViewHelper {

	/**
	 * @var boolean
	 */
	protected $escapingInterceptorEnabled = FALSE;

	/**
	 * Comments out the tag content
	 *
	 * @return string
	 * @api
	 */
	public function render() {
	}
}
