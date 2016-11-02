<?php

/*
 * EXAMPLE: Parsing modifier
 *
 * This example shows you how to use the "parsing off"
 * modifier in template files to disable all Fluid
 * parsing. Useful when for example you want to render
 * a template or partial file without processing it as
 * Fluid, but still being able to render it (=reading
 * and passing through) using `f:render`.
 */

require __DIR__ . '/include/view_init.php';

// Assigning the template path and filename to be rendered. Doing this overrides
// resolving normally done by the TemplatePaths and directly renders this file.
$paths->setTemplatePathAndFilename(__DIR__ . '/Resources/Private/Singles/Passthrough.html');

// Rendering the View: plain old rendering of single file, no bells and whistles.
$output = $view->render();

// Output using helper from view_init.php
example_output($output);
