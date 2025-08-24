<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class ScalarArgumentsViewHelper extends AbstractViewHelper
{
    protected bool $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('booleanArg', 'boolean', '');
        $this->registerArgument('boolArg', 'bool', '');
        $this->registerArgument('stringArg', 'string', '');
        $this->registerArgument('integerArg', 'integer', '');
        $this->registerArgument('intArg', 'int', '');
        $this->registerArgument('floatArg', 'float', '');
        $this->registerArgument('doubleArg', 'double', '');
    }

    public function render(): string
    {
        return serialize($this->arguments);
    }
}
