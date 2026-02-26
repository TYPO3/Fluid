<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\View;

use AppendIterator;
use CallbackFilterIterator;
use FilesystemIterator;
use Iterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * @internal
 */
final readonly class TemplateFinder
{
    /**
     * @param string[] $paths
     * @return string[]
     */
    public function findTemplatesWithFluidFileExtension(array $paths): array
    {
        if ($paths === []) {
            return [];
        }
        $filterIterator = new CallbackFilterIterator(
            $this->createFileIterator($paths),
            fn(SplFileInfo $file): bool => str_contains($file->getBasename(), '.' . TemplatePaths::FLUID_EXTENSION . '.'),
        );
        return array_keys(iterator_to_array($filterIterator));
    }

    /**
     * @param string[] $paths
     * @return string[]
     */
    public function findTemplatesByFileExtension(array $paths, string $fileExtension, bool $stripFileExtension = false): array
    {
        if ($paths === []) {
            return [];
        }
        $filterIterator = new CallbackFilterIterator(
            $this->createFileIterator($paths),
            fn(SplFileInfo $file): bool => str_ends_with($file->getBasename(), '.' . $fileExtension),
        );
        $files = array_keys(iterator_to_array($filterIterator));
        return $stripFileExtension
            ? array_map(fn($file) => $this->stripFileExtension($file, $fileExtension), $files)
            : $files;
    }

    /**
     * @param string[] $paths
     */
    private function createFileIterator(array $paths): Iterator
    {
        $appendIterator = new AppendIterator();
        foreach ($paths as $path) {
            $directoryIterator = new RecursiveDirectoryIterator($path, FilesystemIterator::FOLLOW_SYMLINKS | FilesystemIterator::SKIP_DOTS);
            $recursiveIterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::SELF_FIRST);
            $appendIterator->append($recursiveIterator);
        }
        return $appendIterator;
    }

    private function stripFileExtension(string $path, string $fileExtension): string
    {
        $fileExtension = '.' . $fileExtension;
        $fluidExtension = '.' . TemplatePaths::FLUID_EXTENSION;
        $path = substr($path, 0, - strlen($fileExtension));
        if (str_ends_with($path, $fluidExtension)) {
            $path = substr($path, 0, - strlen($fluidExtension));
        }
        return $path;
    }
}
