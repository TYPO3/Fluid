<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers\TestViewHelperResolverDelegate;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class Render_Sub extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function render(): string
    {
        return 'Render_Sub';
    }
}
