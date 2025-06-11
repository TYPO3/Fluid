<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ComponentCollections;

use TYPO3Fluid\Fluid\Core\Component\AbstractComponentCollection;
use TYPO3Fluid\Fluid\View\TemplatePaths;

final class BasicComponentCollection extends AbstractComponentCollection
{
    public function getTemplatePaths(): TemplatePaths
    {
        $templatePaths = new TemplatePaths();
        $templatePaths->setTemplateRootPaths([
            __DIR__ . '/../Components/',
        ]);
        return $templatePaths;
    }

    public function getAdditionalVariables(string $viewHelperName): array
    {
        return [
            'myAdditionalVariable' => 'my additional value',
            'viewHelperName' => $viewHelperName,
        ];
    }

    public function additionalArgumentsAllowed(string $viewHelperName): bool
    {
        return $viewHelperName === 'additionalArguments' || $viewHelperName === 'additionalArgumentsJson';
    }
}
