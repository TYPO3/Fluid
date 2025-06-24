.. include:: /Includes.rst.txt

.. _component-providers:

=============================
Providing External Components
=============================

..  versionadded:: Fluid 4.3

Fluid supports :ref:`components that are defined as Fluid templates <components>`
out-of-the-box. However, it is also possible to integrate external component
libraries into your Fluid-based project. This makes it possible to bridge
the gap between Fluid and other templating engines, such as Twig or Mustache.

..  note::
    There are no official integrations of Fluid with other templating engines.
    This page merely demonstrates how existing Fluid APIs can be used
    to achieve this.

Architecture Overview
=====================

Fluid's component features are based on several classes and interfaces. Some
are meant to be extended/implemented, others merely exist for internal
purposes and are annotated as `@internal` in the PHPDoc header.

The :php:`ComponentDefinitionProviderInterface`, together with
:php:`ComponentRendererInterface`, are the primary interfaces that can
be used (together with a :ref:`ViewHelperResolver delegate <viewhelperresolver-delegates>`)
to "teach" Fluid how to interact with components. The definition provider
returns instances of `ComponentDefinition`, which is an immutable DTO.

The :php:`ComponentAdapter` is an adapter class that translates between the
ViewHelper API and the described components API. It is used as default
ViewHelper implementation for all components during parse-time and will never
be used once a template is cached. It is marked `@internal` and is not
intended to be extended/replaced.

The :php:`ComponentTemplateResolverInterface` is an internal interface that is
related to Fluid's own component implementation. Same goes for
:php:`ComponentRenderer`, which renders Fluid-based component templates.
:php:`AbstractComponentCollection` is the base implementation for Fluid-based
components, which are documented in a separate chapter about
:ref:`Components <components>`.

Custom ComponentRenderer
========================

The :php:`ComponentRendererInterface` specifies how a component should be rendered.
This is the place where e. g. an external templating engine would be initiated
to render a specific template:

..  code-block:: php
    :caption: CustomComponentRenderer.php (component renderer)

    namespace Vendor\MyPackage;

    use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

    final readonly class CustomComponentRenderer implements ComponentRendererInterface
    {
        public function renderComponent(
            string $viewHelperName,
            array $arguments,
            array $slots,
            RenderingContextInterface $parentRenderingContext,
        ): string {
            $view = new TemplatingEngine();
            $view->setVariables($arguments);
            $view->setSlots($slots);
            return $view->render($viewHelperName);
        }
    }

The :php:`$parentRenderingContext` can be used to extract additional information from
the parent Fluid template that should be passed to the component, such as a `Request`
object.

Custom ComponentDefinitionProvider
==================================

The :php:`ComponentDefinitionProviderInterface` provides Fluid with all necessary
information to resolve, validate and render external components. Depending
on the available component metadata, the Fluid parser is even able to pre-validate
the supplied component parameters (defined and required arguments, booleans). However, it is
also possible to provide a "non-strict" implementation where any argument can be supplied
to the external components. The interface must always be used in combination with
:php:`ViewHelperResolverDelegateInterface`.

..  code-block:: php
    :caption: CustomComponentCollection.php (component definition provider)

    namespace Vendor\MyPackage;

    use TYPO3Fluid\Fluid\Core\Component\ComponentDefinition;
    use TYPO3Fluid\Fluid\Core\ViewHelper\UnresolvableViewHelperException;
    use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolverDelegateInterface;
    use Vendor\MyPackage\CustomComponentRenderer;

    final class CustomComponentCollection implements ViewHelperResolverDelegateInterface, ComponentDefinitionProviderInterface
    {
        public function getComponentDefinition(string $viewHelperName): ComponentDefinition
        {
            $metadata = TemplatingEngine::getComponentMetadata($viewHelperName);
            $definition = new ComponentDefinition(
                // map metadata...
            );
            return $definition;
        }

        public function getComponentRenderer(): ComponentRendererInterface
        {
            return new CustomComponentRenderer();
        }

        public function resolveViewHelperClassName(string $viewHelperName): string
        {
            if (!TemplatingEngine::componentExists($viewHelperName)) {
                throw new UnresolvableViewHelperException(
                    // ...
                );
            }
            return ComponentAdapter::class;
        }

        public function getNamespace(): string
        {
            return static::class;
        }
    }
