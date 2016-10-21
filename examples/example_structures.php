<?php

/*
 * EXAMPLE: Usage of syntax structures
 *
 * This example shows you how to use the structural
 * helps that come with TYPO3.Fluid - such as conditions
 * in various forms, switches, sections, etc.
 */

require __DIR__ . '/include/view_init.php';

// Assigning the template path and filename to be rendered. Doing this overrides
// resolving normally done by the TemplatePaths and directly renders this file.
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

// Output using helper from view_init.php
example_output($output);
