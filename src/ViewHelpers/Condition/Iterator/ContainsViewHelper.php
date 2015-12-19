<?php
namespace TYPO3Fluid\Fluid\ViewHelpers\Condition\Iterator;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\ConditionViewHelperTrait;

/**
 * Condition ViewHelper. Renders the then-child if Iterator/array
 * haystack contains needle value.
 *
 * ### Example:
 *
 *     {v:condition.iterator.contains(needle: 'foo', haystack: {0: 'foo'}, then: 'yes', else: 'no')}
 */
class ContainsViewHelper extends AbstractViewHelper {

	use ConditionViewHelperTrait;

	/**
	 * Initialize arguments
	 */
	public function initializeArguments() {
		$this->registerConditionArguments();
		$this->registerArgument('needle', 'mixed', 'Needle to search for in haystack', TRUE);
		$this->registerArgument('haystack', 'mixed', 'Haystack in which to look for needle', TRUE);
		$this->registerArgument('considerKeys', 'boolean', 'Tell whether to consider keys in the search assuming haystack is an array.', FALSE, FALSE);
	}

	/**
	 * This method decides if the condition is TRUE or FALSE. It can be overriden in extending viewhelpers to adjust functionality.
	 *
	 * @param array $arguments ViewHelper arguments to evaluate the condition for this ViewHelper, allows for flexiblity in overriding this method.
	 * @param RenderingContextInterface $renderingContext
	 * @return bool
	 */
	static protected function evaluateCondition($arguments = NULL, RenderingContextInterface $renderingContext = NULL) {
		return static::assertHaystackHasNeedle($arguments['haystack'], $arguments['needle'], $arguments) !== FALSE;
	}

	/**
	 * @param integer $index
	 * @param array $arguments
	 * @return mixed
	 */
	static protected function getNeedleAtIndex($index, $arguments) {
		if (0 > $index) {
			return NULL;
		}
		$haystack = $arguments['haystack'];
		$asArray = array();
		if (TRUE === is_array($haystack)) {
			$asArray = $haystack;
		} elseif (TRUE === $haystack instanceof \Traversable) {
			$asArray = iterator_to_array($haystack, FALSE);
		} elseif (TRUE === is_string($haystack)) {
			$asArray = str_split($haystack);
		}
		return (TRUE === isset($asArray[$index]) ? $asArray[$index] : FALSE);
	}

	/**
	 * @param mixed $haystack
	 * @param mixed $needle
	 * @param array $arguments
	 * @return boolean|integer
	 */
	static protected function assertHaystackHasNeedle($haystack, $needle, $arguments) {
		if (TRUE === is_array($haystack)) {
			return static::assertHaystackIsArrayAndHasNeedle($haystack, $needle, $arguments);
		} elseif (TRUE === $haystack instanceof \Traversable) {
			return static::assertHaystackIsArrayAndHasNeedle(iterator_to_array($haystack, FALSE), $needle, $arguments);
		} elseif (TRUE === is_string($haystack)) {
			return strpos($haystack, $needle);
		}
		return FALSE;
	}

	/**
	 * @param mixed $haystack
	 * @param mixed $needle
	 * @param array $arguments
	 * @return boolean|integer
	 */
	static protected function assertHaystackIsArrayAndHasNeedle($haystack, $needle, $arguments) {
		if (TRUE === (boolean) $arguments['considerKeys']) {
			$result = (boolean) (FALSE !== array_search($needle, $haystack) || TRUE === isset($haystack[$needle]));
		} else {
			$result = array_search($needle, $haystack);
		}
		return $result;
	}

	/**
	 * @param mixed $haystack
	 * @param mixed $needle
	 * @return boolean|integer
	 */
	static protected function assertHaystackIsStringAndHasNeedle($haystack, $needle) {
		return strpos($haystack, $needle);
	}

}
