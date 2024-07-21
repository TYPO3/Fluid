<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * EXAMPLE: Format other than HTML
 *
 * This example shows how to render template files
 * in a format different from the default HTML.
 */

use TYPO3Fluid\FluidExamples\Helper\ExampleHelper;

require_once __DIR__ . '/../vendor/autoload.php';

$exampleHelper = new ExampleHelper();
$view = $exampleHelper->init();

// Assigning the template path and filename to be rendered. Doing this overrides
// resolving normally done by the TemplatePaths and directly renders this file.
$paths = $view->getRenderingContext()->getTemplatePaths();
$paths->setFormat('json');

// Rendering the View: we use the $action argument for the render() method in
// order to let the internal TemplatePaths object resolve our file paths while
// respecting the special format we defined.
$view->assign('foobar', 'Variable foobar');
$output = $view->render('OtherFormat');

$exampleHelper->output($output);
