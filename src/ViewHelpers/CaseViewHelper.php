<?php
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\ViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Case view helper that is only usable within the SwitchViewHelper.
 * @see \TYPO3Fluid\Fluid\ViewHelpers\SwitchViewHelper
 *
 * @api
 */
class CaseViewHelper extends AbstractViewHelper
{

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('value', 'mixed', 'Value to match in this case', true);
    }

    /**
     * @return string the contents of this view helper if $value equals the expression of the surrounding switch view helper, otherwise an empty string
     * @throws ViewHelper\Exception
     * @api
     */
    public function render()
    {
        $value = $this->arguments['value'];
        $viewHelperVariableContainer = $this->renderingContext->getViewHelperVariableContainer();
        if (!$viewHelperVariableContainer->exists(SwitchViewHelper::class, 'switchExpression')) {
            throw new ViewHelper\Exception('The "case" View helper can only be used within a switch View helper', 1368112037);
        }
        $switchExpression = $viewHelperVariableContainer->get(SwitchViewHelper::class, 'switchExpression');

        // non-type-safe comparison by intention
        if ($switchExpression == $value) {
            $viewHelperVariableContainer->addOrUpdate(SwitchViewHelper::class, 'break', true);
            return $this->renderChildren();
        }
        return '';
    }

    /**
     * @param string $argumentsName
     * @param string $closureName
     * @param string $initializationPhpCode
     * @param ViewHelperNode $node
     * @param TemplateCompiler $compiler
     * @return string
     */
    public function compile($argumentsName, $closureName, &$initializationPhpCode, ViewHelperNode $node, TemplateCompiler $compiler)
    {
        return '\'\'';
    }
}
