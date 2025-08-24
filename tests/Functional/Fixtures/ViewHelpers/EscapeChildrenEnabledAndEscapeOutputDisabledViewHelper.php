<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers;

final class EscapeChildrenEnabledAndEscapeOutputDisabledViewHelper extends AbstractEscapingViewHelper
{
    protected ?bool $escapeChildren = true;
    protected bool $escapeOutput = false;
}
