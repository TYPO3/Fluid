<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

class TagBasedTestViewHelper extends AbstractTagBasedViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('registeredArgument', 'string', 'test argument');
        $this->registerTagAttribute('booleanArgument', 'boolean', 'test argument');
        $this->registerTagAttribute('registeredTagAttribute', 'string', 'test argument');
    }
}
