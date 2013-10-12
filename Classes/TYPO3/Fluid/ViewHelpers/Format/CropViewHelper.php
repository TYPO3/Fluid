<?php
namespace TYPO3\Fluid\ViewHelpers\Format;

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
 * Use this view helper to crop the text between its opening and closing tags.
 *
 * = Examples =
 *
 * <code title="Defaults">
 * <f:format.crop maxCharacters="10">This is some very long text</f:format.crop>
 * </code>
 * <output>
 * This is so...
 * </output>
 *
 * <code title="Custom suffix">
 * <f:format.crop maxCharacters="17" append=" [more]">This is some very long text</f:format.crop>
 * </code>
 * <output>
 * This is some very [more]
 * </output>
 *
 * <code title="Inline notation">
 * <span title="Location: {user.city -> f:format.crop(maxCharacters: '12')}">John Doe</span>
 * </code>
 * <output>
 * <span title="Location: Newtownmount...">John Doe</span>
 * </output>
 *
 * WARNING: This tag does NOT handle tags currently.
 * WARNING: This tag doesn't care about multibyte charsets currently.
 *
 * @api
 */
class CropViewHelper extends AbstractViewHelper {

	/**
	 * Render the cropped text
	 *
	 * @param integer $maxCharacters Place where to truncate the string
	 * @param string $append What to append, if truncation happened
	 * @param string $value The input value which should be cropped. If not set, the evaluated contents of the child nodes will be used
	 * @return string cropped text
	 * @api
	 */
	public function render($maxCharacters, $append = '...', $value = NULL) {
		if ($value === NULL) {
			$value = $this->renderChildren();
		}

		if (strlen($value) > $maxCharacters) {
			return substr($value, 0, $maxCharacters) . $append;
		} else {
			return $value;
		}
	}
}
