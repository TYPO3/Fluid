<?php
namespace TYPO3\Fluid\ViewHelpers\Form;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Error\Result;
use TYPO3\Flow\Mvc\ActionRequest;
use TYPO3\Flow\Reflection\ObjectAccess;

/**
 * Abstract Form View Helper. Bundles functionality related to direct property access of objects in other Form ViewHelpers.
 *
 * If you set the "property" attribute to the name of the property to resolve from the object, this class will
 * automatically set the name and value of a form element.
 *
 * @api
 */
abstract class AbstractFormFieldViewHelper extends AbstractFormViewHelper {

	/**
	 * @return void
	 * @api
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('name', 'string', 'Name of input tag');
		$this->registerArgument('value', 'mixed', 'Value of input tag');
		$this->registerArgument('property', 'string', 'Name of Object Property. If used in conjunction with <f:form object="...">, "name" and "value" properties will be ignored.');
	}

	/**
	 * Get the name of this form element.
	 * Either returns arguments['name'], or the correct name for Object Access.
	 *
	 * In case property is something like bla.blubb (hierarchical), then [bla][blubb] is generated.
	 *
	 * @return string Name
	 */
	protected function getName() {
		$name = $this->getNameWithoutPrefix();
		return $this->prefixFieldName($name);
	}

	/**
	 * Shortcut for retrieving the request from the controller context
	 *
	 * @return ActionRequest
	 */
	protected function getRequest() {
		return $this->controllerContext->getRequest();
	}

	/**
	 * Get the name of this form element, without prefix.
	 *
	 * @return string name
	 */
	protected function getNameWithoutPrefix() {
		if ($this->isObjectAccessorMode()) {
			$propertySegments = explode('.', $this->arguments['property']);
			$formObjectName = $this->viewHelperVariableContainer->get('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName');
			if (!empty($formObjectName)) {
				array_unshift($propertySegments, $formObjectName);
			}
			$name = array_shift($propertySegments);
			foreach ($propertySegments as $segment) {
				$name .= '[' . $segment . ']';
			}
		} else {
			$name = $this->arguments['name'];
		}
		if ($this->hasArgument('value') && is_object($this->arguments['value'])) {
			if (NULL !== $this->persistenceManager->getIdentifierByObject($this->arguments['value'])
				&& (!$this->persistenceManager->isNewObject($this->arguments['value']))) {
				$name .= '[__identity]';
			}
		}

		return $name;
	}

	/**
	 * Get the value of this form element.
	 * Either returns arguments['value'], or the correct value for Object Access.
	 *
	 * @param boolean $convertObjects whether or not to convert objects to identifiers
	 * @return mixed Value
	 * @deprecated since Flow 3.0. Use getValueAttribute() and (if applicable) addAdditionalIdentityPropertiesIfNeeded()
	 */
	protected function getValue($convertObjects = TRUE) {
		$value = NULL;

		if ($this->hasArgument('value')) {
			$value = $this->arguments['value'];
		} elseif ($this->isObjectAccessorMode()) {
			if ($this->hasMappingErrorOccurred()) {
				$value = $this->getLastSubmittedFormData();
			} else {
				$value = $this->getPropertyValue();
			}
			$this->addAdditionalIdentityPropertiesIfNeeded();
		}

		if ($convertObjects && is_object($value)) {
			$identifier = $this->persistenceManager->getIdentifierByObject($value);
			if ($identifier !== NULL) {
				$value = $identifier;
			}
		}
		return $value;
	}

	/**
	 * Returns the current value of this Form ViewHelper and converts it to an identifier string in case it's an object
	 * The value is determined as follows:
	 * * If property mapping errors occurred and the form is re-displayed, the *last submitted* value is returned
	 * * Else the bound property is returned (only in objectAccessor-mode)
	 * * As fallback the "value" argument of this ViewHelper is used
	 *
	 * @param boolean $ignoreSubmittedFormData By default the submitted form value has precedence over value/property argument upon re-display. With this flag set the submitted data is not evaluated (e.g. for checkbox and hidden fields where the value attribute should not be changed)
	 * @return mixed Value
	 */
	protected function getValueAttribute($ignoreSubmittedFormData = FALSE) {
		$value = NULL;
		$submittedFormData = NULL;
		if (!$ignoreSubmittedFormData && $this->hasMappingErrorOccurred()) {
			$submittedFormData = $this->getLastSubmittedFormData();
		}
		if ($submittedFormData !== NULL) {
			$value = $submittedFormData;
		} elseif ($this->hasArgument('value')) {
			$value = $this->arguments['value'];
		} elseif ($this->isObjectAccessorMode()) {
			$value = $this->getPropertyValue();
		}
		if (is_object($value)) {
			$value = $this->persistenceManager->getIdentifierByObject($value);
		}
		return $value;
	}

	/**
	 * @return boolean TRUE if a mapping error occurred, FALSE otherwise
	 * @deprecated since 2.1 Use hasMappingErrorOccurred() instead
	 */
	protected function hasMappingErrorOccured() {
		return $this->hasMappingErrorOccurred();
	}

	/**
	 * Checks if a property mapping error has occurred in the last request.
	 *
	 * @return boolean TRUE if a mapping error occurred, FALSE otherwise
	 */
	protected function hasMappingErrorOccurred() {
		/** @var $validationResults Result */
		$validationResults = $this->getRequest()->getInternalArgument('__submittedArgumentValidationResults');
		return ($validationResults !== NULL && $validationResults->hasErrors());
	}

	/**
	 * Get the form data which has last been submitted; only returns valid data in case
	 * a property mapping error has occurred. Check with hasMappingErrorOccurred() before!
	 *
	 * @return mixed
	 */
	protected function getLastSubmittedFormData() {
		$submittedArguments = $this->getRequest()->getInternalArgument('__submittedArguments');
		if ($submittedArguments === NULL) {
			return;
		}
		return ObjectAccess::getPropertyPath($submittedArguments, $this->getPropertyPath());
	}

	/**
	 * Add additional identity properties in case the current property is hierarchical (of the form "bla.blubb").
	 * Then, [bla][__identity] has to be generated as well.
	 *
	 * @return void
	 */
	protected function addAdditionalIdentityPropertiesIfNeeded() {
		if (!$this->isObjectAccessorMode()) {
			return;
		}
		if (!$this->viewHelperVariableContainer->exists('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject')) {
			return;
		}
		$propertySegments = explode('.', $this->arguments['property']);
		// hierarchical property. If there is no "." inside (thus $propertySegments == 1), we do not need to do anything
		if (count($propertySegments) < 2) {
			return;
		}
		$formObject = $this->viewHelperVariableContainer->get('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject');
		$objectName = $this->viewHelperVariableContainer->get('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName');

		// If count == 2 -> we need to go through the for-loop exactly once
		for ($i = 1; $i < count($propertySegments); $i++) {
			$object = ObjectAccess::getPropertyPath($formObject, implode('.', array_slice($propertySegments, 0, $i)));
			$objectName .= '[' . $propertySegments[$i - 1] . ']';
			$hiddenIdentityField = $this->renderHiddenIdentityField($object, $objectName);

			// Add the hidden identity field to the ViewHelperVariableContainer
			$additionalIdentityProperties = $this->viewHelperVariableContainer->get('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'additionalIdentityProperties');
			$additionalIdentityProperties[$objectName] = $hiddenIdentityField;
			$this->viewHelperVariableContainer->addOrUpdate('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'additionalIdentityProperties', $additionalIdentityProperties);
		}
	}

	/**
	 * Get the current property of the object bound to this form.
	 *
	 * @return mixed Value
	 */
	protected function getPropertyValue() {
		if (!$this->viewHelperVariableContainer->exists('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject')) {
			return NULL;
		}
		$formObject = $this->viewHelperVariableContainer->get('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject');
		$propertyNameOrPath = $this->arguments['property'];
		return ObjectAccess::getPropertyPath($formObject, $propertyNameOrPath);
	}

	/**
	 * Returns the "absolute" property path of the property bound to this ViewHelper.
	 * For <f:form... property="foo.bar" /> this will be "<formObjectName>.foo.bar"
	 * For <f:form... name="foo[bar][baz]" /> this will be "foo.bar.baz"
	 *
	 * @return string
	 */
	protected function getPropertyPath() {
		if ($this->isObjectAccessorMode()) {
			$formObjectName = $this->viewHelperVariableContainer->get('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName');
			if (strlen($formObjectName) === 0) {
				return $this->arguments['property'];
			} else {
				return $formObjectName . '.' . $this->arguments['property'];
			}
		}
		return rtrim(preg_replace('/(\]\[|\[|\])/', '.', $this->getNameWithoutPrefix()), '.');
	}

	/**
	 * Internal method which checks if we should evaluate a domain object or just output arguments['name'] and arguments['value']
	 *
	 * @return boolean TRUE if we should evaluate the domain object, FALSE otherwise.
	 */
	protected function isObjectAccessorMode() {
		return $this->hasArgument('property')
			&& $this->viewHelperVariableContainer->exists('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName');
	}

	/**
	 * Add an CSS class if this view helper has errors
	 *
	 * @return void
	 */
	protected function setErrorClassAttribute() {
		if ($this->hasArgument('class')) {
			$cssClass = $this->arguments['class'] . ' ';
		} else {
			$cssClass = '';
		}
		$mappingResultsForProperty = $this->getMappingResultsForProperty();
		if ($mappingResultsForProperty->hasErrors()) {
			if ($this->hasArgument('errorClass')) {
				$cssClass .= $this->arguments['errorClass'];
			} else {
				$cssClass .= 'error';
			}
			$this->tag->addAttribute('class', $cssClass);
		}
	}

	/**
	 * Get errors for the property and form name of this view helper
	 *
	 * @return Result
	 */
	protected function getMappingResultsForProperty() {
		/** @var $validationResults Result */
		$validationResults = $this->getRequest()->getInternalArgument('__submittedArgumentValidationResults');
		if ($validationResults === NULL) {
			return new Result();
		}
		return $validationResults->forProperty($this->getPropertyPath());
	}

	/**
	 * Renders a hidden field with the same name as the element, to make sure the empty value is submitted
	 * in case nothing is selected. This is needed for checkbox and multiple select fields
	 *
	 * @return void
	 */
	protected function renderHiddenFieldForEmptyValue() {
		$emptyHiddenFieldNames = array();
		if ($this->viewHelperVariableContainer->exists('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'emptyHiddenFieldNames')) {
			$emptyHiddenFieldNames = $this->viewHelperVariableContainer->get('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'emptyHiddenFieldNames');
		}

		$fieldName = $this->getName();
		if (substr($fieldName, -2) === '[]') {
			$fieldName = substr($fieldName, 0, -2);
		}
		if (!in_array($fieldName, $emptyHiddenFieldNames)) {
			$emptyHiddenFieldNames[] = $fieldName;
			$this->viewHelperVariableContainer->addOrUpdate('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'emptyHiddenFieldNames', $emptyHiddenFieldNames);
		}
	}
}
