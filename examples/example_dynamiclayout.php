<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * EXAMPLE: MVC pattern used with TYPO3.Fluid
 *
 * This examples shows how TYPO3.Fluid is integrated
 * in an MVC context, highlighting which parts may
 * be replaced in order to adapt the engine to your
 * favorite MVC framework.
 *
 * The alternative to this is single file rendering
 * - see the other example for that.
 */

use TYPO3Fluid\FluidExamples\Helper\ExampleHelper;

require_once __DIR__ . '/../vendor/autoload.php';

$exampleHelper = new ExampleHelper();
$view = $exampleHelper->init();

// Assign Layout name as ViewVariable which we will pass to f:layout as name
$view->assign('layout', 'Dynamic');

// Set the template path and filename we will render
$view->getTemplatePaths()->setTemplatePathAndFilename(__DIR__ . '/Resources/Private/Singles/DynamicLayout.html');

$output = $view->render();

$exampleHelper->output($output);
