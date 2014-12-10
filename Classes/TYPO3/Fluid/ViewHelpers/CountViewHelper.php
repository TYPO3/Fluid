<?php
namespace TYPO3\Fluid\ViewHelpers;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Fluid\Core\ViewHelper;

/**
 * This ViewHelper counts elements of the specified array or countable object.
 *
 * = Examples =
 *
 * <code title="Count array elements">
 * <f:count subject="{0:1, 1:2, 2:3, 3:4}" />
 * </code>
 * <output>
 * 4
 * </output>
 *
 * <code title="inline notation">
 * {objects -> f:count()}
 * </code>
 * <output>
 * 10 (depending on the number of items in {objects})
 * </output>
 *
 * @api
 */
class CountViewHelper extends AbstractViewHelper {

	/**
	 * @var boolean
	 */
	protected $escapingInterceptorEnabled = FALSE;

	/**
	 * Counts the items of a given property.
	 *
	 * @param array|\Countable $subject The array or \Countable to be counted
	 * @return integer The number of elements
	 * @throws ViewHelper\Exception
	 * @api
	 */
	public function render($subject = NULL) {
		if ($subject === NULL) {
			$subject = $this->renderChildren();
		}
		if (is_object($subject) && !$subject instanceof \Countable) {
			throw new ViewHelper\Exception('CountViewHelper only supports arrays and objects implementing \Countable interface. Given: "' . get_class($subject) . '"', 1279808078);
		}
		return count($subject);
	}
}
