<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers\Text;

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
 * Example:
 * <f3:text.crop >Some very long text</f3:text.crop>
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
class CropViewHelper extends \F3\Fluid\Core\AbstractViewHelper {

	/**
	 * Initialize arguments for this view helper
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function initializeArguments() {
		$this->registerArgument('maxCharacters', 'integer', 'Place where to truncate the string', TRUE);
		$this->registerArgument('append', 'string', 'What to append, if truncation happened. By Default, "..."', FALSE);
	}

	/**
	 * Render the cropped text
	 *
	 * @return string cropped text
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function render() {
		$numberOfCharacters = (int)$this->arguments['maxCharacters'];
		$stringToTruncate = $this->renderChildren();
		$whatToAppend = ($this->arguments['append'] ? $this->arguments['append'] : '...');

		if (strlen($stringToTruncate) > $numberOfCharacters) {
			return substr($stringToTruncate, 0, $numberOfCharacters) . $whatToAppend;
		} else {
			return $stringToTruncate;
		}
	}
}


?>