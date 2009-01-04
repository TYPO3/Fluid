<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers\Form;

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
 * @version $Id:$
 */

/**
 * Textarea View Helper.
 *
 * @scope prototype
 */
class TextAreaViewHelper extends \F3\Fluid\ViewHelpers\Form\AbstractFormViewHelper {
	
	public function initializeArguments() {
		$this->registerTagAttribute('name', 'Name of input tag');
		$this->registerArgument('value', 'string', 'Value of input tag');
		$this->registerArgument('property', 'string', 'Name of Object Property. Use in conjunction with <f3:form object="...">');
		$this->registerUniversalTagAttributes();
	}
	public function render() {
		$this->evaluateProperty();
		$out = '<textarea ' . $this->renderTagAttributes() . '>';
		$out .= $this->arguments['value'];
		$out .= '</textarea>';
		return $out;
	}
}

?>