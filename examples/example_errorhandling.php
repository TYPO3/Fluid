<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * EXAMPLE: Error Handling
 *
 * Shows an example of a custom error handling class which
 * converts parsing/rendering exceptions to more friendly
 * error messages.
 */

use TYPO3Fluid\Fluid\Core\ErrorHandler\TolerantErrorHandler;
use TYPO3Fluid\FluidExamples\Helper\ExampleHelper;

require_once __DIR__ . '/../vendor/autoload.php';

$exampleHelper = new ExampleHelper();
$view = $exampleHelper->init();

// Switch the error handler to the "TolerantErrorHandler" which basically turns
// errors which would normally break the rendering and throw an exception, into
// plain string errors which inform of the problem as inline text in the template.
$view->getRenderingContext()->setErrorHandler(new TolerantErrorHandler());

// Assigning the template path and filename to be rendered. Doing this overrides
// resolving normally done by the TemplatePaths and directly renders this file.
$view->getTemplatePaths()->setTemplatePathAndFilename(__DIR__ . '/Resources/Private/Singles/ErrorHandling.html');

// Rendering the View: we don't specify the optional `$action` parameter for the
// `render()` method - and internally, the View doesn't try to resolve an action
// name because an action is irrelevant when rendering a file directly.
$output = $view->render();

$exampleHelper->output($output);
