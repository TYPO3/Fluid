<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * EXAMPLE: Using conditions
 *
 * This example shows you how to use conditions
 * in the Fluid template language, how they behave
 * and which syntax they support.
 */

use TYPO3Fluid\FluidExamples\Helper\ExampleHelper;

require_once __DIR__ . '/../vendor/autoload.php';

$exampleHelper = new ExampleHelper();
$view = $exampleHelper->init();

// Assigning View variables: we assign variables that will be used by the
// expressions we build in this example. Refer to the names of these
// variables to understand what goes on.
$view->assign('vartrue', true);
$view->assign('varfalse', false);
$view->assign('vararray1', ['foo' => 'bar']);
$view->assign('vararray2', ['bar' => 'foo']);
$view->assign('checkTernary', true);
$view->assign('ternaryTrue', 'The ternary expression is true');
$view->assign('ternaryFalse', 'The ternary expression is false');
$view->assign('asArray', [
    'nested' => [
        'then' => 'Dotted variable true',
        'else' => 'Dotted variable false',
        'check' => true,
    ],
]);

// Assigning the template path and filename to be rendered. Doing this overrides
// resolving normally done by the TemplatePaths and directly renders this file.
$paths = $view->getTemplatePaths();
$paths->setTemplatePathAndFilename(__DIR__ . '/Resources/Private/Singles/Conditions.html');

// Rendering the View: plain old rendering of single file, no bells and whistles.
$output = $view->render();

$exampleHelper->output($output);
