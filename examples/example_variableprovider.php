<?php

/*
 * EXAMPLE: VariableProvider usage
 *
 * This example shows you how to use dynamic/custom
 * variables and variable provisioning in Fluid
 * templates via a VariableProvider class capable of
 * providing variables in exactly the way you want.
 */

require __DIR__ . '/include/view_init.php';
require_once __DIR__ . '/include/class_customvariableprovider.php';

// Assigning View variables: we assign variables that will be used by the
// expressions we build in this example.
$dynamic1 = 'DYN1'; // used as dynamic part when accessing other variables
$dynamic2 = 'DYN2'; // used as dynamic part when accessing other variables

// Assigning the template path and filename to be rendered. Doing this overrides
// resolving normally done by the TemplatePaths and directly renders this file.
$paths->setTemplatePathAndFilename(__DIR__ . '/Resources/Private/Singles/VariableProvider.html');

// Assigning a custom VariableProvider which will return two variables:
// $incrementer and $random; the former automatically increments every
// time it is accessed and the latter generating a random checksum string.
$view->getRenderingContext()->setVariableProvider(new \TYPO3Fluid\Fluid\Tests\Example\CustomVariableProvider());

// Rendering the View: plain old rendering of single file, no bells and whistles.
$output = $view->render();

// Output using helper from view_init.php
example_output($output);
