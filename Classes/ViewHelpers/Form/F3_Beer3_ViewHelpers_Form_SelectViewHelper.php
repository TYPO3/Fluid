<?php
declare(ENCODING = 'utf-8');
namespace F3::Beer3::ViewHelpers::Form;

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
 * @package 
 * @subpackage 
 * @version $Id:$
 */
/**
 * Enter description here...
 * @scope prototype
 */
class SelectViewHelper extends F3::Beer3::Core::TagBasedViewHelper {
	
	public function initializeArguments() {
		$this->registerUniversalTagAttributes();
		$this->registerArgument('options', 'array', 'Associative array with internal IDs as key, and the values are displayed in the select box', TRUE);
		$this->registerArgument('selectedValue', 'string', 'Selected key', FALSE);
		$this->registerTagAttribute('name', 'Name of select box', TRUE);
	}
	public function render() {
		$out = '<select ' . $this->renderTagAttributes() . '>';
		
		$selectedValue = NULL;
		if ($this->arguments['selectedValue']) {
			$selectedValue = $this->arguments['selectedValue'];
		}
		
		foreach ($this->arguments['options'] as $key => $value) {
			$selected = '';
			if ($key == $selectedValue) {
				$selected = 'selected="selected"';
			}
			$out .= '<option ' . $selected . ' value="' . $key . '">' . $value . '</option>';
		}
		
		$out .= '</select>';
		
		return $out;
	}
}

?>