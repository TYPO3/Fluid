<?php
namespace TYPO3\Fluid\ViewHelpers;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


/**
 * Viewhelper that outputs its childnodes with \TYPO3\var_dump()
 *
 * = Examples =
 *
 * <code>
 * <f:debug>{object}</f:debug>
 * </code>
 * <output>
 * all properties of {object} nicely highlighted
 * </output>
 *
 * <code title="inline notation and custom title">
 * {object -> f:debug(title: 'Custom title')}
 * </code>
 * <output>
 * all properties of {object} nicely highlighted (with custom title)
 * </output>
 *
 * <code title="only output the type">
 * {object -> f:debug(typeOnly: 1)}
 * </code>
 * <output>
 * the type or class name of {object}
 * </output>
 * @api
 */
class DebugViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var boolean
	 */
	protected $escapingInterceptorEnabled = FALSE;

	/**
	 * Wrapper for \TYPO3\Flow\var_dump()
	 *
	 * @param string $title
	 * @param boolean $typeOnly Whether only the type should be returned instead of the whole chain.
	 * @return string debug string
	 */
	public function render($title = NULL, $typeOnly = FALSE) {
		$expressionToExamine = $this->renderChildren();
		if ($typeOnly === TRUE && $expressionToExamine !== NULL) {
			$expressionToExamine = (is_object($expressionToExamine) ? get_class($expressionToExamine) : gettype($expressionToExamine));
		}

		ob_start();
		\TYPO3\Flow\var_dump($expressionToExamine, $title);
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
}


?>