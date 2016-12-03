<?php

/*
 * EXAMPLE: ViewHelper resolving by namespace
 *
 * This example shows how to use a collection of
 * ViewHelpers by referencing their class namespace
 * from a template, by use of the special Fluid
 * namespace entry.
 */

require __DIR__ . '/include/view_init.php';
require_once __DIR__ . '/include/class_customviewhelper.php';

// We alias our only ViewHelper so we can access it using multiple names.
if (!class_exists('TYPO3Fluid\\FluidExample\\ViewHelpers\\Nested\\CustomViewHelper')) {
    class_alias('TYPO3Fluid\\FluidExample\\ViewHelpers\\CustomViewHelper', 'TYPO3Fluid\\FluidExample\\ViewHelpers\\Nested\\CustomViewHelper');
}

// Assigning the template path and filename to be rendered. Doing this overrides
// resolving normally done by the TemplatePaths and directly renders this file.
$view->getTemplatePaths()->setTemplatePathAndFilename(__DIR__ . '/Resources/Private/Singles/NamespaceResolving.html');

// Rendering the View: plain old rendering of single file, no bells and whistles.
$output = $view->render();

// Output using helper from view_init.php
example_output($output);
