<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class ContentArgumentNameWithoutEscapeChildrenViewHelper extends AbstractViewHelper
{
    protected ?bool $escapeChildren = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'string', '');
    }

    public function render(): ?string
    {
        return $this->renderChildren();
    }

    public function getContentArgumentName(): string
    {
        return 'value';
    }
}
