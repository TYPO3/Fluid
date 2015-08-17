<?php
namespace TYPO3Fluid\Fluid\ViewHelpers\Format;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Outputs an argument/value without any escaping. Is normally used to output
 * an ObjectAccessor which should not be escaped, but output as-is.
 *
 * PAY SPECIAL ATTENTION TO SECURITY HERE (especially Cross Site Scripting),
 * as the output is NOT SANITIZED!
 *
 * = Examples =
 *
 * <code title="Child nodes">
 * <f:format.raw>{string}</f:format.raw>
 * </code>
 * <output>
 * (Content of {string} without any conversion/escaping)
 * </output>
 *
 * <code title="Value attribute">
 * <f:format.raw value="{string}" />
 * </code>
 * <output>
 * (Content of {string} without any conversion/escaping)
 * </output>
 *
 * <code title="Inline notation">
 * {string -> f:format.raw()}
 * </code>
 * <output>
 * (Content of {string} without any conversion/escaping)
 * </output>
 *
 * @api
 */
class RawViewHelper extends AbstractViewHelper {

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
		$this->registerArgument('value', 'mixed', 'The value to output', FALSE, NULL);
	}

	/**
	 * @return string
	 */
	public function render() {
		if (!$this->hasArgument('value')) {
			return $this->renderChildren();
		} else {
			return $this->arguments['value'];
		}
	}
}
