<?php
// Define a cache directory if one is not set
$FLUID_CACHE_DIRECTORY = !isset($FLUID_CACHE_DIRECTORY) ? __DIR__ . '/../cache/' : $FLUID_CACHE_DIRECTORY;

if (!class_exists(TYPO3Fluid\Fluid\View\TemplateView::class)) {
    foreach ([__DIR__ . '/../../vendor/autoload.php', __DIR__ . '/../../../../autoload.php'] as $possibleAutoloadLocation) {
        if (file_exists($possibleAutoloadLocation)) {
            require_once $possibleAutoloadLocation;
        }
    }
}

// Initializing the View: rendering in Fluid takes place through a View instance
// which contains a RenderingContext that in turn contains things like definitions
// of template paths, instances of variable containers and similar.
$view = new \TYPO3Fluid\Fluid\View\TemplateView();

// TemplatePaths object: a subclass can be used if custom resolving is wanted.
$paths = $view->getTemplatePaths();

// Configuring paths: explicit setters used in this example. Paths can also
// be passed as a ["templateRootPaths" => ["path1/", "path2/"]] constructor
// argument for this implementation of TemplatePaths. When `TYPO3.Fluid`
// reads these paths they are read in reverse and the first matching file
// is used - meaning that if you have the same file in both `TemplatesA`
// and `TemplatesB` and render that using this MVC approach, you will be
// rendering the file located in `TemplatesB` becase this folder was last
// and is checked first (think of these paths as prioritised fallbacks).
$paths->setTemplateRootPaths([
    __DIR__ . '/../Resources/Private/Templates/'
]);
$paths->setLayoutRootPaths([
    __DIR__ . '/../Resources/Private/Layouts/'
]);
$paths->setPartialRootPaths([
    __DIR__ . '/../Resources/Private/Partials/'
]);

if ($FLUID_CACHE_DIRECTORY) {
    // Configure View's caching to use ./examples/cache/ as caching directory.
    $view->setCache(new \TYPO3Fluid\Fluid\Core\Cache\SimpleFileCache($FLUID_CACHE_DIRECTORY));
}


/**
 * Tiny helper that outputs a plain string in a nice way,
 * directly to console.
 *
 * @param string $content
 * @return void
 */
if (!function_exists('example_output')) {
    function example_output($content)
    {
        $content = trim($content);
        echo PHP_EOL . $content . PHP_EOL . PHP_EOL;
        echo '# Sir\'s template is rendered above (';
        echo number_format(strlen($content)) . ' bytes).' . PHP_EOL;
    }
}
