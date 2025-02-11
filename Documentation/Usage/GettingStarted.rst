.. include:: /Includes.rst.txt

.. _getting-started:

===============
Getting Started
===============

Before you can start writing your own Fluid template files, you first need to make
sure that the environment is set up properly. If you are using a framework like
TYPO3, this might already be the case. However, if you are starting from scratch, you
need to do some preparations in PHP first:

.. _create-view:

Creating a View
===============

Fluid comes with a bunch of PHP classes that allow you to open Fluid template files,
assign variables to them and then render them to get the desired result. To get started,
you need to be aware of `TemplateView`, the `RenderingContext` and `TemplatePaths`.

*   The `TemplateView` is the "view part" in the Model-View-Controller pattern. It is the
    main API between your PHP application and the template file. Each `TemplateView` renders
    exactly one template file (which can have a layout file and can also include other templates,
    called partials).
*   The `RenderingContext` is kind of self-describing: It contains all context necessary to
    render the template. At first, you might only use it to define the path to your Fluid
    template file via ...
*   `TemplatePaths`, which is usually a sub-object of the rendering context. It provides multiple
    ways to define the location(s) to your template files.

To render a template file called `MyTemplate.html`, which is located in `/path/to/templates/`,
you first create a `TemplateView` object:

.. code-block:: php

    $view = new \TYPO3Fluid\Fluid\View\TemplateView();

Then you can access the `TemplatePaths` and provide the location to your template file, for
example like this:

.. code-block:: php

    $view->getRenderingContext()->getTemplatePaths()->setTemplateRootPaths(['/path/to/templates/']);

Finally, you can render the desired template:

.. code-block:: php

    $view->render('MyTemplate');

By default, `.html` is used as file extension. If you want to change this, you can specify a different
format in `TemplatePaths`:

.. code-block:: php

    $view->getRenderingContext()->getTemplatePaths()->setFormat('xml');
    $view->render('MyTemplate');

Which would then render `MyTemplate.xml`.

.. _assign-variables:

Assigning Variables
===================

To be able to access data available in your PHP script, you need to assign that data to your view
as a variable:

.. code-block:: php

    $myData = obtainMyDataFromSomewhere();
    $view->assign('myData', $myData);

You can also assign multiple variables at once:

.. code-block:: php

    $view->assignMultiple([
        'title' => $myTitle,
        'description' => $myDescription,
    ]);

Those variables can then be accessed in your template file:

.. code-block:: html

    <h1>{title}</h1>
    <p>{description}</p>

See also the dedicated chapter about :ref:`Variables <variables>`.

.. _using-viewhelpers

Using ViewHelpers
=================

Within Fluid templates, it is possible to use some controll structures to implement simple
logic. It is also possible to perform some data modification. Both is possible by using
so-called ViewHelpers.

With the :ref:`<f:if> ViewHelper <typo3fluid-fluid-if>`, you can implement simple if-then-else
logic, for example:

.. code-block:: xml

    <f:if condition="{title}">
        <f:then>
            <h1>{title}</h1>
        </f:then>
        <f:else>
            <h1>Default Title</h1>
        </f:else>
    </f:if>

With the :ref:`<f:format.case> ViewHelper <typo3fluid-fluid-format-case>` you can convert a
variable to uppercase or lowercase letters:

.. code-block:: xml

    <h1><f:format.case mode="upper">{title}</f:format.case></h1>

Which would result in an all-uppercase headline.

See also the dedicated chapter about :ref:`ViewHelpers <what-are-viewhelpers>` as well
as the :ref:`ViewHelper reference <viewhelper-reference>` for more details.

.. _debugging

Inspecting and Debugging Templates
==================================

If you encounter a problem in your template or you just want to know what
data is available, you can use the :ref:`<f:debug> ViewHelper <typo3fluid-fluid-debug>`:

..  code-block:: xml

    <f:debug>{myVariable}</f:debug>

If you want to get an overview of all available variables, you can use the special
variable `{_all}`:

..  code-block:: xml

    <f:debug>{_all}</f:debug>
