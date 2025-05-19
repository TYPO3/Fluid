<?php

declare(strict_types=1);

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

    public function initializeArguments(): void
    {
        $this->registerArgument('name', 'string', 'Name of the slot that should be rendered', false, self::DEFAULT_SLOT);
    }

    public function render(): ?string
    {
        $variableContainer = $this->renderingContext->getViewHelperVariableContainer();
        $slot = $variableContainer->get(self::class, $this->arguments['name']);
        return is_callable($slot) ? (string)$slot() : null;
    }
}
