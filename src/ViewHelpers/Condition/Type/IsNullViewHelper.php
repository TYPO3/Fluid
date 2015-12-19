<?php
namespace TYPO3Fluid\Fluid\ViewHelpers\Condition\Type;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\ConditionViewHelperTrait;

/**
 * ### Condition: Value is NULL
 *
 * Condition ViewHelper which renders the `then` child if provided
 * value is NULL.
 */
class IsNullViewHelper extends AbstractViewHelper {

	use ConditionViewHelperTrait;

	/**
	 * Initialize arguments
	 */
	public function initializeArguments() {
		$this->registerConditionArguments();
		$this->registerArgument('value', 'string', 'value to check', TRUE);
	}

	/**
	 * This method decides if the condition is TRUE or FALSE. It can be overriden in extending viewhelpers to adjust functionality.
	 *
	 * @param array $arguments ViewHelper arguments to evaluate the condition for this ViewHelper, allows for flexiblity in overriding this method.
	 * @param RenderingContextInterface|NULL $renderingContext
	 * @return bool
	 */
	static protected function evaluateCondition($arguments = NULL, RenderingContextInterface $renderingContext = NULL) {
		return NULL === $arguments['value'];
	}

}
