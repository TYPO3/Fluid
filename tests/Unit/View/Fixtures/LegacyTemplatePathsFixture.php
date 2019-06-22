<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\View\Fixtures;

use TYPO3Fluid\Fluid\View\TemplatePaths;

/**
 * Legacy implementation of TemplatePaths with original method
 * signatures to protect against breaking changes until this
 * fixture is removed.
 */
class LegacyTemplatePathsFixture extends TemplatePaths
{
    public function resolveAvailableTemplateFiles($controllerName, $format = self::DEFAULT_FORMAT): array
    {
        return parent::resolveAvailableTemplateFiles($controllerName, $format);
    }

    public function resolveAvailablePartialFiles($format = self::DEFAULT_FORMAT): array
    {
        return parent::resolveAvailablePartialFiles($format);
    }

    public function resolveAvailableLayoutFiles($format = self::DEFAULT_FORMAT): array
    {
        return parent::resolveAvailableLayoutFiles($format);
    }

    protected function resolveFilesInFolders(array $folders, $format): array
    {
        return parent::resolveFilesInFolders($folders, $format);
    }

    protected function resolveFilesInFolder($folder, $format): array
    {
        return parent::resolveFilesInFolder($folder, $format);
    }

    public function getLayoutIdentifier($layoutName = 'Default'): string
    {
        return parent::getLayoutIdentifier($layoutName);
    }

    public function getLayoutSource($layoutName = 'Default'): string
    {
        return parent::getLayoutSource($layoutName);
    }

    public function getTemplateIdentifier($controller = 'Default', $action = 'Default'): string
    {
        return parent::getTemplateIdentifier($controller, $action);
    }

    public function getTemplateSource($controller = 'Default', $action = 'Default'): string
    {
        return parent::getTemplateSource($controller, $action);
    }

    public function getLayoutPathAndFilename($layoutName = 'Default'): string
    {
        return parent::getLayoutPathAndFilename($layoutName);
    }

    public function getPartialIdentifier($partialName): string
    {
        return parent::getPartialIdentifier($partialName);
    }

    public function getPartialSource($partialName): string
    {
        return parent::getPartialSource($partialName);
    }

    public function getPartialPathAndFilename($partialName): string
    {
        return parent::getPartialPathAndFilename($partialName);
    }

    protected function resolveFileInPaths(array $paths, $relativePathAndFilename, $format = self::DEFAULT_FORMAT): string
    {
        return parent::resolveFileInPaths($paths, $relativePathAndFilename, $format);
    }
}
