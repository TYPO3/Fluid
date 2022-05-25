<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * EXAMPLE: Usage of syntax structures
 *
 * This example shows you how to use the structural
 * helps that come with TYPO3.Fluid - such as conditions
 * in various forms, switches, sections, etc.
 */

use TYPO3Fluid\FluidExamples\Helper\ExampleHelper;

require_once __DIR__ . '/../vendor/autoload.php';

$exampleHelper = new ExampleHelper();
$view = $exampleHelper->init();

// Assigning the template path and filename to be rendered. Doing this overrides
// resolving normally done by the TemplatePaths and directly renders this file.
$paths = $view->getTemplatePaths();
$view->getTemplatePaths()->setTemplatePathAndFilename(__DIR__ . '/Resources/Private/Singles/Structures.html');

$view->assign('dynamicSection', 'Dynamic');
$view->assign('notTrue', false);
$view->assign('notTrueEither', false);
$view->assign('butTrue', true);
$view->assign('switchValue', 3);
$view->assign('secondSwitchValue', 'b');
$view->assign('array', ['one', 'two', 'three']);
$view->assign('group', [
    ['property' => 'one'],
    ['property' => 'one'],
    ['property' => 'two']
]);

// Rendering the View: plain old rendering of single file, no bells and whistles.
$output = $view->render();

$exampleHelper->output($output);
