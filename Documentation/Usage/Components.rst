.. include:: /Includes.rst.txt

.. _components:

==========
Components
==========

..  versionadded:: Fluid 4.3

Fluid's component feature allows you to map ViewHelper tags to individual Fluid
templates. This means that you can effectively create your own HTML elements, which
can be used across the whole project. The concept is similar to popular frontend
frameworks like React and Vue or native Web Components, but they are server-rendered
by PHP.

At first glance, components are quite similar to partials: They are separate
template files that can be reused by other templates and thus avoid duplicate code.
However, they have two major advantages over partials, which make them much
more reusable:

1.  Components can be used in any template, without manual configuration of
    `partialRootPaths` in the template's rendering context.

2.  By default, components have a strict API (using the
    :ref:`<f:argument> ViewHelper <typo3fluid-fluid-argument>`),
    making them less error-prone.

.. _components-setup:

Basic Setup
===========

Before templates can be used as components, an initial setup process is
necessary: A :php:`ComponentCollection` class is required, which allows you to
define which template folder contains your component templates. Fluid comes with
a base implementation in :php:`AbstractComponentCollection` that already
covers the most common use cases. However, it is also possible to
customize the component context, such as providing global settings that
should be available in all component templates.

A basic implementation looks like this:

..  code-block:: php
    namespace Vendor\MyPackage\Components;

    use TYPO3Fluid\Fluid\Core\Component\AbstractComponentCollection;
    use TYPO3Fluid\Fluid\View\TemplatePaths;

    final class ComponentCollection extends AbstractComponentCollection
    {
        public function getTemplatePaths(): TemplatePaths
        {
            $templatePaths = new TemplatePaths();
            $templatePaths->setTemplateRootPaths([
                'path/to/Components/',
            ]);
            return $templatePaths;
        }
    }

.. _components-definition:

Defining Components
===================

The default implementation in :php:`AbstractComponentCollection` specifies that
each component template needs to be placed in a separate folder. The purpose of this
decision is that related asset files (such as CSS or JS) can  be placed right
next to the component's template, which fosters a modular frontend
architecture and enables easier refactoring.

The :xml:`<my:atom.button>` component thus would be defined in
`path/to/Components/Atom/Button/Button.html` like this:

..  code-block:: xml
    <f:argument name="variant" type="string" optional="{true}" default="primary" />

    <button class="myButton myButton--{variant}">
        <f:slot />
    </button>

The :ref:`<f:slot> ViewHelper <typo3fluid-fluid-slot>` can be used to access the
children of the calling ViewHelper.

.. _components-usage:

Using Components
================

Once the :php:`ComponentCollection` class exists and the component template has
been created, it can be imported into any Fluid template and ViewHelper tags can
be used to render components:

..  code-block:: xml
    {namespace my=Vendor\MyPackage\Components\ComponentCollection}

    <my:atom.button variant="secondary">
        Button label
    </my:atom.button>

Of course this also works with :ref:`alternative ways of importing namespaces <viewhelper-namespaces>`.

This example would result in the following rendered HTML:

..  code-block:: html
    <button class="myButton myButton--secondary">
        Button label
    </button>

Combining Components
--------------------

Components can also be nested:

..  code-block:: xml
    {namespace my=Vendor\MyPackage\Components\ComponentCollection}

    <my:atom.button variant="secondary">
        <my:atom.icon name="submit" />
        Button label
    </my:atom.button>

An alternative approach would be to call the icon component from within the
button component and to extend the button API accordingly:

..  code-block:: xml
    {namespace my=Vendor\MyPackage\Components\ComponentCollection}

    <my:atom.button variant="secondary" icon="submit">
        Button label
    </my:atom.button>

The extended button component could look something like this:

..  code-block:: xml
    {namespace my=Vendor\MyPackage\Components\ComponentCollection}

    <f:argument name="variant" type="string" optional="{true}" default="primary" />
    <f:argument name="icon" type="string" optional="{true}" />

    <button class="myButton myButton--{variant}">
        <f:if condition="{icon}">
            <my:atom.icon name="{icon}" />
        </f:if>
        <f:slot />
    </button>

..  note::
    IDE autocomplete for all available components via XSD files, similar to ViewHelpers,
    is not implemented yet, but is planned for a future release.

.. _components-context:

Providing Context
=================

Sometimes it might be helpful to provide some global settings to all components
within one component collection. One common use case could be to provide design
tokens from a JSON file to your components.

The :php:`AbstractComponentCollection` provides the `getAdditionalVariables()`,
which allows you to do just that:

..  code-block:: php
    namespace Vendor\MyPackage\Components;

    use TYPO3Fluid\Fluid\Core\Component\AbstractComponentCollection;

    final class ComponentCollection extends AbstractComponentCollection
    {
        private ?array $designTokens = null;

        // ...

        public function getAdditionalVariables(string $viewHelperName): array
        {
            $this->designTokens ??= json_decode(file_get_contents('path/to/designTokens.json'), true);
            return [
                'designTokens' => $this->designTokens,
            ];
        }
    }

In your component templates you would then be able to access those tokens:

..  code-block:: xml
    <f:argument name="color" type="string" optional="{true}" default="brand" />

    <div style="background-color: {designTokens.colors.{color}}"></div>

.. _components-arbitrary-arguments:

Allowing Arbitrary Arguments
============================

By default, components only accept arguments that are defined explicitly via
the :ref:`<f:argument> ViewHelper <typo3fluid-fluid-argument>`. However, there might
be use cases where you would like to accept arbitrary arguments.

This is possible by defining :php:`additionalArgumentsAllowed()` in your
:php:`ComponentCollection` implementation (in this example for all components
in the collection):

..  code-block:: php
    namespace Vendor\MyPackage\Components;

    use TYPO3Fluid\Fluid\Core\Component\AbstractComponentCollection;

    final class ComponentCollection extends AbstractComponentCollection
    {
        // ...

        protected function additionalArgumentsAllowed(string $viewHelperName): bool
        {
            return true;
        }
    }

The following call of the button component would then be valid:

..  code-block:: xml
    {namespace my=Vendor\MyPackage\Components\ComponentCollection}

    <my:atom.button something="my text">
        Button label
    </my:atom.button>

In the component, `{something}` would be available as an additional variable.

.. _components-folder-structure:

Alternative Folder Structure
============================

If you want to define an alternative folder structure to the default
`{componentName}/{componentName}.html`, you can do so by providing a custom
implementation of :php:`resolveTemplateName()` in your :php:`ComponentCollection`.
The following example skips the additional folder per component:

..  code-block:: php
    namespace Vendor\MyPackage\Components;

    use TYPO3Fluid\Fluid\Core\Component\AbstractComponentCollection;

    final class ComponentCollection extends AbstractComponentCollection
    {
        // ...

        public function resolveTemplateName(string $viewHelperName): string
        {
            $fragments = array_map(ucfirst(...), explode('.', $viewHelperName));
            return implode('/', $fragments);
        }
    }

`<my:atom.button>` would be resolved to `path/to/Components/Atom/Button.html`.
