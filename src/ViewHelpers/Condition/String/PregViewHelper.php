<?php
namespace TYPO3Fluid\Fluid\ViewHelpers\Condition\String;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\TemplateVariableViewHelperTrait;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\ConditionViewHelperTrait;

/**
 * ### Condition: String matches regular expression
 *
 * Condition ViewHelper which renders the `then` child if provided
 * string matches provided regular expression. $matches array containing
 * the results can be made available by providing a template variable
 * name with argument $as.
 */
class PregViewHelper extends AbstractViewHelper {

	use ConditionViewHelperTrait;

	/**
	 * Initialize arguments
	 */
	public function initializeArguments() {
		$this->registerConditionArguments();
		$this->registerArgument('pattern', 'string', 'regex pattern to match string against', TRUE);
		$this->registerArgument('string', 'string', 'string to match with the regex pattern', TRUE);
		$this->registerArgument('global', 'boolean', 'match global', FALSE, FALSE);
	}

	/**
	 * This method decides if the condition is TRUE or FALSE. It can be overriden in extending viewhelpers to adjust functionality.
	 *
	 * @param array $arguments ViewHelper arguments to evaluate the condition for this ViewHelper, allows for flexiblity in overriding this method.
	 * @param RenderingContextInterface|NULL $renderingContext
	 * @return bool
	 */
	static protected function evaluateCondition($arguments = NULL, RenderingContextInterface $renderingContext = NULL) {
		$matches = array();
		if (TRUE === (boolean) $arguments['global']) {
			preg_match_all($arguments['pattern'], $arguments['string'], $matches, PREG_SET_ORDER);
		} else {
			preg_match($arguments['pattern'], $arguments['string'], $matches);
		}
		return 0 < count($matches);
	}

}
