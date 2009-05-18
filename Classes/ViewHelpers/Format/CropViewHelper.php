<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers\Format;

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
 * Use this view helper to crop the text between its opening and closing tags.
 *
 * = Examples =
 * 
 * <code title="Defaults">
 * <f:format.crop maxCharacters="10">This is some very long text</f:format.crop>
 * </code>
 *
 * Output:
 * This is so...
 * 
 * <code title="Custom suffix">
 * <f:format.crop maxCharacters="17" append=" [more]">This is some very long text</f:format.crop>
 * </code>
 * 
 * Output:
 * This is some very [more]
 *
 * WARNING: This tag does NOT handle tags currently.
 * WARNING: This tag doesn't care about multibyte charsets currently.
 *
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
class CropViewHelper extends \F3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Render the cropped text
	 *
	 * @param integer $maxCharacters Place where to truncate the string
	 * @param string $append What to append, if truncation happened
	 * @return string cropped text
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function render($maxCharacters, $append = '...') {
		$stringToTruncate = $this->renderChildren();

		if (strlen($stringToTruncate) > $maxCharacters) {
			return substr($stringToTruncate, 0, $maxCharacters) . $append;
		} else {
			return $stringToTruncate;
		}
	}
}


?>