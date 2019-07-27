<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\ExpressionComponentInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * This view helper implements an if/else condition.
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
 * - string
 *
 * ::
 *
 *   <f:if condition="{rank} > 100">
 *     Will be shown if rank is > 100
 *   </f:if>
 *   <f:if condition="{rank} % 2">
 *     Will be shown if rank % 2 != 0.
 *   </f:if>
 *   <f:if condition="{rank} == {k:bar()}">
 *     Checks if rank is equal to the result of the ViewHelper "k:bar"
 *   </f:if>
 *   <f:if condition="{foo.bar} == 'stringToCompare'">
 *     Will result in true if {foo.bar}'s represented value equals 'stringToCompare'.
 *   </f:if>
 *
 * = Examples =
 *
 * <code title="Basic usage">
 * <f:if condition="somecondition">
 *   This is being shown in case the condition matches
 * </f:if>
 * </code>
 * <output>
 * Everything inside the <f:if> tag is being displayed if the condition evaluates to TRUE.
 * </output>
 *
 * <code title="If / then / else">
 * <f:if condition="somecondition">
 *   <f:then>
 *     This is being shown in case the condition matches.
 *   </f:then>
 *   <f:else>
 *     This is being displayed in case the condition evaluates to FALSE.
 *   </f:else>
 * </f:if>
 * </code>
 * <output>
 * Everything inside the "then" tag is displayed if the condition evaluates to TRUE.
 * Otherwise, everything inside the "else"-tag is displayed.
 * </output>
 *
 * <code title="inline notation">
 * {f:if(condition: someCondition, then: 'condition is met', else: 'condition is not met')}
 * </code>
 * <output>
 * The value of the "then" attribute is displayed if the condition evaluates to TRUE.
 * Otherwise, everything the value of the "else"-attribute is displayed.
 * </output>
 *
 * @api
 */
class IfViewHelper extends AbstractConditionViewHelper implements ExpressionComponentInterface
{
    protected $parts = [];

    public function __construct(iterable $parts = [])
    {
        $this->parts = $parts;
    }

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('condition', 'boolean', 'Condition expression conforming to Fluid boolean rules', false, false);
    }

    /**
     * Renders <f:then> child if $condition is true, otherwise renders <f:else> child.
     * Method which only gets called if the template is not compiled. For static calling,
     * the then/else nodes are converted to closures and condition evaluation closures.
     *
     * @return mixed
     * @api
     */
    public function render()
    {
        if (!empty($this->parts)) {
            return $this->evaluateParts($this->renderingContext, $this->parts);
        }
        return parent::render();
    }

    protected function condition(): bool
    {
        return (bool) $this->arguments['condition'];
    }

    /**
     * Matches possibilities:
     *
     * - {foo ? bar : baz}
     * - {foo ?: baz}
     *
     * But not:
     *
     * - {?bar:baz}
     * - {foo?bar:baz}
     * - {foo ?? bar}
     * - {foo ? bar : baz : more}
     *
     * And so on.
     *
     * @param array $parts
     * @return bool
     */
    public static function matches(array $parts): bool
    {
        return isset($parts[2]) && ($parts[1] === '?' && (($parts[2] ?? null) === ':' && !isset($parts[4])) || ($parts[3] ?? null) === ':' && !isset($parts[5]));
    }

    protected function evaluateParts(RenderingContextInterface $renderingContext, iterable $parts)
    {
        $check = null;
        $then = null;
        $else = null;
        $expression = '';
        $variables = $renderingContext->getVariableProvider();
        foreach ($parts as $part) {
            $expression .= $part . ' ';
            if ($part === ':' && $then === null) {
                $then = $check;
                continue;
            }

            if ($check === null) {
                $check = $part;
                continue;
            }

            if ($part === '?' || $part === ':') {
                continue;
            }

            if ($then === null) {
                $then = $part;
                continue;
            }

            if ($else === null) {
                $else = $part;
                break;
            }
        }

        $negated = false;
        if (!is_numeric($check)) {
            if ($check[0] === '!') {
                $check = substr($check, 1);
                $negated = true;
            }
            $check = $variables->get($check);
        }

        if (!is_numeric($then)) {
            $then = $variables->get($then) ?? $then;
        }

        if (!is_numeric($else)) {
            $else = $variables->get($else) ?? $else;
        }

        return $negated ? (!$check ? $then : $else) : ($check ? $then : $else);
    }
}
