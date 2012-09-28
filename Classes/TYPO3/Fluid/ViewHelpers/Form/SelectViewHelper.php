<?php
namespace TYPO3\Fluid\ViewHelpers\Form;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * This view helper generates a <select> dropdown list for the use with a form.
 *
 * = Basic usage =
 *
 * The most straightforward way is to supply an associative array as the "options" parameter.
 * The array key is used as option key, and the value is used as human-readable name.
 *
 * <code title="Basic usage">
 * <f:form.select name="paymentOptions" options="{payPal: 'PayPal International Services', visa: 'VISA Card'}" />
 * </code>
 *
 * = Pre-select a value =
 *
 * To pre-select a value, set "value" to the option key which should be selected.
 * <code title="Default value">
 * <f:form.select name="paymentOptions" options="{payPal: 'PayPal International Services', visa: 'VISA Card'}" value="visa" />
 * </code>
 * Generates a dropdown box like above, except that "VISA Card" is selected.
 *
 * If the select box is a multi-select box (multiple="true"), then "value" can be an array as well.
 *
 * = Usage on domain objects =
 *
 * If you want to output domain objects, you can just pass them as array into the "options" parameter.
 * To define what domain object value should be used as option key, use the "optionValueField" variable. Same goes for optionLabelField.
 * If neither is given, the Identifier (UUID/uid) and the __toString() method are tried as fallbacks.
 *
 * If the optionValueField variable is set, the getter named after that value is used to retrieve the option key.
 * If the optionLabelField variable is set, the getter named after that value is used to retrieve the option value.
 *
 * <code title="Domain objects">
 * <f:form.select name="users" options="{userArray}" optionValueField="id" optionLabelField="firstName" />
 * </code>
 * In the above example, the userArray is an array of "User" domain objects, with no array key specified.
 *
 * So, in the above example, the method $user->getId() is called to retrieve the key, and $user->getFirstName() to retrieve the displayed value of each entry.
 *
 * The "value" property now expects a domain object, and tests for object equivalence.
 *
 * = Translation of select content =
 *
 * The view helper can be given a "translate" argument with configuration on how to translate option labels.
 * The array can have the following keys:
 * - "by" defines if translation by message id or original label is to be used ("id" or "label")
 * - "using" defines if the option tag's "value" or "label" should be used as translation input, defaults to "value"
 * - "locale" defines the locale identifier to use, optional, defaults to current locale
 * - "source" defines the translation source name, optional, defaults to "Main"
 * - "package" defines the package key of the translation source, optional, defaults to current package
 * - "prefix" defines a prefix to use for the message id – only works in combination with "by id"
 *
 * <code title="Label translation">
 * <f:form.select name="paymentOption" options="{payPal: 'PayPal International Services', visa: 'VISA Card'}" translate="{by: 'id'}" />
 * </code>
 *
 * The above example would use the values "payPal" and "visa" to look up translations for those ids in the current package's "Main" XLIFF file.
 *
 * <code title="Label translation">
 * <f:form.select name="paymentOption" options="{payPal: 'PayPal International Services', visa: 'VISA Card'}" translate="{by: 'id', prefix: 'shop.paymentOptions.'}" />
 * </code>
 *
 * The above example would use the translation ids "shop.paymentOptions.payPal" and "shop.paymentOptions.visa" for translating the labels.
 *
 * @api
 */
class SelectViewHelper extends \TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\I18n\Translator
	 */
	protected $translator;

	/**
	 * @var string
	 */
	protected $tagName = 'select';

	/**
	 * @var mixed
	 */
	protected $selectedValue = NULL;

	/**
	 * Initialize arguments.
	 *
	 * @return void
	 * @api
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerUniversalTagAttributes();
		$this->registerTagAttribute('multiple', 'string', 'if set, multiple select field');
		$this->registerTagAttribute('size', 'string', 'Size of input field');
		$this->registerTagAttribute('disabled', 'string', 'Specifies that the input element should be disabled when the page loads');
		$this->registerArgument('options', 'array', 'Associative array with internal IDs as key, and the values are displayed in the select box', TRUE);
		$this->registerArgument('optionValueField', 'string', 'If specified, will call the appropriate getter on each object to determine the value.');
		$this->registerArgument('optionLabelField', 'string', 'If specified, will call the appropriate getter on each object to determine the label.');
		$this->registerArgument('sortByOptionLabel', 'boolean', 'If true, List will be sorted by label.', FALSE, FALSE);
		$this->registerArgument('selectAllByDefault', 'boolean', 'If specified options are selected if none was set before.', FALSE, FALSE);
		$this->registerArgument('errorClass', 'string', 'CSS class to set if there are errors for this view helper', FALSE, 'f3-form-error');
		$this->registerArgument('translate', 'array', 'Configures translation of view helper output.');
	}

	/**
	 * Render the tag.
	 *
	 * @return string rendered tag.
	 * @api
	 */
	public function render() {
		$name = $this->getName();
		if ($this->hasArgument('multiple')) {
			$name .= '[]';
		}

		$this->tag->addAttribute('name', $name);

		$options = $this->getOptions();
		if (empty($options)) {
			$options = array('' => '');
		}
		$this->tag->setContent($this->renderOptionTags($options));

		$this->setErrorClassAttribute();

			// register field name for token generation.
			// in case it is a multi-select, we need to register the field name
			// as often as there are elements in the box
		if ($this->hasArgument('multiple') && $this->arguments['multiple'] !== '') {
			$this->renderHiddenFieldForEmptyValue();
			for ($i = 0; $i < count($options); $i++) {
				$this->registerFieldNameForFormTokenGeneration($name);
			}
		} else {
			$this->registerFieldNameForFormTokenGeneration($name);
		}

		return $this->tag->render();
	}

	/**
	 * Render the option tags.
	 *
	 * @param array $options the options for the form.
	 * @return string rendered tags.
	 */
	protected function renderOptionTags($options) {
		$output = '';

		foreach ($options as $value => $label) {
			$output .= $this->renderOptionTag($value, $label) . chr(10);
		}
		return $output;
	}

	/**
	 * Render the option tags.
	 *
	 * @return array an associative array of options, key will be the value of the option tag
	 * @throws \TYPO3\Fluid\Core\ViewHelper\Exception
	 */
	protected function getOptions() {
		if (!is_array($this->arguments['options']) && !($this->arguments['options'] instanceof \Traversable)) {
			return array();
		}
		$options = array();
		foreach ($this->arguments['options'] as $key => $value) {
			if (is_object($value)) {
				if ($this->hasArgument('optionValueField')) {
					$key = \TYPO3\Flow\Reflection\ObjectAccess::getPropertyPath($value, $this->arguments['optionValueField']);
					if (is_object($key)) {
						if (method_exists($key, '__toString')) {
							$key = (string)$key;
						} else {
							throw new \TYPO3\Fluid\Core\ViewHelper\Exception('Identifying value for object of class "' . get_class($value) . '" was an object.' , 1247827428);
						}
					}
				} elseif ($this->persistenceManager->getIdentifierByObject($value) !== NULL) {
					$key = $this->persistenceManager->getIdentifierByObject($value);
				} elseif (method_exists($value, '__toString')) {
					$key = (string)$value;
				} else {
					throw new \TYPO3\Fluid\Core\ViewHelper\Exception('No identifying value for object of class "' . get_class($value) . '" found.' , 1247826696);
				}

				if ($this->hasArgument('optionLabelField')) {
					$value = \TYPO3\Flow\Reflection\ObjectAccess::getPropertyPath($value, $this->arguments['optionLabelField']);
					if (is_object($value)) {
						if (method_exists($value, '__toString')) {
							$value = (string)$value;
						} else {
							throw new \TYPO3\Fluid\Core\ViewHelper\Exception('Label value for object of class "' . get_class($value) . '" was an object without a __toString() method.' , 1247827553);
						}
					}
				} elseif (method_exists($value, '__toString')) {
					$value = (string)$value;
				} elseif ($this->persistenceManager->getIdentifierByObject($value) !== NULL) {
					$value = $this->persistenceManager->getIdentifierByObject($value);
				}
			}
			$options[$key] = $value;
		}
		if ($this->arguments['sortByOptionLabel']) {
			asort($options);
		}
		return $options;
	}

	/**
	 * Render the option tags.
	 *
	 * @param mixed $value Value to check for
	 * @return boolean TRUE if the value should be marked a s selected; FALSE otherwise
	 */
	protected function isSelected($value) {
		$selectedValue = $this->getSelectedValue();
		if ($value === $selectedValue || (string)$value === $selectedValue) {
			return TRUE;
		}
		if ($this->hasArgument('multiple')) {
			if ($selectedValue === NULL && $this->arguments['selectAllByDefault'] === TRUE) {
				return TRUE;
			} elseif (is_array($selectedValue) && in_array($value, $selectedValue)) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * Retrieves the selected value(s)
	 *
	 * @return mixed value string or an array of strings
	 */
	protected function getSelectedValue() {
		$value = $this->getValue();
		if (!is_array($value) && !($value instanceof  \Traversable)) {
			return $this->getOptionValueScalar($value);
		}
		$selectedValues = array();
		foreach ($value as $selectedValueElement) {
			$selectedValues[] = $this->getOptionValueScalar($selectedValueElement);
		}
		return $selectedValues;
	}

	/**
	 * Get the option value for an object
	 *
	 * @param mixed $valueElement
	 * @return string
	 */
	protected function getOptionValueScalar($valueElement) {
		if (is_object($valueElement)) {
			if ($this->hasArgument('optionValueField')) {
				return \TYPO3\Flow\Reflection\ObjectAccess::getPropertyPath($valueElement, $this->arguments['optionValueField']);
			} elseif ($this->persistenceManager->getIdentifierByObject($valueElement) !== NULL) {
				return $this->persistenceManager->getIdentifierByObject($valueElement);
			} else {
				return (string)$valueElement;
			}
		} else {
			return $valueElement;
		}
	}

	/**
	 * Render one option tag
	 *
	 * @param string $value value attribute of the option tag (will be escaped)
	 * @param string $label content of the option tag (will be escaped)
	 * @return string the rendered option tag
	 */
	protected function renderOptionTag($value, $label) {
		$output = '<option value="' . htmlspecialchars($value) . '"';
		if ($this->isSelected($value)) {
			$output .= ' selected="selected"';
		}

		if ($this->hasArgument('translate')) {
			$label = $this->getTranslatedLabel($value, $label);
		}
		$output .= '>' . htmlspecialchars($label) . '</option>';

		return $output;
	}

	/**
	 * Returns a translated version of the given label
	 *
	 * @param string $value option tag value
	 * @param string $label option tag label
	 * @return string
	 * @throws \TYPO3\Fluid\Core\ViewHelper\Exception
	 */
	protected function getTranslatedLabel($value, $label) {
		$translationConfiguration = $this->arguments['translate'];

		$translateBy = isset($translationConfiguration['by']) ? $translationConfiguration['by'] : 'id';
		$sourceName = isset($translationConfiguration['source']) ? $translationConfiguration['source'] : 'Main';
		$packageKey = isset($translationConfiguration['package']) ? $translationConfiguration['package'] : $this->controllerContext->getRequest()->getControllerPackageKey();
		$prefix = isset($translationConfiguration['prefix']) ? $translationConfiguration['prefix'] : '';

		if (isset($translationConfiguration['locale'])) {
			try {
				$localeObject = new \TYPO3\Flow\I18n\Locale($translationConfiguration['locale']);
			} catch (\TYPO3\Flow\I18n\Exception\InvalidLocaleIdentifierException $e) {
				throw new \TYPO3\Fluid\Core\ViewHelper\Exception('"' . $translationConfiguration['locale'] . '" is not a valid locale identifier.' , 1330013193);
			}
		} else {
			$localeObject = NULL;
		}

		switch ($translateBy) {
			case 'label':
				$label =  isset($translationConfiguration['using']) && $translationConfiguration['using'] === 'value' ? $value : $label;
				return $this->translator->translateByOriginalLabel($label, array(), NULL, $localeObject, $sourceName, $packageKey);
			case 'id':
				$id =  $prefix . (isset($translationConfiguration['using']) && $translationConfiguration['using'] === 'label' ? $label : $value);
				return $this->translator->translateById($id, array(), NULL, $localeObject, $sourceName, $packageKey);
			default:
				throw new \TYPO3\Fluid\Exception('You can only request to translate by "label" or by "id", but asked for "' . $translateBy . '" in your SelectViewHelper tag.', 1340050647);
		}
	}
}

?>