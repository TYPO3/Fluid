<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollectionInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

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

    public function execute(RenderingContextInterface $renderingContext, ?ArgumentCollectionInterface $arguments = null)
    {
        $arguments = ($arguments ?? $this->parsedArguments ?? $this->getArguments())->evaluate($renderingContext);
        $value = $arguments['value'];
        $viewHelperVariableContainer = $renderingContext->getViewHelperVariableContainer();
        if (!$viewHelperVariableContainer->exists(SwitchViewHelper::class, 'switchExpression')) {
            throw new Exception('The "case" View helper can only be used within a switch View helper', 1368112037);
        }
        $switchExpression = $viewHelperVariableContainer->get(SwitchViewHelper::class, 'switchExpression');

        // non-type-safe comparison by intention
        if ($switchExpression == $value) {
            $viewHelperVariableContainer->addOrUpdate(SwitchViewHelper::class, 'break', true);
            return $this->evaluateChildren($renderingContext);
        }
        return null;
    }
}
