<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\TemplateScanner;

use AppendIterator;
use CallbackFilterIterator;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * @internal
 */
final readonly class TemplateScanner
{
    public function scanTemplateFilesForIssues(array $templates, ?RenderingContextInterface $baseRenderingContext = null): array
    {
        $baseRenderingContext ??= new RenderingContext();
        $results = [];
        foreach ($templates as $template) {
            $deprecations = $errors = [];
            set_error_handler(
                function (int $errno, string $errstr, string $errfile, int $errline) use (&$deprecations): bool {
                    $deprecations[] = new Deprecation($errfile, $errline, $errstr);
                    return true;
                },
                E_USER_DEPRECATED,
            );

            $renderingContext = clone $baseRenderingContext;
            $renderingContext->setViewHelperResolver($renderingContext->getViewHelperResolver()->getScopedCopy());
            $templatePaths = $renderingContext->getTemplatePaths();
            $templatePaths->setTemplatePathAndFilename($template);
            $templateIdentifier = $templatePaths->getTemplateIdentifier();
            $parsedTemplate = null;
            try {
                $parsedTemplate = $renderingContext->getTemplateParser()->parse(
                    $templatePaths->getTemplateSource(),
                    $templateIdentifier,
                );
            } catch (\Exception $e) {
                $errors[] = $e;
            }

            restore_error_handler();
            $results[$template] = new TemplateScannerResult(
                $templateIdentifier,
                $template,
                $errors,
                $deprecations,
                $parsedTemplate,
            );
        }
        return $results;
    }

    /**
     * @param string[] $paths
     * @param string[] $fileExtensions
     * @return string[]
     */
    public function findTemplatesInPaths(array $paths, array $fileExtensions = ['html', 'txt', 'xml']): array
    {
        if ($paths === [] || $fileExtensions === []) {
            return [];
        }
        $appendIterator = new AppendIterator();
        foreach ($paths as $path) {
            $directoryIterator = new RecursiveDirectoryIterator($path, FilesystemIterator::FOLLOW_SYMLINKS | FilesystemIterator::SKIP_DOTS);
            $recursiveIterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::SELF_FIRST);
            $appendIterator->append($recursiveIterator);
        }
        $filterIterator = new CallbackFilterIterator(
            $appendIterator,
            fn(SplFileInfo $current, $key, $iterator): bool => $this->validateFileExtension($current, $fileExtensions),
        );
        return array_keys(iterator_to_array($filterIterator));
    }

    /**
     * @param string[] $fileExtensions
     */
    private function validateFileExtension(SplFileInfo $file, array $fileExtensions): bool
    {
        foreach ($fileExtensions as $extension) {
            if ($extension === '*') {
                return true;
            }
            if (str_ends_with($file->getBasename(), '.' . $extension)) {
                return true;
            }
        }
        return false;
    }
}
