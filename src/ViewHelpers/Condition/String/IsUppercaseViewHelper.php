<?php
namespace TYPO3Fluid\Fluid\ViewHelpers\Condition\String;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\ConditionViewHelperTrait;

/**
 * ### Condition: String is lowercase
 *
 * Condition ViewHelper which renders the `then` child if provided
 * string is uppercase. By default only the first letter is tested.
 * To test the full string set $fullString to TRUE.
 */
class IsUppercaseViewHelper extends AbstractConditionViewHelper {

	use ConditionViewHelperTrait;

	/**
	 * Initialize arguments
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('string', 'string', 'string to check', TRUE);
		$this->registerArgument('fullString', 'string', 'need', FALSE, FALSE);
	}

	/**
	 * This method decides if the condition is TRUE or FALSE. It can be overriden in extending viewhelpers to adjust functionality.
	 *
	 * @param array $arguments ViewHelper arguments to evaluate the condition for this ViewHelper, allows for flexiblity in overriding this method.
	 * @return bool
	 */
	static protected function evaluateCondition($arguments = NULL) {
		if (TRUE === $arguments['fullString']) {
			$result = ctype_upper($arguments['string']);
		} else {
			$result = ctype_upper(substr($arguments['string'], 0, 1));
		}
		return TRUE === $result;
	}

}
