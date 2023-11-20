<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\FluidExamples\Helper;

use TYPO3Fluid\Fluid\Core\Cache\SimpleFileCache;
use TYPO3Fluid\Fluid\View\TemplateView;

class ExampleHelper
{
    public function init(): TemplateView
    {
        // Initializing the View: rendering in Fluid takes place through a View instance
        // which contains a RenderingContext that in turn contains things like definitions
        // of template paths, instances of variable containers and similar.
        $view = new TemplateView();

        // TemplatePaths object: a subclass can be used if custom resolving is wanted.
        $paths = $view->getTemplatePaths();

        // Configuring paths: explicit setters used in this example. Paths can also
        // be passed as a ["templateRootPaths" => ["path1/", "path2/"]] constructor
        // argument for this implementation of TemplatePaths. When `TYPO3.Fluid`
        // reads these paths they are read in reverse and the first matching file
        // is used - meaning that if you have the same file in both `TemplatesA`
        // and `TemplatesB` and render that using this MVC approach, you will be
        // rendering the file located in `TemplatesB` because this folder was last
        // and is checked first (think of these paths as prioritised fallbacks).
        $paths->setTemplateRootPaths([
            __DIR__ . '/../../Resources/Private/Templates/'
        ]);
        $paths->setLayoutRootPaths([
            __DIR__ . '/../../Resources/Private/Layouts/'
        ]);
        $paths->setPartialRootPaths([
            __DIR__ . '/../../Resources/Private/Partials/'
        ]);

        // Configure View's caching to use system temp dir (typically /tmp und unix) as caching directory.
        $cachePath = sys_get_temp_dir() . '/' . 'fluid-examples';
        if (!is_dir($cachePath)) {
            mkdir($cachePath);
        }
        $view->setCache(new SimpleFileCache($cachePath));

        return $view;
    }

    /**
     * Tiny helper to output a plain string directly to console.
     */
    public function output(string $content): void
    {
        echo '#' . PHP_EOL;
        echo '# Output of rendered template (' . number_format(strlen($content)) . ' bytes)' . PHP_EOL;
        echo '#' . PHP_EOL;
        echo trim($content) . PHP_EOL . PHP_EOL;
    }

    public function cleanup(): void
    {
        $cachePath = sys_get_temp_dir() . '/' . 'fluid-examples';
        if (!is_dir($cachePath)) {
            return;
        }
        (new SimpleFileCache($cachePath))->flush();
    }
}
