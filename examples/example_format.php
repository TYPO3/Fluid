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
$paths->setFormat('json');

// Rendering the View: we use the $action argument for the render() method in
// order to let the internal TemplatePaths object resolve our file paths while
// respecting the special format we defined.
$view->assign('foobar', 'Variable foobar');
$output = $view->render('OtherFormat');

// Output using helper from view_init.php
example_output($output);
