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
    public function prepareArguments()
    {
        // Override to avoid the static cache of registered ViewHelper arguments; will always return
        // only those arguments that are registered in this particular instance.
        $this->argumentDefinitions = [];
        $this->registerUniversalTagAttributes();
        $this->initializeArguments();
        return $this->argumentDefinitions;
    }
}
