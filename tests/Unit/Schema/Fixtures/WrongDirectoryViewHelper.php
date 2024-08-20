<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Schema\Fixtures;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class WrongDirectoryViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'string', 'A test argument');
    }

    public function render(): string
    {
        return '';
    }
}
