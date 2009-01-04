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
 * 
 * @scope prototype
 */
abstract class AbstractFormViewHelper extends \F3\Fluid\Core\TagBasedViewHelper {
	
	public function initializeArguments() {
		$this->registerTagAttribute('name', 'Name of input tag');
		$this->registerTagAttribute('value', 'string', 'Value of input tag');
		$this->registerArgument('property', 'string', 'Name of Object Property. Use in conjunction with <f3:form object="...">');
	}
	
	protected function evaluateProperty() {
		if ($this->arguments['property'] && $this->arguments['__formObject'] && $this->arguments['__formName']) {
			$this->arguments['name'] = $this->variableContainer->get('__formName') . '[' . $this->arguments['property'] . ']';
			$this->arguments['value'] = $this->getValue($this->variableContainer->get('__formObject'), $this->arguments['property']);
		}
	}
	
	private function getValue($object, $propertyName) {
		$methodName = 'get' . ucfirst($propertyName);
		return $object->$methodName;
	}
}

?>