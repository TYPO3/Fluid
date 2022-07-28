<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * EXAMPLE: Custom ViewHelperResolver
 *
 * This example shows you an example of how a custom
 * ViewHelperResolver class may be implemented to
 * manipulate the resolving of ViewHelpers done by
 * the View. See src/CustomViewHelperResolver.php
 * and src/ViewHelper/CustomViewHelper.php for the
 * Resolver and a single ViewHelper class that this
 * Resolver adds in the default `f:` namespace as
 * `f:myLink` which has a `page` argument to illustrate
 * how a custom ViewHelper might accept arbitrary
 * variables to render things like framework links.
 */

use TYPO3Fluid\FluidExamples\CustomViewHelperResolver;
use TYPO3Fluid\FluidExamples\Helper\ExampleHelper;

require_once __DIR__ . '/../vendor/autoload.php';

$exampleHelper = new ExampleHelper();
$view = $exampleHelper->init();

// We tell the View to use our custom ViewHelperResolver for all class
// and argument resolving operations. This class lets us use a ViewHelper
// through the default namespace without that ViewHelper being in the
// default package. The ViewHelper is added dynamically as `f:myLink`.
// See CustomViewHelperResolver class for details.
$view->getRenderingContext()->setViewHelperResolver(new CustomViewHelperResolver());

// Assigning the template path and filename to be rendered. Doing this overrides
// resolving normally done by the TemplatePaths and directly renders this file.
$paths = $view->getTemplatePaths();
$paths->setTemplatePathAndFilename(__DIR__ . '/Resources/Private/Singles/CustomResolving.html');

// Rendering the View: plain old rendering of single file, no bells and whistles.
$output = $view->render();

// Output using helper from view_init.php
$exampleHelper->output($output);
