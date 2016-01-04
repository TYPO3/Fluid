<?php
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * This ViewHelper counts elements of the specified array or countable object.
 *
 * ### Examples
 *
 * #### Count array elements
 *
 * ```html
 * <f:count subject="{0:1, 1:2, 2:3, 3:4}" />
 * ```
 * will output ```4```
 *
 * #### inline notation
 *
 * ```html
 * {objects -> f:count()}
 * ```
 *
 * will output ```10``` (depending on the number of items in {objects})
 *
 * @api
 */
class CountViewHelper extends AbstractViewHelper {

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
		$this->registerArgument('subject', 'array', 'Countable subject, array or \Countable', FALSE, NULL);
	}

	/**
	 * Counts the items of a given property.
	 *
	 * @return integer The number of elements
	 * @api
	 */
	public function render() {
		$subject = $this->arguments['subject'];
		if ($subject === NULL) {
			$subject = $this->renderChildren();
		}
		return count($subject);
	}
}
