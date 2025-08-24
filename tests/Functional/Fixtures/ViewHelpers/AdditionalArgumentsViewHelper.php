<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

final class AdditionalArgumentsViewHelper extends AbstractViewHelper
{
    protected bool $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('defined', 'string', '');
    }

    public function render(): string
    {
        return '';
    }

    public function validateAdditionalArguments(array $arguments): void
    {
        throw new Exception('additional arguments validation: ' . implode(',', array_keys($arguments)), 1748543954);
    }
}
