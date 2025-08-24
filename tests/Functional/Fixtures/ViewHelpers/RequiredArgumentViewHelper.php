<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class RequiredArgumentViewHelper extends AbstractViewHelper
{
    protected bool $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('optional', 'string', '', false);
        $this->registerArgument('required', 'string', '', true);
    }

    public function render(): string
    {
        return '';
    }
}
