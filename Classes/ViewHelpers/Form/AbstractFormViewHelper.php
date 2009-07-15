<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers\Form;

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
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id$
 */

/**
 * Abstract Form View Helper. Bundles functionality related to direct property access of objects in other Form ViewHelpers.
 *
 * If you set the "property" attribute to the name of the property to resolve from the object, this class will
 * automatically set the name and value of a form element.
 *
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */
abstract class AbstractFormViewHelper extends \F3\Fluid\Core\ViewHelper\TagBasedViewHelper {

	/**
	 * @var \F3\FLOW3\Persistence\ManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * Injects the FLOW3 Persistence Manager
	 *
	 * @param \F3\FLOW3\Persistence\ManagerInterface $persistenceManager
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectPersistenceManager(\F3\FLOW3\Persistence\ManagerInterface $persistenceManager) {
		$this->persistenceManager = $persistenceManager;
	}

	/**
	 * Initialize arguments.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @api
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('name', 'string', 'Name of input tag');
		$this->registerArgument('value', 'mixed', 'Value of input tag');
		$this->registerArgument('property', 'string', 'Name of Object Property. If used in conjunction with <f3:form object="...">, "name" and "value" properties will be ignored.');
	}

	/**
	 * Get the name of this form element.
	 * Either returns arguments['name'], or the correct name for Object Access.
	 *
	 * @return string Name
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function getName() {
		$name = ($this->isObjectAccessorMode()) ? $this->viewHelperVariableContainer->get('F3\Fluid\ViewHelpers\FormViewHelper', 'formName') . '[' . $this->arguments['property'] . ']' : $this->arguments['name'];
		if (is_object($this->arguments['value']) && NULL !== $this->persistenceManager->getBackend()->getUUIDByObject($this->arguments['value'])
				&& ($this->arguments['value'] instanceof \F3\FLOW3\Persistence\Aspect\DirtyMonitoringInterface && !$this->arguments['value']->FLOW3_Persistence_isNew())) {
			$name .= '[__identity]';
		}
		return $name;
	}

	/**
	 * Get the value of this form element.
	 * Either returns arguments['value'], or the correct value for Object Access.
	 *
	 * @return string Value
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function getValue() {
		if ($this->isObjectAccessorMode() && $this->viewHelperVariableContainer->exists('F3\Fluid\ViewHelpers\FormViewHelper', 'formObject') && ($this->arguments['value'] === NULL)) {
			$value = $this->getObjectValue($this->viewHelperVariableContainer->get('F3\Fluid\ViewHelpers\FormViewHelper', 'formObject'), $this->arguments['property']);
		} else {
			$value =  $this->arguments['value'];
		}
		if (is_object($value)) {
			$uuid = $this->persistenceManager->getBackend()->getUUIDByObject($value);
			if ($uuid !== NULL) {
				$value = $uuid;
			}
		}
		return $value;
	}

	/**
	 * Internal method which checks if we should evaluate a domain object or just output arguments['name'] and arguments['value']
	 *
	 * @return boolean TRUE if we should evaluate the domain object, FALSE otherwise.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function isObjectAccessorMode() {
		return (($this->arguments['property'] !== NULL) && $this->viewHelperVariableContainer->exists('F3\Fluid\ViewHelpers\FormViewHelper', 'formName')) ? TRUE : FALSE;
	}

	/**
	 * Get object value. Calls the appropriate getter.
	 *
	 * @param object $object Object to get the value from
	 * @param string $propertyName Name of property to get.
	 * @todo replace with something generic.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	private function getObjectValue($object, $propertyName) {
		$getterMethodName = 'get' . ucfirst($propertyName);
		return $object->$getterMethodName();
	}

	/**
	 * Get errors for the property and form name of this view helper
	 *
	 * @return array An array of F3\FLOW3\Error\Error objects
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	protected function getErrorsForProperty() {
		$errors = $this->controllerContext->getRequest()->getErrors();
		$formName = $this->viewHelperVariableContainer->get('F3\Fluid\ViewHelpers\FormViewHelper', 'formName');

		if ($this->arguments->hasArgument('property')) {
			$propertyName = $this->arguments['property'];
			$formErrors = array();
			foreach ($errors as $error) {
				if ($error instanceof \F3\FLOW3\Validation\PropertyError && $error->getPropertyName() == $formName) {
					$formErrors = $error->getErrors();
					foreach ($formErrors as $formError) {
						if ($formError instanceof \F3\FLOW3\Validation\PropertyError && $formError->getPropertyName() == $propertyName) {
							return $formError->getErrors();
						}
					}
				}
			}
		}
		return array();
	}
}

?>