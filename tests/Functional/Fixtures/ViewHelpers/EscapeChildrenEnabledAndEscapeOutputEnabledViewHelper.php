<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers;

/**
 * Class EscapeChildrenEnabledAndEscapeOutputEnabledViewHelper
 */
class EscapeChildrenEnabledAndEscapeOutputEnabledViewHelper extends AbstractEscapingViewHelper
{
    protected $escapeChildren = true;
    protected $escapeOutput = true;
}
