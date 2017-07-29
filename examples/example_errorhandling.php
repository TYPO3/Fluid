<?php

/*
 * EXAMPLE: Error Handling
 *
 * Shows an example of a custom error handling class which
 * converts parsing/rendering exceptions to more friendly
 * error messages.
 */

require __DIR__ . '/include/view_init.php';

// Switch the error handler to the "TolerantErrorHandler" which basically turns
// errors which would normally break the rendering and throw an exception, into
// plain string errors which inform of the problem as inline text in the template.
$view->getRenderingContext()->setErrorHandler(new \TYPO3Fluid\Fluid\Core\ErrorHandler\TolerantErrorHandler());

// Assigning the template path and filename to be rendered. Doing this overrides
// resolving normally done by the TemplatePaths and directly renders this file.
$view->getTemplatePaths()->setTemplatePathAndFilename(__DIR__ . '/Resources/Private/Singles/ErrorHandling.html');

// Rendering the View: we don't specify the optional `$action` parameter for the
// `render()` method - and internally, the View doesn't try to resolve an action
// name because an action is irrelevant when rendering a file directly.
$output = $view->render();

// Output using helper from view_init.php
example_output($output);
