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

// Assigning View variables: each variable defined using `assign()` can be used in
// the template as {variable}. Variables can be assigned individually like here,
// or in bulk using `assignMultiple` which accepts an associative array of template
// variables to assign.
$view->assign('foobar', 'MVC template');

// Rendering the View: in this example we are explicitly rendering the "Default"
// controller action on the "Default" controller (Fluid assumes you use MVC).
// To customise the context, instantiate \TYPO3Fluid\Fluid\Core\Rendering\RenderingContext
// and set your context settings (controller name, action, etc) and then override
// the context using `$view->setRenderingContext($context)`.
// Output of Controller "Default" action "Default":
$output = $view->render('Default');
$exampleHelper->output($output);

// We now illustrate how to change the Controller name that gets used when templates
// are resolved - making the controller name "Other". We are still rendering the
// action "Default".
// Output of Controller "Other" action "Default":
$view->getRenderingContext()->setControllerName('Other');
$output = $view->render('Default');
$exampleHelper->output($output);

// Finally, we illustrate how to change the action that gets rendered.
// Output of Controller "Other" action "List":
$output = $view->render('List');
$exampleHelper->output($output);

// NB: in a normal MVC context you usually would not be reusing the same View
// instance for all these renderings - normally, you would be creating a fresh
// View for each controller or each controller action, depending on your needs.
// However, the View does support rendering multiple controllers and actions through
// the same instance - internally, reusing every TemplatePaths, ViewHelperResolver
// and other classes involved in rendering the View.
