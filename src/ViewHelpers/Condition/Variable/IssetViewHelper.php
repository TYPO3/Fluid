<?php
namespace TYPO3Fluid\Fluid\ViewHelpers\Condition\Variable;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\ConditionViewHelperTrait;

/**
 * ### Variable: Isset
 *
 * Renders the `then` child if the variable name given in
 * the `name` argument exists in the template. The value
 * can be zero, NULL or an empty string - but the ViewHelper
 * will still return TRUE if the variable exists.
 *
 * Combines well with dynamic variable names:
 *
 *     <!-- if {variableContainingVariableName} is "foo" this checks existence of {foo} -->
 *     <v:condition.variable.isset name="{variableContainingVariableName}">...</v:condition.variable.isset>
 *     <!-- if {suffix} is "Name" this checks existence of "variableName" -->
 *     <v:condition.variable.isset name="variable{suffix}">...</v:condition.variable.isset>
 *     <!-- outputs value of {foo} if {bar} is defined -->
 *     {foo -> v:condition.variable.isset(name: bar)}
 */
class IssetViewHelper extends AbstractConditionViewHelper {

	use ConditionViewHelperTrait;

	/**
	 * Initialize arguments
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('name', 'string', 'name of the variable', TRUE);
	}
	/**
 	 * Render
 	 *
 	 * @return string
 	 */
 	public function render() {
		return static::renderStatic(
			$this->arguments,
			$this->buildRenderChildrenClosure(),
			$this->renderingContext
		);
	}

	/**
	 * @param array|NULL $arguments
	 * @param RenderingContextInterface|NULL $renderingContext
	 * @return bool
	 */
	protected static function evaluateCondition($arguments = NULL, RenderingContextInterface $renderingContext = NULL) {
		return $renderingContext->getVariableProvider()->exists($arguments['name']);
	}

}
