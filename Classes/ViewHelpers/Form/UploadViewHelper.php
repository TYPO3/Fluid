<?php
namespace TYPO3\Fluid\ViewHelpers\Form;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A view helper which generates an <input type="file"> HTML element.
 * Make sure to set enctype="multipart/form-data" on the form!
 *
 * = Examples =
 *
 * <code title="Example">
 * <f:form.upload name="file" />
 * </code>
 * <output>
 * <input type="file" name="file" />
 * </output>
 *
 * @api
 */
class UploadViewHelper extends \TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper {

	/**
	 * @var string
	 */
	protected $tagName = 'input';

	/**
	 * @var TYPO3\FLOW3\Property\PropertyMapper
	 * @FLOW3\Inject
	 */
	protected $propertyMapper;

	/**
	 * Initialize the arguments.
	 *
	 * @return void
	 * @api
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerTagAttribute('disabled', 'string', 'Specifies that the input element should be disabled when the page loads');
		$this->registerArgument('errorClass', 'string', 'CSS class to set if there are errors for this view helper', FALSE, 'f3-form-error');
		$this->registerUniversalTagAttributes();
	}

	/**
	 * Renders the upload field.
	 *
	 * @return string
	 * @api
	 */
	public function render() {
		$name = $this->getName();
		$this->registerFieldNameForFormTokenGeneration($name);

		$output = '';
		$resourceObject = NULL;
		if ($this->hasMappingErrorOccured()) {
			$value = $this->getLastSubmittedFormData();
		} else {
			$value = $this->getValue();
		}
		if ($value) {

				// The form data which was submitted at the last request was in a format
				// which the PropertyMapper understands; so we re-build the Resource object
				// using the property mapper
			$resourceObject = $this->propertyMapper->convert($value, 'TYPO3\FLOW3\Resource\Resource');
		}
		$fileNameIdAttribute = $resourcePointerIdAttribute = '';
		if ($this->hasArgument('id')) {
			$fileNameIdAttribute = ' id="' . $this->arguments['id'] . '-fileName"';
			$resourcePointerIdAttribute = ' id="' . $this->arguments['id'] . '-resourcePointer"';
		}
		$fileNameValue = $resourcePointerValue = '';
		if ($resourceObject instanceof \TYPO3\FLOW3\Resource\Resource) {
			$fileNameValue = $resourceObject->getFileName();
			$resourcePointerValue = $resourceObject->getResourcePointer();
		}
		$output .= '<input type="hidden" name="'. $this->getName() . '[submittedFile][fileName]" value="' . $fileNameValue . '"' . $fileNameIdAttribute . ' />';
		$output .= '<input type="hidden" name="'. $this->getName() . '[submittedFile][resourcePointer]" value="' . $resourcePointerValue . '"' . $resourcePointerIdAttribute . ' />';

		$this->tag->addAttribute('type', 'file');
		$this->tag->addAttribute('name', $name);

		$this->setErrorClassAttribute();

		$output .= $this->tag->render();
		return $output;
	}
}


?>
