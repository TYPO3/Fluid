<?php
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * This view helper implements an if/else condition.
 * Check \TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode::convertArgumentValue() to see how boolean arguments are evaluated
 *
 * **Conditions:**
 *
 * As a condition is a boolean value, you can just use a boolean argument.
 * Alternatively, you can write a boolean expression there.
 * Boolean expressions have the following form:
 * XX Comparator YY
 * Comparator is one of: ==, !=, <, <=, >, >= and %
 * The % operator converts the result of the % operation to boolean.
 *
 * XX and YY can be one of:
 * - number
 * - Object Accessor
 * - Array
 * - a ViewHelper
 *
 * > Note: Strings at XX/YY are NOT allowed, however, for the time being,
 * > a string comparison can be achieved with comparing arrays (see example below).
 *
 * ```html
 *   <f:if condition="{rank} > 100">
 *     Will be shown if rank is > 100
 *   </f:if>
 *   <f:if condition="{rank} % 2">
 *     Will be shown if rank % 2 != 0.
 *   </f:if>
 *   <f:if condition="{rank} == {k:bar()}">
 *     Checks if rank is equal to the result of the ViewHelper "k:bar"
 *   </f:if>
 *   <f:if condition="{0: foo.bar} == {0: 'stringToCompare'}">
 *     Will result true if {foo.bar}'s represented value equals 'stringToCompare'.
 *   </f:if>
 *```
 *
 * ### Examples
 *
 * **Basic usage**
 *
 * ```html
 * <f:if condition="somecondition">
 *   This is being shown in case the condition matches
 * </f:if>
 * ```
 * Everything inside the <f:if> tag is being displayed if the condition evaluates to TRUE.
 *
 *
 * **If / then / else**
 *
 * ```html
 * <f:if condition="somecondition">
 *   <f:then>
 *     This is being shown in case the condition matches.
 *   </f:then>
 *   <f:else>
 *     This is being displayed in case the condition evaluates to FALSE.
 *   </f:else>
 * </f:if>
 * ```
 * Everything inside the "then" tag is displayed if the condition evaluates to TRUE.
 * Otherwise, everything inside the "else"-tag is displayed.
 *
 * **inline notation**
 *
 * ```html
 * {f:if(condition: someCondition, then: 'condition is met', else: 'condition is not met')}
 * ```
 *
 * The value of the "then" attribute is displayed if the condition evaluates to TRUE.
 * Otherwise, everything the value of the "else"-attribute is displayed.
 *
 *
 * @see \TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode::convertArgumentValue()
 * @api
 */
class IfViewHelper extends AbstractConditionViewHelper {

	/**
	 * Renders <f:then> child if $condition is true, otherwise renders <f:else> child.
	 *
	 * @param boolean $condition View helper condition
	 * @return string the rendered string
	 * @api
	 */
	public function render() {
		if ($this->arguments['condition']) {
			return $this->renderThenChild();
		} else {
			return $this->renderElseChild();
		}
	}

}
