<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class ObjectUnionTypeViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument(
            'arg',
            'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\Objects\WithCamelCaseGetter|TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\Objects\WithProperties',
            '',
            true,
        );
    }

    public function render(): string
    {
        return get_class($this->arguments['arg']);
    }
}
