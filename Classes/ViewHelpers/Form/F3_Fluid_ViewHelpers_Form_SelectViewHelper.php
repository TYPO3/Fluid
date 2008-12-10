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
 * This view helper generates a <select> dropdown list for the use with a form.
 * 
 * Example:
 * (1) Basic usage
 * 
 * <f3:form.select name="paymentOptions" options="{payPal: 'PayPal International Services', visa: 'VISA Card'}" />
 * Generates a dropdown with two options. The array key is used as key, and the value is used as human-readable name.
 * 
 * 
 * (2) Pre-select a value
 * 
 * <f3:form.select name="paymentOptions" options="{payPal: 'PayPal International Services', visa: 'VISA Card'}" selectedValue="visa" />
 * Generates a dropdown box like above, except that "VISA Card" is selected.
 * 
 * 
 * (3) Usage on domain objects
 * 
 * <f3:form.select name="users" options="{userArray}" optionKey="id" optionValue="firstName" />
 * In the above example, the userArray is an array of "User" domain objects, with no array key specified.
 * If the optionKey variable is set, the getter named after that value is used to retrieve the option key.
 * If the optionValue variable is set, the getter named after that value is used to retrieve the option key.
 * 
 * So, in the above example, the method $user->getId() is called to retrieve the key, and $user->getFirstName() to retrieve
 * the displayed value of each entry.
 * 
 * The "selectedValue" property now expects a domain object, and tests for object equivalence.
 * 
 * @scope prototype
 */
class SelectViewHelper extends \F3\Fluid\Core\TagBasedViewHelper {
	
	/**
	 * Initialize arguments.
	 * 
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function initializeArguments() {
		$this->registerUniversalTagAttributes();
		$this->registerArgument('options', 'array', 'Associative array with internal IDs as key, and the values are displayed in the select box', TRUE);
		$this->registerArgument('selectedValue', 'string', 'Selected key', FALSE);
		$this->registerArgument('optionKey', 'string', 'If specified, will call the appropriate getter on each object to determine the key.', FALSE);
		$this->registerArgument('optionValue', 'string', 'If specified, will call the appropriate getter on each object to determine the value.', FALSE);
		$this->registerTagAttribute('name', 'Name of select box', TRUE);
	}
	
	/**
	 * Render the tag.
	 * 
	 * @return string rendered tag.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @todo HTMLspecialchar output
	 */
	public function render() {
		$out = '<select ' . $this->renderTagAttributes() . '>';
		
		$selectedValue = NULL;
		if ($this->arguments['selectedValue']) {
			$selectedValue = $this->arguments['selectedValue'];
		}
		
		if ($this->arguments['options']) {
			if ($this->arguments['optionKey']) {
				foreach ($this->arguments['options'] as $domainObject) {
					$key = $this->callSpecifiedProperty($domainObject, $this->arguments['optionKey']);
				
					$selected = '';
					if ($domainObject == $selectedValue) {
						$selected = 'selected="selected"';
					}
					
					$value = $this->callSpecifiedProperty($domainObject, $this->arguments['optionValue']);
					
					$out .= '<option ' . $selected . ' value="' . $key . '">' . $value . '</option>';
				}			
			} else {
				foreach ($this->arguments['options'] as $key => $value) {
					$selected = '';
					if ($key == $selectedValue) {
						$selected = 'selected="selected"';
					}
					$out .= '<option ' . $selected . ' value="' . $key . '">' . $value . '</option>';
				}
			}
		}
		
		$out .= '</select>';
		
		return $out;
	}
	
	/**
	 * Helper which converts $name in something like get$name, and then calls this method on the given $object.
	 * Returns the result.
	 * 
	 * @param object $object an object with some getters
	 * @param string $name The name of the property. The first character will be capitalized and the right getter called.
	 * @return object any object returned by the getter
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function callSpecifiedProperty($object, $name) {
		if (!is_object($object)) throw new \F3\Fluid\RuntimeException('SelectViewHelper expects a list of objects if you specify the "optionKey" argument.', 1227711174);
		
		$getterMethodName = 'get' . \F3\PHP6\Functions::ucfirst($name);
		if (method_exists($object, $getterMethodName)) {
			return call_user_func(array($object, $getterMethodName));
		} else {
			throw new \F3\Fluid\RuntimeException('Tried to call "' . $getterMethodName . '" on an object in the select view helper but method does not exist.', 1227711306);
		}
	}
}

?>
