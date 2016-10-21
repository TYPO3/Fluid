<?php

/*
 * EXAMPLE: Usage of conditions
 *
 * This example shows you how to use conditions
 * in the Fluid template language, how they behave
 * and which syntax they support.
 */

require __DIR__ . '/include/view_init.php';

// Assigning View variables: we assign variables that will be used by the
// expressions we build in this example. Refer to the names of these
// variables to understand what goes on.
$view->assign('vartrue', true);
$view->assign('varfalse', false);
$view->assign('vararray1', ['foo' => 'bar']);
$view->assign('vararray2', ['bar' => 'foo']);
$view->assign('checkTernary', true);
$view->assign('ternaryTrue', 'The ternary expression is TRUE');
$view->assign('ternaryFalse', 'The ternary expression is FALSE');

// Assigning the template path and filename to be rendered. Doing this overrides
// resolving normally done by the TemplatePaths and directly renders this file.
$paths->setTemplatePathAndFilename(__DIR__ . '/Resources/Private/Singles/Conditions.html');

// Rendering the View: plain old rendering of single file, no bells and whistles.
$output = $view->render();

// Output using helper from view_init.php
example_output($output);
