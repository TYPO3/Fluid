<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\ViewHelper;

/**
 * Only to be used together with TemplateStructureViewHelperResolver.
 *
 * @internal
 * @todo This logic should be part of the TemplateParser.
 */
final class TemplateStructurePlaceholderViewHelper extends AbstractViewHelper
{
    public function render(): string
    {
        return '';
    }

    public function validateAdditionalArguments(array $arguments): void
    {
        // Allow all arguments to prevent parser exceptions
    }
}
