<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * EXAMPLE: ViewHelper Performance
 *
 * This example tests the performance of ViewHelper calls
 * in a template file. This mostly tests cached template
 * performance, since only the first run will be uncached.
 * However, this is the relevant real-world performance metric.
 */

use TYPO3Fluid\FluidExamples\Helper\ExampleHelper;

require_once __DIR__ . '/../vendor/autoload.php';

$exampleHelper = new ExampleHelper();

$runs = 200;
$items = 500;
$subitems = 500;

$testArray = array_fill(0, $items, array_map(fn($value) => $value . PHP_EOL . $value, range(1, $subitems)));
$starttime = microtime(true);

for ($i = 0; $i < $runs; $i++) {
    $view = $exampleHelper->init();

    $paths = $view->getRenderingContext()->getTemplatePaths();
    $view->getRenderingContext()->getTemplatePaths()->setTemplatePathAndFilename(__DIR__ . '/Resources/Private/Singles/Performance.fluid.html');

    $view->assign('testArray', $testArray);
    $view->assign('testString', 'lorem ipsum');

    $view->render();
}

$exampleHelper->output(sprintf(
    'Template parsed %d times with %d items and each %d subitems. Total execution time: %.04f seconds',
    $runs,
    $items,
    $subitems,
    microtime(true) - $starttime,
));
