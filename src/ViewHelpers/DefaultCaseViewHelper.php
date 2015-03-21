<?php
namespace TYPO3\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\ViewHelper;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * A view helper which specifies the "default" case when used within the SwitchViewHelper.
 * @see \TYPO3\Fluid\ViewHelpers\SwitchViewHelper
 *
 * @api
 */
class DefaultCaseViewHelper extends AbstractViewHelper {

	/**
	 * @var boolean
	 */
	protected $escapeOutput = FALSE;

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
