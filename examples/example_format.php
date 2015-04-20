<?php

/*
 * EXAMPLE: Format other than HTML
 *
 * This example shows how to render template files
 * in a format different from the default HTML.
 */

require __DIR__ . '/include/view_init.php';

// Assigning the template path and filename to be rendered. Doing this overrides
// resolving normally done by the TemplatePaths and directly renders this file.
$view->getTemplatePaths()->setFormat('json');

// Rendering the View: we don't specify the optional `$action` parameter for the
// `render()` method - and internally, the View doesn't try to resolve an action
// name because an action is irrelevant when rendering a file directly.
$view->assign('foobar', 'Variable foobar');
$output = $view->render('OtherFormat');

// Output using helper from view_init.php
example_output($output);
