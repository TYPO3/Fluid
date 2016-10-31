<?php
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileEmpty;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\PassthroughRenderChildren;

/**
 * Else-Branch of a condition. Only has an effect inside of "If". See the If-ViewHelper for documentation.
 *
 * = Examples =
 *
 * <code title="Output content if condition is not met">
 * <f:if condition="{someCondition}">
 *   <f:else>
 *     condition was not true
 *   </f:else>
 * </f:if>
 * </code>
 * <output>
 * Everything inside the "else" tag is displayed if the condition evaluates to FALSE.
 * Otherwise nothing is outputted in this example.
 * </output>
 *
 * @see TYPO3Fluid\Fluid\ViewHelpers\IfViewHelper
 * @api
 */
class ElseViewHelper extends AbstractViewHelper
{
    use CompileEmpty;
    use PassthroughRenderChildren;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('if', 'boolean', 'Condition expression conforming to Fluid boolean rules');
    }

}
