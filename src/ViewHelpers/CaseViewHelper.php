<?php
namespace NamelessCoder\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\Core\ViewHelper;
use NamelessCoder\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Case view helper that is only usable within the SwitchViewHelper.
 * @see \NamelessCoder\Fluid\ViewHelpers\SwitchViewHelper
 *
 * @api
 */
class CaseViewHelper extends AbstractViewHelper {

	/**
	 * @var boolean
	 */
	protected $escapeOutput = FALSE;

	/**
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('value', 'mixed', 'Value to match in this case', TRUE);
	}

	/**
	 * @return string the contents of this view helper if $value equals the expression of the surrounding switch view helper, otherwise an empty string
	 * @throws ViewHelper\Exception
	 * @api
	 */
	public function render() {
		$value = $this->arguments['value'];
		$viewHelperVariableContainer = $this->renderingContext->getViewHelperVariableContainer();
		if (!$viewHelperVariableContainer->exists('NamelessCoder\Fluid\ViewHelpers\SwitchViewHelper', 'switchExpression')) {
			throw new ViewHelper\Exception('The "case" View helper can only be used within a switch View helper', 1368112037);
		}
		$switchExpression = $viewHelperVariableContainer->get('NamelessCoder\Fluid\ViewHelpers\SwitchViewHelper', 'switchExpression');

		// non-type-safe comparison by intention
		if ($switchExpression == $value) {
			$viewHelperVariableContainer->addOrUpdate('NamelessCoder\Fluid\ViewHelpers\SwitchViewHelper', 'break', TRUE);
			return $this->renderChildren();
		}
		return '';
	}
}
