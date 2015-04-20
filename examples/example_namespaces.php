<?php

/*
 * EXAMPLE: Namespaces in Fluid templates
 *
 * How to import and use namespaces in Fluid.
 *
 * This example also shows how to import and alias
 * namespaces in a manner suited for XSD-based
 * autocompletion.
 */

require __DIR__ . '/include/view_init.php';

// Assigning the template path and filename to be rendered. Doing this overrides
// resolving normally done by the TemplatePaths and directly renders this file.
$view->getTemplatePaths()->setTemplatePathAndFilename(__DIR__ . '/Resources/Private/Singles/Namespaces.html');

// Rendering the View: we don't specify the optional `$action` parameter for the
// `render()` method - and internally, the View doesn't try to resolve an action
// name because an action is irrelevant when rendering a file directly.
$output = $view->render();

// Output using helper from view_init.php
example_output($output);
