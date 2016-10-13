<?php

/*
 * EXAMPLE: Static string caching of template code
 *
 * This example shows how to force a chunk of template
 * code to be compiled not as a set of converted nodes
 * but as a static string, resulting in that string
 * being directly output when the compiled template is
 * rendered. Doing this also prevents arguments and child
 * nodes from being compiled and is the best possible
 * performance for template code that does not depend on
 * dynamic variables (e.g. always produce the same output).
 */

require __DIR__ . '/include/view_init.php';

// Assigning View variables: each variable defined using `assign()` can be used
// used in the template as {variable}. Variables can be assigned individually
// like here, or in bulk using `assignMultiple` which accepts an associative
// array of template variables to assign.
$view->assign('foobar', 'Cached as static text');

// Assigning the template path and filename to be rendered. Doing this overrides
// resolving normally done by the TemplatePaths and directly renders this file.
$view->getTemplatePaths()->setTemplatePathAndFilename(__DIR__ . '/Resources/Private/Singles/CacheStatic.html');

// Rendering the View: we don't specify the optional `$action` parameter for the
// `render()` method - and internally, the View doesn't try to resolve an action
// name because an action is irrelevant when rendering a file directly.
$output = $view->render();

// Output using helper from view_init.php
example_output($output);
