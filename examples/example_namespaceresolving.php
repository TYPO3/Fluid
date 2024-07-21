<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * EXAMPLE: ViewHelper resolving by namespace
 *
 * This example shows how to use a collection of
 * ViewHelpers by referencing their class namespace
 * from a template, by use of the special Fluid
 * namespace entry.
 */

use TYPO3Fluid\FluidExamples\Helper\ExampleHelper;

require_once __DIR__ . '/../vendor/autoload.php';

$exampleHelper = new ExampleHelper();
$view = $exampleHelper->init();

// Assigning the template path and filename to be rendered. Doing this overrides
// resolving normally done by the TemplatePaths and directly renders this file.
$view->getRenderingContext()->getTemplatePaths()->setTemplatePathAndFilename(__DIR__ . '/Resources/Private/Singles/NamespaceResolving.html');

// Rendering the View: plain old rendering of single file, no bells and whistles.
$output = $view->render();

$exampleHelper->output($output);
