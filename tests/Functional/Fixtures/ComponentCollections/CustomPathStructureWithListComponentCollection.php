<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ComponentCollections;

use TYPO3Fluid\Fluid\Core\Component\AbstractComponentCollection;
use TYPO3Fluid\Fluid\View\TemplatePaths;

final class CustomPathStructureWithListComponentCollection extends AbstractComponentCollection
{
    public function getTemplatePaths(): TemplatePaths
    {
        $templatePaths = new TemplatePaths();
        $templatePaths->setTemplateRootPaths([
            __DIR__ . '/../Components/',
        ]);
        return $templatePaths;
    }

    public function resolveTemplateName(string $viewHelperName): string
    {
        $fragments = array_map(ucfirst(...), explode('.', $viewHelperName));
        $name = array_pop($fragments);
        $path = implode('/', $fragments);
        return ($path !== '' ? $path . '/' : '') . $name;
    }

    public function getAvailableComponents(): array
    {
        $availableTemplates = $this->getTemplatePaths()->resolveAvailableTemplateFiles(null, null, true);
        $availableComponents = [];
        foreach ($availableTemplates as $templatePath) {
            // Remove template root path
            foreach ($this->getTemplatePaths()->getTemplateRootPaths() as $rootPath) {
                if (str_starts_with($templatePath, $rootPath)) {
                    $templatePath = substr($templatePath, strlen($rootPath));
                    break;
                }
            }
            // Convert template name into ViewHelper name and validate directory structure
            // (resolveTemplateName() in reverse)
            $fragments = explode('/', $templatePath);
            $availableComponents[] = implode('.', array_map(lcfirst(...), $fragments));
        }
        return $availableComponents;
    }
}
