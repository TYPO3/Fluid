<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * EXAMPLE: Multiple paths
 *
 * How to use multiple paths for template files
 * in order to avoid copying an entire collection
 * of files to override individual ones.
 *
 * Renders an overridden "Default.html" template
 * which illustrates how rendering of Partials
 * works with multiple paths. Uses a "Default.html"
 * Layout which also is overridden in the secondary
 * path for Layouts.
 */

use TYPO3Fluid\Fluid\View\TemplatePaths;
use TYPO3Fluid\FluidExamples\Helper\ExampleHelper;

require_once __DIR__ . '/../vendor/autoload.php';

$exampleHelper = new ExampleHelper();
$view = $exampleHelper->init();

// Filling template paths using a complete array which contains multiple paths.
// This approach can be used to source path definitions when the configuration
// is stored in a configuration that can be converted to an array.
// We are adding two path locations: the original one which acts as fallback
// plus the secondary one, ResourceOverrides, which contains overrides for some
// template files but not all. The ResourceOverrides naming is optional;
// usually you would be using the same name for the Resources folder, but point
// the overrides to a path in, for example, another package's Resources folder.
// Specifying this array can also be done as constructor argument for the
// TemplatePaths class which can be passed to the View; see view_init.php.
$view->getRenderingContext()->getTemplatePaths()->fillFromConfigurationArray([
    TemplatePaths::CONFIG_TEMPLATEROOTPATHS => [
        __DIR__ . '/Resources/Private/Templates/',
        __DIR__ . '/ResourceOverrides/Private/Templates/',
    ],
    TemplatePaths::CONFIG_LAYOUTROOTPATHS => [
        __DIR__ . '/Resources/Private/Layouts/',
        __DIR__ . '/ResourceOverrides/Private/Layouts/',
    ],
    TemplatePaths::CONFIG_PARTIALROOTPATHS => [
        __DIR__ . '/Resources/Private/Partials/',
        __DIR__ . '/ResourceOverrides/Private/Partials/',
    ],
]);

$view->assign('foobar', 'This is foobar');
$view->assign('baz', 'This is baz');

// Rendering the View: in this example we are explicitly rendering the "Default"
// controller action on the "Default" controller, but our template path structure
// means that the template file that gets used will be the one from our folder
// containing overridden templates.
$output = $view->render('Default');

$exampleHelper->output($output);
