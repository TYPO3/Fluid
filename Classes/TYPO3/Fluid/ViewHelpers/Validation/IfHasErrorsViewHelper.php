<?php
namespace TYPO3\Fluid\ViewHelpers\Validation;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Error\Result;
use TYPO3\Flow\Mvc\ActionRequest;
use TYPO3\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * This view helper allows to check whether validation errors adhere to the current request.
 *
 * = Examples =
 *
 * <code title="Basic usage">
 * <f:validation.ifHasErrors>
 *   <div class="alert">Please fill out all fields according to the description</div>
 * </f:validation.ifHasErrors>
 * </code>
 *
 * <code title="Usage with property path in forms">
 * <f:form name="blog">
 *   <div class="row {f:validation.ifHasErrors(for: 'blog.title', then: 'has-error')}">
 *     <f:form.textfield property="title" />
 *     <span class="error-text">You must provide a title.</span>
 *   </div>
 * </f:form>
 * </code>
 *
 * @api
 */
class IfHasErrorsViewHelper extends AbstractConditionViewHelper {

	/**
	 * Renders <f:then> child if there are validation errors. The check can be narrowed down to
	 * specific property paths.
	 * If no errors are there, it renders the <f:else>-child.
	 *
	 * @param string $for The argument or property name or path to check for error(s)
	 * @return string
	 * @api
	 */
	public function render($for = NULL) {
		/** @var $request ActionRequest */
		$request = $this->controllerContext->getRequest();
		/** @var $validationResults Result */
		$validationResults = $request->getInternalArgument('__submittedArgumentValidationResults');

		if ($validationResults !== NULL) {
				// if $for is not set, ->forProperty will return the initial Result object untouched
			$validationResults = $validationResults->forProperty($for);
			if ($validationResults->hasErrors()) {
				return $this->renderThenChild();
			}
		}
		return $this->renderElseChild();
	}
}
