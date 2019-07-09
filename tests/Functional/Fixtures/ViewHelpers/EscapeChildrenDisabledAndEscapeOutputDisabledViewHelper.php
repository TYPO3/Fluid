<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Class EscapeChildrenDisabledAndEscapeOutputDisabledViewHelper
 */
class EscapeChildrenDisabledAndEscapeOutputDisabledViewHelper extends AbstractEscapingViewHelper
{

    protected $escapeChildren = false;
    protected $escapeOutput = false;
}
