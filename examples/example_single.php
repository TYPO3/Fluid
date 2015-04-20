<?php

/*
 * EXAMPLE: Single file rendering
 *
 * This example shows how to render a single Fluid
 * template and illustrates how the use of Layouts
 * and Partials in directly rendered files still is
 * subject to the paths resolving.
 *
 * The alternative to this approach is MVC - see
 * other example for that.
 */

require __DIR__ . '/include/view_init.php';

// Assigning View variables: each variable defined using `assign()` can be used
// used in the template as {variable}. Variables can be assigned individually
// like here, or in bulk using `assignMultiple` which accepts an associative
// array of template variables to assign.
$view->assign('foobar', 'Single template');

// Assigning the template path and filename to be rendered. Doing this overrides
// resolving normally done by the TemplatePaths and directly renders this file.
$view->getTemplatePaths()->setTemplatePathAndFilename(__DIR__ . '/Resources/Private/Singles/Single.html');

// Rendering the View: we don't specify the optional `$action` parameter for the
// `render()` method - and internally, the View doesn't try to resolve an action
// name because an action is irrelevant when rendering a file directly.
$output = $view->render();

// Output using helper from view_init.php
example_output($output);
