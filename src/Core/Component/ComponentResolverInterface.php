<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Component;

use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\View\TemplatePaths;

/**
 * @internal This interface should only be used for type hinting
 */
interface ComponentResolverInterface
{
    public function resolveTemplateName(string $viewHelperName): string;
    public function getTemplatePaths(): TemplatePaths;
    public function getAdditionalVariables(): array;
}
