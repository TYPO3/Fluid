<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class OriginalTemplatePathViewHelper extends AbstractViewHelper
{
    public function render(): string
    {
        if (method_exists($this->renderingContext, 'getOriginalTemplatePath')) {
            return $this->renderingContext->getOriginalTemplatePath();
        }
        return '';
    }
}
