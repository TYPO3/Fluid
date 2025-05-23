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

..  deprecated:: 4.4
    Prevously, it was possible to set the layout of a template with the special
    variable `layoutName`. This will no longer work with Fluid 5. Please use the
    `<f:layout> ViewHelper <https://docs.typo3.org/permalink/fluid:typo3fluid-fluid-layout>`_
    instead.

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

Most of your options for extending the Fluid language - like adding new ways
to format strings, to make special condition types or to render custom links -
are implemented as ViewHelpers. ViewHelpers are the special PHP classes that can
be called directly from a Fluid template:

..  code-block:: xml
    <f:format.trim>{somestring}</f:format.trim>

A ViewHelper is essentially referenced by the namespace alias and the name of the
ViewHelper, in this case `f` being the namespace alias and `format.trim` being
the name. The alias refers to a namespace definition, which is provided either directly
in the template file or via the PHP API of the :php:`ViewHelperResolver`, see
:ref:`Registering/importing ViewHelpers <viewhelper-namespaces>`.

The :php:`ViewHelperResolver` is the class responsible for turning those pieces
of information into an expected class name. By default, ViewHelpers are resolved
by combining a defined ViewHelper namespace with the ViewHelper name to a fully
qualified PHP class name: The ViewHelper class.

..  code-block:: xml
    {namespace my=Vendor\MyPackage\ViewHelpers}

    <my:foo.bar />

The `<my:foo.bar />` ViewHelper would be resolved to the ViewHelper class
`Vendor\MyPackage\ViewHelpers\Foo\BarViewHelper`.

.. _viewhelperresolver-delegates:

ViewHelperResolver delegates
----------------------------

..  versionadded:: Fluid 4.3

In most cases, it shouldn't be necessary to replace the default
:php:`ViewHelperResolver` with a custom implementation, since the default resolving
logic can be modified per ViewHelper namespace by defining a custom resolver delegate:

..  code-block:: php
    namespace Vendor\MyPackage;

    use TYPO3Fluid\Fluid\Core\ViewHelper\UnresolvableViewHelperException;
    use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolverDelegateInterface;

    final class CustomViewHelperResolverDelegate implements ViewHelperResolverDelegateInterface
    {
        public function resolveViewHelperClassName(string $viewHelperName): string
        {
            // Generate a ViewHelper class name based on the ViewHelper name
            $className = $this->generateViewHelperClassName($viewHelperName);
            // If the ViewHelper name is invalid, throw UnresolvableViewHelperException
            if (!class_exists($className)) {
                throw new UnresolvableViewHelperException('Class ' . $className . ' does not exist.', 1750667093);
            }
            return $className;
        }

        public function getNamespace(): string
        {
            return self::class;
        }
    }

If that namespace is used in a template, the custom resolver delegate will
be used to resolve the ViewHelper tag to the appropriate ViewHelper implementation:

..  code-block:: xml
    {namespace my=Vendor\MyPackage\CustomViewHelperResolverDelegate}

    <my:foo />

Note that the fully qualified class name of the delegate is used as ViewHelper
namespace in the template. Fluid first checks if the specified PHP namespace refers
to an existing PHP class. As a fallback, the default ViewHelper resolving logic is
used.

ViewHelper instantiation
------------------------

The main use case for replacing the :php:`ViewHelperResolver` with a custom
class is to influence the way Fluid instantiates ViewHelper classes or
ViewHelperResolver delegates. The concrete implmementation heavily depends on
your use case, but in general you would extend the built-in class and
override the methods you want to customize:

..  code-block:: php
    namespace Vendor\MyPackage;

    use Psr\Container\ContainerInterface;
    use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperCollection;
    use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;
    use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
    use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolverDelegateInterface;

    final class MyViewHelperResolver extends ViewHelperResolver
    {
        public function __construct(
            private readonly ContainerInterface $container,
        ) {}

        public function createViewHelperInstanceFromClassName(string $viewHelperClassName): ViewHelperInterface
        {
            // Use dependency injection container to fetch ViewHelper instance
            return $this->container->get($viewHelperClassName);
        }

        public function createResolverDelegateInstanceFromClassName(string $delegateClassName): ViewHelperResolverDelegateInterface
        {
            // Use dependency injection container to fetch ViewHelperResolver delegate instance
            if (!$this->container->has($delegateClassName)) {
                return new ViewHelperCollection($delegateClassName);
            }
            return $this->container->get($delegateClassName);
        }
    }

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
