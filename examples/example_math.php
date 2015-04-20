<?php

/*
 * EXAMPLE: Mathematical expressions
 *
 * This example shows you how to use mathematical
 * expressions in the Fluid template language to
 * perform small inline calculations using variables
 * and raw numbers.
 */

require __DIR__ . '/include/view_init.php';

// Assigning View variables: we assign variables that will be used by the
// expressions we build in this example.
$view->assign('numberone', 1);
$view->assign('numbertwo', 2);
$view->assign('numberten', 10);
$view->assign('half', 0.5);

// Assigning the template path and filename to be rendered. Doing this overrides
// resolving normally done by the TemplatePaths and directly renders this file.
$view->getTemplatePaths()->setTemplatePathAndFilename(__DIR__ . '/Resources/Private/Singles/Math.html');

// Rendering the View: plain old rendering of single file, no bells and whistles.
$output = $view->render();

// Output using helper from view_init.php
example_output($output);
