<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * This ViewHelper implements an if/else condition.
 *
 * Fluid Boolean Rules / Conditions:
 * =================================
 *
 * A condition is evaluated as a boolean value, so you can use any
 * boolean argument, like a variable.
 * Alternatively, you can use a full boolean expression.
 * The entered expression is evaluated as a PHP expression. You can
 * combine multiple expressions via :php:`&&` (logical AND) and
 * :php:`||` (logical OR).
 *
 * An expression can also be prepended with the :php:`!` ("not") character,
 * which will negate that expression.
 *
 * Have a look into the Fluid section of the "TYPO3 Explained" Documentation
 * for more details about complex conditions.
 *
 * Boolean expressions have the following form:
 *
 * `is true` variant: `{variable}`::
 *
 *       <f:if condition="{foo}">
 *           Will be shown if foo is truthy.
 *       </f:if>
 *
 * or `is false` variant: `!{variable}`::
 *
 *       <f:if condition="!{foo}">
 *           Will be shown if foo is falsy.
 *       </f:if>
 *
 * or comparisons with expressions::
 *
 *       XX Comparator YY
 *
 * Comparator is one of: :php:`==, !=, <, <=, >, >=` and :php:`%`
 * The :php:`%` operator (modulo) converts the result of the operation to
 * boolean.
 *
 * `XX` and `YY` can be one of:
 *
 * - Number
 * - String
 * - Object Accessor (`object.property`)
 * - Array
 * - a ViewHelper
 *
 * ::
 *
 *       <f:if condition="{rank} > 100">
 *           Will be shown if rank is > 100
 *       </f:if>
 *       <f:if condition="{rank} % 2">
 *           Will be shown if rank % 2 != 0.
 *       </f:if>
 *       <f:if condition="{rank} == {k:bar()}">
 *           Checks if rank is equal to the result of the ViewHelper "k:bar"
 *       </f:if>
 *       <f:if condition="{object.property} == 'stringToCompare'">
 *           Will result in true if {object.property}'s represented value
 *           equals 'stringToCompare'.
 *       </f:if>
 *
 * Examples
 * ========
 *
 * Basic usage
 * -----------
 *
 * ::
 *
 *     <f:if condition="somecondition">
 *         This is being shown in case the condition matches
 *     </f:if>
 *
 * Output::
 *
 *     Everything inside the <f:if> tag is being displayed if the condition evaluates to true.
 *
 * If / then / else
 * ----------------
 *
 * ::
 *
 *     <f:if condition="somecondition">
 *         <f:then>
 *             This is being shown in case the condition matches.
 *         </f:then>
 *         <f:else>
 *             This is being displayed in case the condition evaluates to false.
 *         </f:else>
 *     </f:if>
 *
 * Output::
 *
 *     Everything inside the "then" tag is displayed if the condition evaluates to true.
 *     Otherwise, everything inside the "else" tag is displayed.
 *
 * Inline notation
 * ---------------
 *
 * ::
 *
 *     {f:if(condition: someCondition, then: 'condition is met', else: 'condition is not met')}
 *
 * Output::
 *
 *     The value of the "then" attribute is displayed if the condition evaluates to true.
 *     Otherwise, everything the value of the "else" attribute is displayed.
 *
 * Combining multiple conditions
 * -----------------------------
 *
 * ::
 *
 *     <f:if condition="{user.rank} > 100 && {user.type} == 'contributor'">
 *         <f:then>
 *             This is being shown in case both conditions match.
 *         </f:then>
 *         <f:else if="{user.rank} > 200 && ({user.type} == 'contributor' || {user.type} == 'developer')">
 *             This is being displayed in case the first block of the condition evaluates to true and any condition in
 *             the second condition block evaluates to true.
 *         </f:else>
 *         <f:else>
 *             This is being displayed when none of the above conditions evaluated to true.
 *         </f:else>
 *     </f:if>
 *
 * Output::
 *
 *     Depending on which expression evaluated to true, that value is displayed.
 *     If no expression matched, the contents inside the final "else" tag are displayed.
 *
 * @api
 * @todo: Declare final with next major
 */
class IfViewHelper extends AbstractConditionViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('condition', 'boolean', 'Condition expression conforming to Fluid boolean rules', false, false);
    }

    /**
     * @param array $arguments
     * @param RenderingContextInterface $renderingContext
     * @return bool
     */
    public static function verdict(array $arguments, RenderingContextInterface $renderingContext)
    {
        return (bool)$arguments['condition'];
    }
}
