<?php

/*
 * EXAMPLE: Cache warmup using fallback variables
 *
 * This example shows how to add variables which only apply when the
 * template code is being compiled specifically via cache warmup, which
 * differs from normal rendering in that variables may potentially be
 * missing (e.g. assigned by a controller class which is not consulted
 * by the warmup process).
 *
 * Useful when a particular template piece will fail or throw an error
 * if a variable is not assigned - e.g. when rendering sections or partials
 * with dynamic names coming from variables. In those cases, if no variable
 * was explicitly defined as part of the cache warmup process or if no
 * variable can be resolved from whichever RenderingContext (and therefore
 * VariableProvider) was specified for the cache warmup.
 *
 * See the "fluid" CLI script that comes with this library!
 */

require __DIR__ . '/include/view_init.php';

// Assigns a required variable (reference to the name of a section which
// gets rendered WITHOUT the "optional" flag.
$view->assign('dynamicName', 'Works');

// Assigning the template path and filename to be rendered. Doing this overrides
// resolving normally done by the TemplatePaths and directly renders this file.
$view->getTemplatePaths()->setTemplatePathAndFilename(__DIR__ . '/Resources/Private/Singles/CacheWarmup.html');

// Rendering the View: we don't specify the optional `$action` parameter for the
// `render()` method - and internally, the View doesn't try to resolve an action
// name because an action is irrelevant when rendering a file directly.
$output = $view->render();

// Output using helper from view_init.php
example_output($output);
