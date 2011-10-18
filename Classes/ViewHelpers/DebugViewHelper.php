<?php
namespace TYPO3\Fluid\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


/**
 * Viewhelper that outputs its childnodes with \TYPO3\var_dump()
 *
 * @api
 */
class DebugViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var boolean
	 */
	protected $escapingInterceptorEnabled = FALSE;

	/**
	 * Wrapper for \TYPO3\FLOW3\var_dump()
	 *
	 * @param string $title
	 * @return string debug string
	 */
	public function render($title = NULL) {
		ob_start();
		\TYPO3\FLOW3\var_dump($this->renderChildren(), $title);
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
}


?>