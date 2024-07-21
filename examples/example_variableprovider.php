<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * EXAMPLE: VariableProvider usage
 *
 * This example shows you how to use dynamic/custom
 * variables and variable provisioning in Fluid
 * templates via a VariableProvider class capable of
 * providing variables in exactly the way you want.
 */

use TYPO3Fluid\FluidExamples\CustomVariableProvider;
use TYPO3Fluid\FluidExamples\Helper\ExampleHelper;

require_once __DIR__ . '/../vendor/autoload.php';

$exampleHelper = new ExampleHelper();
$view = $exampleHelper->init();

// Assigning View variables: we assign variables that will be used by the
// expressions we build in this example.
$dynamic1 = 'DYN1'; // used as dynamic part when accessing other variables
$dynamic2 = 'DYN2'; // used as dynamic part when accessing other variables

// Assigning the template path and filename to be rendered. Doing this overrides
// resolving normally done by the TemplatePaths and directly renders this file.
$paths = $view->getRenderingContext()->getTemplatePaths();
$paths->setTemplatePathAndFilename(__DIR__ . '/Resources/Private/Singles/VariableProvider.html');

// Assigning a custom VariableProvider which will return two variables:
// $incrementer and $random; the former automatically increments every
// time it is accessed and the latter generating a random checksum string.
$view->getRenderingContext()->setVariableProvider(new CustomVariableProvider());

// Rendering the View: plain old rendering of single file, no bells and whistles.
$output = $view->render();

$exampleHelper->output($output);
