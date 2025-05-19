<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class SlotViewHelper extends AbstractViewHelper
{
    public const DEFAULT_SLOT = 'default';

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'Name of the slot that should be rendered', false, self::DEFAULT_SLOT);
    }

    /**
     * @return mixed
     */
    public function render()
    {
        $variableContainer = $this->renderingContext->getViewHelperVariableContainer();
        $slot = $variableContainer->get(self::class, $this->arguments['name']);
        return is_callable($slot) ? $slot() : null;
    }
}
