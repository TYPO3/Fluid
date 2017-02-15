<?php

/*
 * EXAMPLE: Use of escaping modifier
 *
 * This example shows how to use the `{escaping on|off|true|false}`
 * modifier in templates. Using the modifier switches escaping off
 * for the entire template file parsing.
 */

require __DIR__ . '/include/view_init.php';

// Assigning View variables: one variable containing HTML
$view->assign('html', '<strong>This is not escaped</strong>');

// Assigning the template path and filename to be rendered. Doing this overrides
// resolving normally done by the TemplatePaths and directly renders this file.
$view->getTemplatePaths()->setTemplatePathAndFilename(__DIR__ . '/Resources/Private/Singles/EscapingModifier.html');

// Rendering the View: we don't specify the optional `$action` parameter for the
// `render()` method - and internally, the View doesn't try to resolve an action
// name because an action is irrelevant when rendering a file directly.
$output = $view->render();

// Output using helper from view_init.php
example_output($output);
