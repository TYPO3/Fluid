.. include:: /Includes.rst.txt

.. _implementations:
.. _integrating-fluid:

=================
Integrating Fluid
=================

Fluid provides a standard implementation which works great on simple MVC
frameworks and as standalone rendering engine. However, the standard
implementation may lack certain features needed by the product into which you
are integrating Fluid.

To make sure you are able to override key behaviors of Fluid the package
will delegate much of the resolving, instantiation, argument mapping and
rendering of ViewHelpers to special classes which can be both manipulated and
overridden by the user. These special classes and their use cases are:

..  contents::

.. _templateview:

TemplateView
============

A fairly standard View implementation. The default object expects
:php:`TemplatePaths` as constructor argument and has a handful of utility methods
like :php:`$view->assign('variablename', 'value');`. Custom View types can be
implemented by subclassing the default class - but in order to avoid
problems, make sure you also call the original class' constructor method.

Creating a custom View allows you to change just a few aspects, mainly about
composition: which implementations of `TemplatePaths` the View requires, if it
needs a :ref:`custom ViewHelperResolver <viewhelperresolver>`,
if it must have some default variables, if it should have a default cache, etc.

..  note::

    The special variable `layoutName` is reserved and can be assigned to a
    template to set its Layout instead of using `<f:layout name="LayoutName" />`.

.. _templatepaths:

TemplatePaths
=============

In the default :php:`TemplatePaths` object included with Fluid we provide a
set of conventions for resolving the template files that go into rendering a
Fluid template - the templates themselves, plus partials and layouts.

You should use the default :php:`TemplatePaths` object if:

1.  You are able to place your template files in folders that match the
    Fluid conventions, including the convention of subfolders named the
    same as your controllers.
2.  You are able to provide the template paths that get used as an array with
    which :php:`TemplatePaths` can be initialized.
3.  Or you are able to individually set each group of paths.
4.  You are able to rely on standard format handling (`format` simply being the
    file extension of template files).

And you should replace the :php:`TemplatePaths` with your own subclass if:

1.  You answered no to any of the above.
2.  You want to be able to deliver template content before parsing, from other
    sources than files.
3.  You want the resolving of template files for controller actions to happen in
    a different way.
4.  You want to create other (caching-) identifiers for your partials, layouts
    and templates than defaults.

Whether you use your own class or the default, the :php:`TemplatePaths` instance
*must be provided as first argument for the View*.

.. _renderingcontext:

RenderingContext
================

The rendering context is *the* state object in Fluid's rendering process.
By default, it contains references to all other objects that are relevant
in the rendering process, such as the :php:`TemplateParser`, the :php:`TemplateCompiler`,
a :php:`StandardVariableProvider` or the :php:`TemplatePaths` mentioned above.
It also contains information about the current template context, somewhat
confusingly stored in :php:`controllerName` and :php:`controllerAction` due to the
MVC origins of Fluid.

Since Fluid 2.14, it is also possible to add arbitrary data to the rendering
context, which obsoletes most cases where you would have to override the
rendering context implementation in Fluid integrations:

..  code-block:: php

    $myCustomState = new \Vendor\Package\MyClass::class();
    $view->getRenderingContext()->setAttribute(\Vendor\Package\MyClass::class, $myCustomState);


If at all possible, it should be avoided to use a custom `RenderingContext`
implementation. However, currently it might still be necessary for some cases,
for example if you want to replace the default implementation of one of the other
dependencies, such as the :php:`StandardVariableProvider`.

With further refactoring, we try to provide better ways for these use cases in the future.

.. _fluidcache:

FluidCache
==========

The caching of Fluid templates happens by compiling the templates to PHP files
which execute much faster than a parsed template ever could. These compiled
templates can only be stored if a :php:`FluidCacheInterface`-implementing object is
provided. Fluid provides one such caching implementation: the
:php:`SimpleFileCache` which just stores compiled PHP code in a designated directory.

Should you need to store the compiled templates in other ways you can implement
:php:`FluidCacheInterface` in your caching object.

Whether you use your own cache class or the default, the `FluidCache`
*must be passed as third parameter for the View* or it
*must be assigned using :php:`$view->getRenderingContext()->setCache($cacheInstance)`
before calling :php:`$view->render()`*.

.. _viewhelperinvoker:

ViewHelperInvoker
=================

The :php:`ViewHelperInvoker` is a class dedicated to validating current arguments of
and if valid, calling the ViewHelper's render method. It is the primary API to
execute a ViewHelper from within PHP code. The default object
supports the arguments added via :php:`initializeArguments()` and
:php:`registerArgument()` on the ViewHelper and provides all additional arguments
via :php:`handleAdditionalArguments()` to the ViewHelper class. By default, the
ViewHelper implementations throw an exception, but this handling can be overwritten,
as demonstrated by :php:`AbstractTagBasedViewHelper`.

You should replace the :php:`ViewHelperInvoker` if:

1.  You must support different ways of calling ViewHelpers such as alternative
    `setArguments` names.
2.  You wish to change the way the invoker uses and stores ViewHelper instances,
    for example to use an internal cache.
3.  You wish to change the way ViewHelper arguments are validated, for example
    changing the Exceptions that are thrown.
4.  You wish to perform processing on the output of ViewHelpers, for example to
    remove XSS attempts according to your own rules.

..  note::

    ViewHelper instance creation and argument retrieval is handled by the
    :ref:`ViewHelperResolver <viewhelperresolver>`.

.. _implementation-view-helper-resolver:
.. _viewhelperresolver:

ViewHelperResolver
==================

In Fluid most of your options for extending the language - for example,
adding new ways to format strings, to make special condition types, custom links
and such - are implemented as ViewHelpers. These are the special classes that are
called using for example
:xml:`<f:format.htmlentities>{somestring}</f:format.htmlentities>`.

A ViewHelper is essentially referenced by the namespace and the path to the
ViewHelper, in this case `f` being the namespace and `format.htmlentities` being
the path.

The :php:`ViewHelperResolver` is the class responsible for turning these two pieces
of information into an expected class name and when this class is resolved, to
retrieve from it the arguments you can use for each ViewHelper.

You should use the default :php:`ViewHelperResolver` if:

1.  You can rely on the default way of turning a namespace and path of a
    ViewHelper into a class name.
2.  You can rely on the default way ViewHelpers return the arguments they
    support.
3.  You can rely on instantiation of ViewHelpers happening through a simple
    `new $class()`.

You should replace the :php:`ViewHelperResolver` if:

1.  You answered no to any of the above.
2.  You want to make ViewHelper namespaces available in templates without
    importing.
3.  You want to use the dependency injection of your framework to resolve
    and instantiate ViewHelper objects.
4.  You want to change which class is resolved from a given namespace and
    ViewHelper path, for example allowing you to add your own ViewHelpers to the
    default namespace or replace default ViewHelpers with your own.
5.  You want to change the argument retrieval from ViewHelpers or you want to
    manipulate the arguments (for example, giving them a default value, making
    them optional, changing their data type).

The default :php:`ViewHelperResolver` can be replaced on the rendering context by calling
:php:`$renderingContext->setViewHelperResolver($resolverInstance);`.

.. _templateprocessor:

TemplateProcessor
=================

While custom :ref:`TemplatePaths <templatepaths>` also allows sources
of template files to be modified before they are given to the TemplateParser, a
custom :php:`TemplatePaths` implementation is sometimes overkill - and has the drawback
of completely overruling the reading of template file sources and making it up to
the custom class how exactly this processing happens.

In order to allow a more readily accessible and flexible way of pre-processing
template sources and affect key aspects of the parsing process, a
:php:`TemplateProcessorInterface` is provided. Implementing this interface and the
methods it designates allows your class to be passed to the :php:`TemplateView` and
be triggered every time a template source is parsed, right before parsing
starts:

.. code-block:: php

    $myTemplateProcessor = new MyTemplateProcessor();
    $myTemplateProcessor->setDoMyMagicThing(true);
    $templateView->setTemplateProcessors([
        $myTemplateProcessor
    ]);

The registration method requires an array - this is to let you define multiple
processors without needing to wrap them in a single class as well as reuse
validation/manipulation across frameworks and only replace the parts that need
to be replaced.

This makes the method :php:`preProcessSource($templateSource)` be called on this
class every time the TemplateParser is asked to parse a Fluid template.
Modifying the source and returning it makes that new template source be used.
Inside the TemplateProcessor method you have access to the TemplateParser and
ViewHelperResolver instances which the View uses.

The result is that TemplateProcessor instances are able to, for example:

*   Validate template sources and implement reporting/logging of errors in a framework.
*   Fix things like character encoding issues in template sources.
*   Process Fluid code from potentially untrusted sources, for example doing XSS
    removals before parsing.
*   Extract legacy namespace definitions and assign those to the
    ViewHelperResolver for active use.
*   Extract legacy escaping instruction headers and assign those to the
    TemplateParser's Configuration instance.
*   Enable the use of custom template code in file's header, extracted and used
    by a framework.

Note again: these same behaviors are possible using a custom :php:`TemplatePaths`
implementation - but even with such a custom implementation this
TemplateProcessor pattern can still be used to manipulate/validate the sources
coming from :php:`TemplatePaths`, providing a nice way to decouple paths resolving
from template source processing.
