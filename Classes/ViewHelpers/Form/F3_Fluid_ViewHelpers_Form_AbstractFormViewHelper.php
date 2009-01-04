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
		$this->registerArgument('name', 'Name of input tag');
		$this->registerArgument('value', 'string', 'Value of input tag');
		$this->registerArgument('property', 'string', 'Name of Object Property. Use in conjunction with <f3:form object="...">');
	}
	
	private function getObjectValue($object, $propertyName) {
		$methodName = 'get' . ucfirst($propertyName);
		return $object->$methodName();
	}
	
	protected function getName() {
	    if ($this->isObjectAccessorMode()) {
		return $this->variableContainer->get('__formName') . '[' . $this->arguments['property'] . ']';
	    } else {
		return $this->arguments['name'];
	    }
	    return $this->name;
	}
	
	protected function getValue() {
	    if ($this->isObjectAccessorMode()) {
		return $this->getObjectValue($this->variableContainer->get('__formObject'), $this->arguments['property']);
	    } else {
		return $this->arguments['value'];
	    }
	}
	
	private function isObjectAccessorMode() {
	    return ($this->arguments['property'] && $this->variableContainer->exists('__formObject') && $this->variableContainer->exists('__formName'))?TRUE:FALSE; 
	}
}

?>