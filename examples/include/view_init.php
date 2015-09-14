<?php
// Define a cache directory if one is not set
$FLUID_CACHE_DIRECTORY = !isset($FLUID_CACHE_DIRECTORY) ? __DIR__ . '/../cache/' : $FLUID_CACHE_DIRECTORY;

// Use Composer's autoloader to handle our class loading.
require_once __DIR__ . '/../../vendor/autoload.php';

// TemplatePaths object: a subclass can be used if custom resolving is wanted.
$paths = new \TYPO3Fluid\Fluid\View\TemplatePaths();

// Configuring paths: explicit setters used in this example. Paths can also
// be passed as a ["templateRootPaths" => ["path1/", "path2/"]] constructor
// argument for this implementation of TemplatePaths. When `TYPO3.Fluid`
// reads these paths they are read in reverse and the first matching file
// is used - meaning that if you have the same file in both `TemplatesA`
// and `TemplatesB` and render that using this MVC approach, you will be
// rendering the file located in `TemplatesB` becase this folder was last
// and is checked first (think of these paths as prioritised fallbacks).
$paths->setTemplateRootPaths(array(
	__DIR__ . '/../Resources/Private/Templates/'
));
$paths->setLayoutRootPaths(array(
	__DIR__ . '/../Resources/Private/Layouts/'
));
$paths->setPartialRootPaths(array(
	__DIR__ . '/../Resources/Private/Partials/'
));

// Initializing the View: rendering in Fluid takes place through a View instance
// which is given the TemplatePaths that resolve files and uses these as sources
// for the rendering engine. As with TemplatePaths, custom implementations of
// this View can be created to change the format from HTML to XML, assign some
// default values, add additional ViewHelper namespaces, etc.
$view = new \TYPO3Fluid\Fluid\View\TemplateView($paths);

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
	function example_output($content) {
		$content = trim($content);
		echo PHP_EOL . $content . PHP_EOL . PHP_EOL;
		echo '# Sir\'s template is rendered above (';
		echo number_format(strlen($content)) . ' bytes).' . PHP_EOL;
	}
}
