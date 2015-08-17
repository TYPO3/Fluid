<?php

/*
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

require __DIR__ . '/include/view_init.php';

// Assigning View variables: each variable defined using `assign()` can be used
// used in the template as {variable}. Variables can be assigned individually
// like here, or in bulk using `assignMultiple` which accepts an associative
// array of template variables to assign.
$view->assign('foobar', 'MVC template');

// Rendering the View: in this example we are explicitly rendering the "Default"
// controller action on the "Default" controller (Fluid assumes you use MVC).
// To customise the context, instantiate \TYPO3Fluid\Fluid\Core\Rendering\RenderingContext
// and set your context settings (controller name, action, etc) and then override
// the context using `$view->setRenderingContext($context)`.
$output = $view->render('Default');

// Output of Controller "Default" action "Default" using helper from view_init.php
example_output($output);

// We now illustrate how to change the Controller name that gets used when templates
// are resolved - making the controller name "Other". We are still rendering the
// action "Default":
$view->getRenderingContext()->setControllerName('Other');
$output = $view->render('Default');

// Output of Controller "Other" action "Default" using helper from view_init.php
example_output($output);

// Finally, we illustrate how to change the action that gets rendered:
$output = $view->render('List');

// Output of Controller "Other" action "List" using helper from view_init.php
example_output($output);

// NB: in a normal MVC context you usually would not be reusing the same View
// instance for all these renderings - normally, you would be creating a fresh
// View for each controller or each controller action, depending on your needs.
// However, the View does support rendering multiple controllers and actions through
// the same instance - internally, reusing every TemplatePaths, ViewHelperResolver
// and other classes involved in rendering the View.
