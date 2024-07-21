<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * EXAMPLE: Parsing modifier
 *
 * This example shows you how to use the "parsing off"
 * modifier in template files to disable all Fluid
 * parsing. Useful when for example you want to render
 * a template or partial file without processing it as
 * Fluid, but still being able to render it (=reading
 * and passing through) using `f:render`.
 */

use TYPO3Fluid\FluidExamples\Helper\ExampleHelper;

require_once __DIR__ . '/../vendor/autoload.php';

$exampleHelper = new ExampleHelper();
$view = $exampleHelper->init();

// Assigning the template path and filename to be rendered. Doing this overrides
// resolving normally done by the TemplatePaths and directly renders this file.
$paths = $view->getRenderingContext()->getTemplatePaths();
$paths->setTemplatePathAndFilename(__DIR__ . '/Resources/Private/Singles/Passthrough.html');

// Rendering the View: plain old rendering of single file, no bells and whistles.
$output = $view->render();

$exampleHelper->output($output);
