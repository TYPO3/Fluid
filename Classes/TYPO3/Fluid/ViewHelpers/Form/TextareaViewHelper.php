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

/**
 * Textarea view helper.
 * The value of the text area needs to be set via the "value" attribute, as with all other form ViewHelpers.
 *
 * = Examples =
 *
 * <code title="Example">
 * <f:form.textarea name="myTextArea" value="This is shown inside the textarea" />
 * </code>
 * <output>
 * <textarea name="myTextArea">This is shown inside the textarea</textarea>
 * </output>
 *
 * @api
 */
class TextareaViewHelper extends AbstractFormFieldViewHelper {

	/**
	 * @var string
	 */
	protected $tagName = 'textarea';

	/**
	 * Initialize the arguments.
	 *
	 * @return void
	 * @api
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerTagAttribute('rows', 'int', 'The number of rows of a text area');
		$this->registerTagAttribute('cols', 'int', 'The number of columns of a text area');
		$this->registerTagAttribute('disabled', 'string', 'Specifies that the input element should be disabled when the page loads');
		$this->registerTagAttribute('placeholder', 'string', 'The placeholder of the textarea');
		$this->registerTagAttribute('autofocus', 'string', 'Specifies that a text area should automatically get focus when the page loads');
		$this->registerArgument('errorClass', 'string', 'CSS class to set if there are errors for this view helper', FALSE, 'f3-form-error');
		$this->registerArgument('required', 'boolean', 'If the field should be marked as required or not', FALSE, FALSE);
		$this->registerUniversalTagAttributes();
	}

	/**
	 * Renders the textarea.
	 *
	 * @return string
	 * @api
	 */
	public function render() {
		$name = $this->getName();
		$this->registerFieldNameForFormTokenGeneration($name);

		$this->tag->forceClosingTag(TRUE);
		$this->tag->addAttribute('name', $name);
		$this->tag->setContent(htmlspecialchars($this->getValue()));

		if ($this->hasArgument('required') && $this->arguments['required'] === TRUE) {
			$this->tag->addAttribute('required', 'required');
		}

		$this->setErrorClassAttribute();

		return $this->tag->render();
	}
}
