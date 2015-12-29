<?php
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper that outputs its child nodes with \TYPO3Fluid\Flow\var_dump()
 *
 * ### Examples
 *
 * ```html
 * <f:debug>{object}</f:debug>
 * ```
 * will ouput all properties of {object} nicely highlighted
 *
 * #### inline notation and custom title
 *
 * ```html
 * {object -> f:debug(title: 'Custom title')}
 * ```
 * will output all properties of {object} nicely highlighted (with custom title)
 *
 * #### only output the type
 *
 * ```
 * {object -> f:debug(typeOnly: true)}
 * ```
 * will output the type or class name of {object}
 *
 * > Note: This view helper is only meant to be used during development
 *
 * @api
 */
class DebugViewHelper extends AbstractViewHelper {

	/**
	 * @var boolean
	 */
	protected $escapeChildren = FALSE;

	/**
	 * @var boolean
	 */
	protected $escapeOutput = FALSE;

	/**
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('typeOnly', 'boolean', 'If TRUE, debugs only the type of variables', FALSE, FALSE);
	}

	/**
	 * Wrapper for \TYPO3Fluid\Flow\var_dump()
	 *
	 * @return string debug string
	 */
	public function render() {
		$typeOnly = $this->arguments['typeOnly'];
		$expressionToExamine = $this->renderChildren();
		if ($typeOnly === TRUE) {
			return (is_object($expressionToExamine) ? get_class($expressionToExamine) : gettype($expressionToExamine));
		}

		return var_export($expressionToExamine, TRUE);
	}
}
