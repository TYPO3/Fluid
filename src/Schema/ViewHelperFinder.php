<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Schema;

use Composer\Autoload\ClassLoader;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * @internal
 */
final class ViewHelperFinder
{
    private const FILE_SUFFIX = 'ViewHelper.php';

    private ViewHelperMetadataFactory $viewHelperMetadataFactory;

    public function __construct(?ViewHelperMetadataFactory $viewHelperMetadataFactory = null)
    {
        $this->viewHelperMetadataFactory = $viewHelperMetadataFactory ?? new ViewHelperMetadataFactory();
    }

    /**
     * @return ViewHelperMetadata[]
     */
    public function findViewHelpersInComposerProject(ClassLoader $autoloader): array
    {
        $viewHelpers = [];
        foreach ($autoloader->getPrefixesPsr4() as $namespace => $paths) {
            foreach ($paths as $path) {
                $viewHelpers = array_merge($viewHelpers, $this->findViewHelperFilesInPath($namespace, $path));
            }
        }
        return $viewHelpers;
    }

    /**
     * @return ViewHelperMetadata[]
     */
    private function findViewHelperFilesInPath(string $namespace, string $path): array
    {
        $viewHelpers = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_PATHNAME),
        );
        foreach ($iterator as $filePath) {
            // Naming convention: ViewHelper files need to have "ViewHelper" suffix
            if (!str_ends_with((string)$filePath, self::FILE_SUFFIX)) {
                continue;
            }

            // Guesstimate PHP namespace based on file path
            $pathInPackage = substr($filePath, strlen($path) + 1, -4);
            $className = $namespace . str_replace('/', '\\', $pathInPackage);
            $phpNamespace = substr($className, 0, strrpos($className, '\\'));

            // Make sure that we generated the correct namespace for the file;
            // This prevents duplicate class declarations if files are part of
            // multiple/overlapping namespaces
            // The alternative would be to use PHP-Parser for the whole finding process,
            // but then we would have to check for correct interface implementation of
            // ViewHelper classes manually
            $phpCode = file_get_contents($filePath);
            if (!preg_match('#namespace\s+' . preg_quote($phpNamespace, '#') . '\s*;#', $phpCode)) {
                continue;
            }

            try {
                $viewHelpers[] = $this->viewHelperMetadataFactory->createFromViewhelperClass($className);
            } catch (\InvalidArgumentException) {
                // Just ignore this class
            }
        }
        return $viewHelpers;
    }
}
