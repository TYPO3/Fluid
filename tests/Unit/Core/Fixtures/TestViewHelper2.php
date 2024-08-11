<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Fixtures;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class TestViewHelper2 extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('param1', 'integer', 'P1 Stuff', true);
        $this->registerArgument('param2', 'array', 'P2 Stuff', true);
        $this->registerArgument('param2', 'string', 'P3 Stuff', false, 'default');
    }

    public function render(): void {}
}
