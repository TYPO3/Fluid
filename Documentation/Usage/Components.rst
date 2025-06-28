.. include:: /Includes.rst.txt

.. _components:

==========
Components
==========

..  versionadded:: Fluid 4.3

Fluid's components are custom HTML-like tags based on Fluid templates that you can
reuse throughout your project. The concept is similar to popular frontend
frameworks like React and Vue or native Web Components, but they are server-rendered
by PHP.

At first glance, components are quite similar to partials: They are separate
template files that can be reused by other templates and thus avoid duplicate code.
However, they have two major advantages over partials, which make them much
more reusable:

1.  Components can be used in any template, without manual configuration of
    `partialRootPaths` in the template's rendering context.

2.  Components have strict, typed definitions of arguments via their API (using the
    :ref:`<f:argument> ViewHelper <typo3fluid-fluid-argument>`), making them less
    error-prone.

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
    :caption: ComponentCollection.php

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

..  tip::
    In the context of TYPO3, it is recommend to put components into `Resources/Private/Components/`
    in a central extension and to provide that path in your :php:`ComponentCollection` (see code example) via

    ..  code-block:: php
        use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

        ExtensionManagementUtility::extPath('my_sitepackage', 'Resources/Private/Components');

.. _components-definition:

Defining Components
===================

The default implementation in :php:`AbstractComponentCollection` specifies that
each component template needs to be placed in a separate folder. The purpose of this
decision is that related asset files (such as CSS or JS) can  be placed right
next to the component's template, which fosters a modular frontend
architecture and enables easier refactoring.

..  note::
    In the following examples, `atomic design <https://bradfrost.com/blog/post/atomic-web-design/>`_
    is used to demonstrate that components can be structured in subfolders.
    You can use any structure that works best for your project.

..  directory-tree::

    * :path:`path/to/Components/`
        * :path:`Atom`
            * :path:`Button`
                * :file:`Button.html`

Check out :ref:`components-folder-structure` if you want to adjust this.

The :xml:`<my:atom.button>` component thus would be defined in
`path/to/Components/Atom/Button/Button.html` like this:

..  code-block:: xml
    :caption: Button.html (component definition)

    <f:argument name="variant" type="string" optional="{true}" default="primary" />

    <button class="myButton myButton--{variant}">
        <f:slot />
    </button>

The :ref:`<f:slot> ViewHelper <typo3fluid-fluid-slot>` can be used to access the
children of the component tag.

.. _components-usage:

Using Components
================

Once the :php:`ComponentCollection` class exists and the component template has
been created, it can be imported into any Fluid template and component tags
can be used:

..  code-block:: xml
    :caption: MyTemplate.html

    <html
        xmlns:my="http://typo3.org/ns/Vendor/MyPackage/Components/ComponentCollection"
        data-namespace-typo3-fluid="true"
    >

    <my:atom.button variant="secondary">
        Button label
    </my:atom.button>

Of course this works with all :ref:`alternatives for importing namespaces <viewhelper-namespaces>`.

This example would result in the following rendered HTML:

..  code-block:: html
    :caption: Rendered result

    <button class="myButton myButton--secondary">
        Button label
    </button>

Combining Components
--------------------

Components can also be nested:

..  code-block:: xml
    :caption: MyTemplate.html

    <html
        xmlns:my="http://typo3.org/ns/Vendor/MyPackage/Components/ComponentCollection"
        data-namespace-typo3-fluid="true"
    >

    <my:atom.button variant="secondary">
        <my:atom.icon name="submit" />
        Button label
    </my:atom.button>

An alternative approach that prevents the need for nesting would be to call the `atom.icon`
component from within the `atom.button` component and to extend its argument API accordingly:

..  code-block:: xml
    :caption: MyTemplate.html

    <html
        xmlns:my="http://typo3.org/ns/Vendor/MyPackage/Components/ComponentCollection"
        data-namespace-typo3-fluid="true"
    >

    <my:atom.button variant="secondary" icon="submit">
        Button label
    </my:atom.button>

The extended `atom.button` component could look something like this:

..  code-block:: xml
    :caption: Button.html (component definition)

    <html
        xmlns:my="http://typo3.org/ns/Vendor/MyPackage/Components/ComponentCollection"
        data-namespace-typo3-fluid="true"
    >

    <f:argument name="variant" type="string" optional="{true}" default="primary" />
    <f:argument name="icon" type="string" optional="{true}" />

    <button class="myButton myButton--{variant}">
        <f:if condition="{icon}">
            <my:atom.icon name="{icon}" />
        </f:if>
        <f:slot />
    </button>

..  note::
    IDE autocomplete for all available components (and their attributes) via XSD files,
    similar to ViewHelpers, is not implemented yet, but is planned for a future release.

.. _components-context:

Providing Context
=================

Sometimes it might be helpful to provide some global settings to all components
within one component collection. One common use case could be to provide design
tokens from a JSON file to your components.

:php:`AbstractComponentCollection` provides `getAdditionalVariables()`,
which allows you to do just that:

..  code-block:: php
    :caption: ComponentCollection.php

    namespace Vendor\MyPackage\Components;

    use TYPO3Fluid\Fluid\Core\Component\AbstractComponentCollection;

    final class ComponentCollection extends AbstractComponentCollection
    {
        // Runtime cache to prevent reading/parsing the JSON file multiple times
        // this is not mandatory, but increases performance with multiple components
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
    :caption: MyComponent.html (component definition)

    <f:argument name="color" type="string" optional="{true}" default="brand" />

    <div style="background-color: {designTokens.colors.{color}}"></div>

.. _components-arbitrary-arguments:

Allowing Arbitrary Arguments
============================

..  note::
    Please use this approach with care because one of the key concepts of components
    is their argument contract.

By default, components only accept arguments that are defined explicitly via
the :ref:`<f:argument> ViewHelper <typo3fluid-fluid-argument>`. However, there might
be use cases where you would like to accept arbitrary arguments.

This is possible by defining :php:`additionalArgumentsAllowed()` in your
:php:`ComponentCollection` implementation (in this example for all components
in the collection):

..  code-block:: php
    :caption: ComponentCollection.php

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
    :caption: MyTemplate.html

    <html
        xmlns:my="http://typo3.org/ns/Vendor/MyPackage/Components/ComponentCollection"
        data-namespace-typo3-fluid="true"
    >

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
    :caption: ComponentCollection.php

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

`<my:atom.button>` will then be resolved to `path/to/Components/Atom/Button.html`.

..  directory-tree::

    * :path:`path/to/Components/`
        * :path:`Atom`
            * :path:`Button.html`
