<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ComponentCollections;

use TYPO3Fluid\Fluid\Core\Component\AbstractComponentCollection;
use TYPO3Fluid\Fluid\Core\Component\ComponentListProviderInterface;
use TYPO3Fluid\Fluid\View\TemplatePaths;

final class ListComponentCollection extends AbstractComponentCollection implements ComponentListProviderInterface
{
    public function getTemplatePaths(): TemplatePaths
    {
        $templatePaths = new TemplatePaths();
        $templatePaths->setTemplateRootPaths([
            __DIR__ . '/../Components/',
        ]);
        return $templatePaths;
    }

    /**
     * @todo add this to AbstractComponentCollection in Fluid 6
     * @return string[]
     */
    public function getAvailableComponents(): array
    {
        $availableTemplates = $this->getTemplatePaths()->resolveAvailableTemplateFiles(null);
        $fallbackFileExtension = '.' . $this->getTemplatePaths()->getFormat();
        $fullFileExtension = '.' . TemplatePaths::FLUID_EXTENSION . $fallbackFileExtension;
        $availableComponents = [];
        foreach ($availableTemplates as $templatePath) {
            // Remove file extension
            if (str_ends_with($templatePath, $fullFileExtension)) {
                $templatePath = substr($templatePath, 0, -strlen($fullFileExtension));
            } elseif (str_ends_with($templatePath, $fallbackFileExtension)) {
                $templatePath = substr($templatePath, 0, -strlen($fallbackFileExtension));
            }
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
            $name1 = array_pop($fragments);
            $name2 = array_pop($fragments);
            if ($name1 !== $name2) {
                continue;
            }
            $fragments[] = $name2;
            $availableComponents[] = implode('.', array_map(lcfirst(...), $fragments));
        }
        return $availableComponents;
    }
}
