<?php

/*
 * EXAMPLE: Custom ViewHelperResolver
 *
 * This example shows you an example of how a custom
 * ViewHelperResolver class may be implemented to
 * manipulate the resolving of ViewHelpers done by
 * the View. See include/class_customviewhelperresolver.php
 * and include/class_customlinkviewhelper.php for the
 * Resolver and a single ViewHelper class that this
 * Resolver adds in the default `f:` namespace as
 * `f:myLink` which has a `page` argument to illustrate
 * how a custom ViewHelper might accept arbitrary
 * variables to render things like framework links.
 */

if (!defined('FLUID_CACHE_DIRECTORY')) {
    define('FLUID_CACHE_DIRECTORY', __DIR__ . '/cache/');
}

require __DIR__ . '/include/view_init.php';
require_once __DIR__ . '/include/class_customviewhelperresolver.php';
require_once __DIR__ . '/include/class_customviewhelper.php';

// We tell the View to use our custom ViewHelperResolver for all class
// and argument resolving operations. This class lets us use a ViewHelper
// through the default namespace without that ViewHelper being in the
// default package. The ViewHelper is added dynamically as `f:myLink`.
// See CustomViewHelperResolver class for details.
$view->getRenderingContext()->setViewHelperResolver(new \TYPO3Fluid\Fluid\Tests\Example\CustomViewHelperResolver());

// Assigning the template path and filename to be rendered. Doing this overrides
// resolving normally done by the TemplatePaths and directly renders this file.
$paths->setTemplatePathAndFilename(__DIR__ . '/Resources/Private/Singles/CustomResolving.html');

// Rendering the View: plain old rendering of single file, no bells and whistles.
$output = $view->render();

// Output using helper from view_init.php
example_output($output);
