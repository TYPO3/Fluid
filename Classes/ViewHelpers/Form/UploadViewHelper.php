<?php
namespace TYPO3\Fluid\ViewHelpers\Form;

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
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @scope prototype
 */
class UploadViewHelper extends \TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper {

	/**
	 * @var string
	 */
	protected $tagName = 'input';

	/**
	 * @var TYPO3\FLOW3\Property\PropertyMapper
	 * @inject
	 */
	protected $propertyMapper;

	/**
	 * Initialize the arguments.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
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
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
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
