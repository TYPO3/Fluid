.. include:: /Includes.rst.txt

:navigation-title: Template File Structure

.. _template-file-structure:

=============================
Fluid Template File Structure
=============================

Fluid knows three different types of template files: **Templates**, **Layouts**
and **Partials**. Templates are always the starting point and thus are always
required, while both Partials and Layouts are optional. However, these can be
very helpful to improve the structure of your templates and can avoid duplicate
code, which makes maintenance much easier.

**Layouts** are a flexible method to reuse HTML markup that should wrap around
multiple template files. You could for example extract your header and footer
markup to a layout file and only keep the content in-between in your template.
Layouts automatically have access to all variables defined within the template.

**Partials** are an easy way to abstract and reuse code snippets in your templates.
They don't have access to all template variables, instead the required variables
need to be provided to the partial when it is used.

..  note::
    When referring to these files, the names are used with an uppercase starting
    letter (i.e., the name of the type). When referring to any file containing Fluid, the
    term "templates" is sometimes used (i.e., lowercase starting letter), and in this
    case refers to all of the above types as a group.

Inside Templates and Partials, the :ref:`<f:section> ViewHelper <typo3fluid-fluid-section>`
can be used to define sections that can be rendered using the
:ref:`<f:render> ViewHelper <typo3fluid-fluid-render>`. Both Templates and Partials
may define and render sections, but Layouts may **only render** sections and partials.

.. _templatepaths-api:

The API
=======

Fluid uses a class called `TemplatePaths` which is part of the view's `RenderingContext`
and can resolve and deliver template file paths and sources.
In order to change the default paths you can set new ones in the `TemplatePaths`
associated with your `RenderingContext`:

.. code-block:: php

    // Create your view
    $view = new \TYPO3Fluid\Fluid\View\TemplateView();
    // set up paths object with arrays of paths with files
    $paths = $view->getRenderingContext()->getTemplatePaths();
    $paths->setTemplateRootPaths(['/path/to/templates/']);
    $paths->setLayoutRootPaths(['/path/to/layouts/']);
    $paths->setPartialRootPaths(['/path/to/partials/']);

Note that paths are *always defined as arrays*. In the default `TemplatePaths`
implementation, Fluid supports lookups in multiple template file locations -
which is very useful if you are rendering template files from another package
and wish to replace just a few template files. By adding your own template files
path *last in the paths arrays*, Fluid will check those paths *first*.

.. _templates:

Templates
=========

In Fluid, Templates can be referenced in two different ways:

* Directly by file path and filename
* Resolved using a controller name and action (and format)

Direct usage is of course done by simply setting the full path to the template
file that must be rendered; no magic in that.

In an MVC (model-view-controller) context the latter can be used to implement a
universal way to resolve the template files so you do not have to set the file
path and filename for each file you want to render. In this case, Fluid will
resolve template by using the pattern `{$templateRootPath}/{$controllerName}/{$actionName}.{$format}`
with all of these variables coming directly from the `TemplatePaths` instance -
which means that by filling the `TemplatePaths` instance with information about
your MVC context you can have Fluid automatically resolve the paths of template
files associated with controller actions.

Templates may or may not use a layout. The layout can be indicated by the use of
`<f:layout name="LayoutName" />` in the template source.

Fluid will behave slightly differently when a template uses a layout and when it
does not:

* When no Layout is used, *the template is rendered directly* and will output
  everything not contained in an `<f:section>`
* When a Layout is used, *the Template itself is not rendered directly*.
  Instead, the Template defines any number of `<f:section>` containers which
  contain the pieces that will be rendered from the layout using `<f:render>`

You can choose freely between using a layout and not using one - even when
rendering templates in an MVC context, some templates might use layouts and
others might not. Whether or not you use layouts of course depends on the
design you are trying to convey.

* `An example Template without a Layout <https://github.com/TYPO3/Fluid/blob/main/examples/Resources/Private/Singles/LayoutLess.html>`__
* `An example Template with a Layout <https://github.com/TYPO3/Fluid/blob/main/examples/Resources/Private/Templates/Default/Default.html>`__ and the
  `Layout used by that Template <https://github.com/TYPO3/Fluid/blob/main/examples/Resources/Private/Layouts/Default.html>`__

.. _layouts:

Layouts
=======

Layouts are as the name implies a layout for composing the individual bits of
the design. When your design uses a shared HTML design with just smaller pieces
being interchangeable (which most web applications do) your layout can contain
the container HTML and the individual templates can define the smaller design
bits that get used by the layout.

The template in this case defines a number of `<f:section>` containers which the
layout renders with `<f:render>`. In application terms, the rendering engine
switches to the layout when it detects one and renders it while preserving the
template's context of controller name and action name.

* `An example Layout <https://github.com/TYPO3/Fluid/blob/main/examples/Resources/Private/Layouts/Default.html>`__ and
  `Template which uses it <https://github.com/TYPO3/Fluid/blob/main/examples/Resources/Private/Templates/Default/Default.html>`__

.. _partials:

Partials
========

Partials are the smallest design bits that you can use when you need to have
reusable bits that can be rendered from multiple templates, layouts or even
other partials. To name a few types of design bits that make sense as partials:

* Address renderings
* Lists rendered from arrays
* Article metadata blocks
* Structured data markup

The trick with partials is they can expect a generically named but predictably
structured object (such as an `Address` domain object instance, an array of string
values, etc). When rendering the Partial, the data can then be picked from any
source that fulfills the requirements. In the example of an `Address`, such an
object might be found on both a `Person` and a `Company`, in which case we can
render the same partial but with different sources:

* `<f:render partial="Address" arguments="{address: person.address}" />`
* `<f:render partial="Address" arguments="{address: company.address}" />`

The partial then expects the variable `{address}` with all the properties
required to render an address; street, city, etc.

A partial may or may not contain `<f:section>`. If it does contain `<f:section>`
containers, then the contents of those containers can be rendered anywhere,
including inside the Partial itself, by `<f:render partial="NameOfPartial" section="NameOfSection" />`.
Partials without sections can be rendered by just
`<f:render partial="NameOfPartial" />` (with or without `arguments`).

* `An example of a partial template without sections <https://github.com/TYPO3/Fluid/blob/main/examples/Resources/Private/Partials/FirstPartial.html>`__
* `An example of a partial template with sections <https://github.com/TYPO3/Fluid/blob/main/examples/Resources/Private/Partials/Structures.html>`__

.. _template-argument-definitions:

Argument Definitions
====================

..  versionadded:: Fluid 4.2

Templates, layouts and partials can define requirements for variables by
using the :ref:`<f:argument> ViewHelper <typo3fluid-fluid-argument>`. It is
possible to define a variable as required or optional. Also, a specific
type can be required. If any of the constraints don't match the supplied
data, an exception is thrown.

In effect, this allows you to define an API for template files, which
improves both documentation and reusability of template files, especially
partials.
