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
 * A view helper which specifies the "default" case when used within the SwitchViewHelper.
 * @see \TYPO3\Fluid\ViewHelpers\SwitchViewHelper
 *
 * @api
 */
class DefaultCaseViewHelper extends AbstractViewHelper {

	/**
	 * @return string the contents of this view helper if no other "Case" view helper of the surrounding switch view helper matches
	 * @throws ViewHelper\Exception
	 * @api
	 */
	public function render() {
		$viewHelperVariableContainer = $this->renderingContext->getViewHelperVariableContainer();
		if (!$viewHelperVariableContainer->exists('TYPO3\Fluid\ViewHelpers\SwitchViewHelper', 'switchExpression')) {
			throw new ViewHelper\Exception('The "default case" View helper can only be used within a switch View helper', 1368112037);
		}
		return $this->renderChildren();
	}
}
