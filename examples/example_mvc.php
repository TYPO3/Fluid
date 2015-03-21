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
// To customise the context, instantiate \TYPO3\Fluid\Core\Rendering\RenderingContext
// and set your context settings (controller name, action, etc) and then override
// the context using `$view->setRenderingContext($context)`.
// The resulting file we reference is `./Templates(A|B)/Default/Default.html`
// depending on which of the `TemplatesA` or `TemplatesB` folders contains the
// expected file.
$output = $view->render('Default');

// Output using helper from view_init.php
example_output($output);
